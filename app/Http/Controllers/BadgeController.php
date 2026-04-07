<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BadgeController extends Controller
{
    public function index()
    {
        $badges = Badge::byCompany(Auth::user()->company_id)->withCount('userBadges')->latest()->paginate(15);
        return view('badge.index', compact('badges'));
    }

    public function create()
    {
        return view('badge.createOrEdit');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'image'       => 'nullable|file|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ]);

        $data = $request->only(['name', 'description']);
        $data['company_id'] = Auth::user()->company_id;

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $fileName = uniqid('badge_') . '.' . $file->getClientOriginalExtension();
            $filePath = 'badges/' . $fileName;
            Storage::put($filePath, file_get_contents($file));
            $data['image'] = $filePath;
        }

        Badge::create($data);

        return redirect()->route('badge.index')->with('success', 'Badge berhasil dibuat.');
    }

    public function edit(Badge $badge)
    {
        return view('badge.createOrEdit', compact('badge'));
    }

    public function update(Request $request, Badge $badge)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'image'       => 'nullable|file|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ]);

        $data = $request->only(['name', 'description']);

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $fileName = uniqid('badge_') . '.' . $file->getClientOriginalExtension();
            $filePath = 'badges/' . $fileName;
            Storage::put($filePath, file_get_contents($file));
            $data['image'] = $filePath;
        }

        $badge->update($data);

        return redirect()->route('badge.index')->with('success', 'Badge berhasil diperbarui.');
    }

    public function destroy(Badge $badge)
    {
        $badge->delete();
        return redirect()->route('badge.index')->with('success', 'Badge berhasil dihapus.');
    }

    // ── ASSIGN ──────────────────────────────────────────────────────────────

    public function assignIndex()
    {
        $badges = Badge::orderBy('name')->get();
        $users  = User::byCompany(Auth::user()->company_id)->isActive()->orderBy('name')->get();
        $recent = UserBadge::with(['badge', 'user', 'givenBy'])
                    ->whereHas('user', fn($q) => $q->where('company_id', Auth::user()->company_id))
                    ->latest()
                    ->paginate(20);

        return view('badge.assign', compact('badges', 'users', 'recent'));
    }

    public function assignStore(Request $request)
    {
        $request->validate([
            'badge_id' => 'required|exists:badges,id',
            'user_id'  => 'required|exists:users,id',
        ]);

        UserBadge::create([
            'badge_id' => $request->badge_id,
            'user_id'  => $request->user_id,
            'given_by' => Auth::id(),
        ]);

        return redirect()->route('badge.assign')->with('success', 'Badge berhasil dikirim ke user.');
    }

    public function revokeUserBadge(UserBadge $userBadge)
    {
        $userBadge->delete();
        return redirect()->route('badge.assign')->with('success', 'Badge berhasil dicabut.');
    }
}
