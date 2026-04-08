<?php

namespace App\Http\Controllers;

use App\Helpers\ChallengeProgressHelper;
use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChallengeController extends Controller
{
    public function index()
    {
        $challenges = Challenge::byCompany(Auth::user()->company_id)
            ->withCount('challengeUsers')
            ->latest()
            ->paginate(15);

        return view('challenge.index', compact('challenges'));
    }

    public function create()
    {
        $moduleOptions = Challenge::moduleOptions();
        return view('challenge.createOrEdit', compact('moduleOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'description'  => 'nullable|string|max:500',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'reward_point' => 'required|integer|min:0',
            'reward_xp'    => 'required|integer|min:0',
            'module_type'  => 'required|in:' . implode(',', array_keys(Challenge::moduleOptions())),
            'target_count' => 'required|integer|min:1',
        ]);

        Challenge::create([
            ...$request->only(['name', 'description', 'start_date', 'end_date', 'reward_point', 'reward_xp', 'module_type', 'target_count']),
            'company_id' => Auth::user()->company_id,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('challenge.index')->with('success', 'Challenge berhasil dibuat.');
    }

    public function show(Challenge $challenge)
    {
        $challenge->load('createdBy');

        $participants = $challenge->challengeUsers()->with('user', 'invitedBy')->get()
            ->map(function ($cu) use ($challenge) {
                $current = ChallengeProgressHelper::current($challenge, $cu->user_id);
                $percent = $challenge->target_count > 0
                    ? min(100, (int) round(($current / $challenge->target_count) * 100))
                    : 0;
                return [
                    'cu'      => $cu,
                    'user'    => $cu->user,
                    'current' => $current,
                    'percent' => $percent,
                ];
            })
            ->sortByDesc('current')
            ->values();

        $invitableUsers = User::byCompany(Auth::user()->company_id)
            ->isActive()
            ->whereNotIn('id', $challenge->invitedUsers->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('challenge.show', compact('challenge', 'participants', 'invitableUsers'));
    }

    public function edit(Challenge $challenge)
    {
        $moduleOptions = Challenge::moduleOptions();
        return view('challenge.createOrEdit', compact('challenge', 'moduleOptions'));
    }

    public function update(Request $request, Challenge $challenge)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'description'  => 'nullable|string|max:500',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'reward_point' => 'required|integer|min:0',
            'reward_xp'    => 'required|integer|min:0',
            'module_type'  => 'required|in:' . implode(',', array_keys(Challenge::moduleOptions())),
            'target_count' => 'required|integer|min:1',
        ]);

        $challenge->update($request->only([
            'name', 'description', 'start_date', 'end_date',
            'reward_point', 'reward_xp', 'module_type', 'target_count',
        ]));

        return redirect()->route('challenge.show', $challenge)->with('success', 'Challenge berhasil diperbarui.');
    }

    public function destroy(Challenge $challenge)
    {
        $challenge->delete();
        return redirect()->route('challenge.index')->with('success', 'Challenge berhasil dihapus.');
    }

    // ── Invite / Remove ────────────────────────────────────────────────────

    public function invite(Request $request, Challenge $challenge)
    {
        $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        foreach ($request->user_ids as $uid) {
            ChallengeUser::firstOrCreate(
                ['challenge_id' => $challenge->id, 'user_id' => $uid],
                ['invited_by'   => Auth::id()]
            );
        }

        return redirect()->route('challenge.show', $challenge)
            ->with('success', count($request->user_ids) . ' user berhasil diinvite.');
    }

    public function removeUser(Challenge $challenge, string $userId)
    {
        ChallengeUser::where('challenge_id', $challenge->id)
                     ->where('user_id', $userId)
                     ->delete();

        return redirect()->route('challenge.show', $challenge)
            ->with('success', 'User berhasil dikeluarkan dari challenge.');
    }
}
