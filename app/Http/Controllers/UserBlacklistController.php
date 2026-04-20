<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\UserBlacklist;
use App\Schemas\RoleSchema;

class UserBlacklistController extends Controller
{
    /**
     * Tampilkan daftar blacklist + form tambah manual.
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $isRoot = Auth::user()->role->name === RoleSchema::ROOT;

        $blacklists = UserBlacklist::when(!$isRoot, fn($q) => $q->byCompany($companyId))
            ->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->get('search') . '%')
                  ->orWhere('email', 'like', '%' . $request->get('search') . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // User tidak aktif (tidak punya divisi) untuk fitur import
        $inactiveUsers = User::when(!$isRoot, fn($q) => $q->byCompany($companyId))
            ->isNotActive()
            ->orderBy('name')
            ->get();

        // Cek user mana yang sudah ada di blacklist
        $blacklistedUserIds = UserBlacklist::when(!$isRoot, fn($q) => $q->byCompany($companyId))
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->toArray();

        return view('blacklist.index', compact('blacklists', 'inactiveUsers', 'blacklistedUserIds'));
    }

    /**
     * Tambah manual ke blacklist.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'nullable|email|max:255',
            'phone'  => ['nullable', 'regex:/^(\+62|0|62)[0-9]{9,13}$/'],
            'id_card' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'reason' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $blacklist = new UserBlacklist();
        $blacklist->company_id    = Auth::user()->company_id;
        $blacklist->name          = $request->name;
        $blacklist->email         = $request->email;
        $blacklist->phone         = $request->phone;
        $blacklist->id_card       = $request->id_card;
        $blacklist->address       = $request->address;
        $blacklist->reason        = $request->reason;
        $blacklist->blacklisted_by = Auth::id();

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = 'public/blacklist_avatars/' . $fileName;
            Storage::put($filePath, file_get_contents($file));
            $blacklist->avatar = $filePath;
        }

        $blacklist->save();

        return redirect()->route('user-blacklist.index')->with('store', true);
    }

    /**
     * Import user tidak aktif ke blacklist.
     */
    public function importInactive(Request $request)
    {
        $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'uuid|exists:users,id',
            'reason'     => 'nullable|string|max:1000',
        ]);

        $companyId = Auth::user()->company_id;
        $count = 0;

        foreach ($request->user_ids as $userId) {
            // Hindari duplikat
            $exists = UserBlacklist::where('company_id', $companyId)
                ->where('user_id', $userId)
                ->exists();

            if ($exists) {
                continue;
            }

            $user = User::find($userId);
            if (!$user) {
                continue;
            }

            UserBlacklist::create([
                'company_id'    => $companyId,
                'user_id'       => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'phone'         => $user->phone,
                'id_card'       => $user->id_card,
                'address'       => $user->address,
                'avatar'        => $user->avatar,
                'reason'        => $request->reason,
                'blacklisted_by' => Auth::id(),
            ]);

            $count++;
        }

        return redirect()->route('user-blacklist.index')
            ->with('storeWithMessage', "$count pengguna berhasil ditambahkan ke blacklist.");
    }

    /**
     * Hapus dari blacklist.
     */
    public function destroy($id)
    {
        $blacklist = UserBlacklist::findOrFail($id);

        // Pastikan hanya bisa hapus dari company sendiri (kecuali ROOT)
        if (Auth::user()->role->name !== RoleSchema::ROOT) {
            if ($blacklist->company_id !== Auth::user()->company_id) {
                return redirect()->back()->with('error', 'Tidak diizinkan.');
            }
        }

        if ($blacklist->avatar && !$blacklist->user_id) {
            // Hapus foto jika diupload manual (bukan mirrored dari user)
            Storage::delete($blacklist->avatar);
        }

        $blacklist->delete();

        return redirect()->route('user-blacklist.index')->with('delete', true);
    }

    /**
     * AJAX: cari nama di blacklist saat input nama user baru.
     */
    public function search(Request $request)
    {
        $name = $request->get('name', '');
        $companyId = Auth::user()->company_id;
        $isRoot = Auth::user()->role->name === RoleSchema::ROOT;

        if (strlen(trim($name)) < 2) {
            return response()->json([]);
        }

        $results = UserBlacklist::with(['user.company'])
            ->when(!$isRoot, fn($q) => $q->byCompany($companyId))
            ->where('name', 'like', '%' . $name . '%')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $user = $item->user;

                // Ambil avatar — fallback ke blacklist avatar jika user tidak ada
                $avatarPath = $user ? ($user->avatar ?? null) : ($item->avatar ?? null);
                $avatarUrl  = $avatarPath ? s3_asset(true, 10, $avatarPath) : null;

                return [
                    'id'           => $item->id,
                    'name'         => $item->name,
                    'email'        => $user ? ($user->email ?? $item->email) : $item->email,
                    'phone'        => $user ? ($user->phone ?? $item->phone) : $item->phone,
                    'id_card'      => $user ? ($user->id_card ?? $item->id_card) : $item->id_card,
                    'address'      => $user ? ($user->address ?? $item->address) : $item->address,
                    'company_name' => $user && $user->company ? $user->company->name : null,
                    'reason'       => $item->reason,
                    'avatar_url'   => $avatarUrl,
                ];
            });

        return response()->json($results);
    }
}
