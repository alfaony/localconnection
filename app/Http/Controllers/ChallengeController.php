<?php

namespace App\Http\Controllers;

use App\Helpers\ChallengeProgressHelper;
use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Access;

class ChallengeController extends Controller
{
    public function index(Request $request)
    {
        $permissionCreate = Access::can('create','challenges');
        $query = Challenge::byCompany(Auth::user()->company_id)
            ->withCount('challengeUsers')
            ->latest();


        if(!$permissionCreate){
            $query->byInvitedUser(Auth::id());
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) == 2) {
                try {
                    $start = \Carbon\Carbon::parse(trim($dates[0]))->startOfDay();
                    $end = \Carbon\Carbon::parse(trim($dates[1]))->endOfDay();
                    $query->where(function ($q) use ($start, $end) {
                        $q->whereBetween('start_date', [$start, $end])
                          ->orWhereBetween('end_date', [$start, $end])
                          ->orWhere(function ($q2) use ($start, $end) {
                              $q2->where('start_date', '<=', $start)
                                 ->where('end_date', '>=', $end);
                          });
                    });
                } catch (\Exception $e) {
                    // Ignore date parsing errors
                }
            }
        }

        $challenges = $query->paginate(15)->withQueryString();

        return view('challenge.index', compact('challenges'));
    }

    public function create()
    {
        $moduleOptions = Challenge::moduleOptions();
        $events        = $this->getCompanyEvents();

        return view('challenge.createOrEdit', compact('moduleOptions', 'events'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'description'  => 'nullable|string|max:500',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'status'       => 'nullable|in:draft,running,finish',
            'reward_point' => 'required|integer|min:0',
            'reward_xp'    => 'required|integer|min:0',
            'module_type'  => 'required|in:' . implode(',', array_keys(Challenge::moduleOptions())),
            'target_count' => 'required|integer|min:1',
            'events'       => 'nullable|array',
            'events.*'     => 'exists:events,id',
        ]);

        $challenge = Challenge::create([
            ...$request->only(['name', 'description', 'start_date', 'end_date', 'status', 'reward_point', 'reward_xp', 'module_type', 'target_count']),
            'company_id' => Auth::user()->company_id,
            'created_by' => Auth::id(),
            'status'     => $request->status ?? 'draft',
        ]);

        // Attach events ke challenge
        if ($request->filled('events')) {
            $challenge->events()->sync($request->events);

            // Untuk setiap event yang sync_participants=true, auto-invite semua peserta event ke challenge ini
            $this->syncEventParticipantsToChallenge($challenge, $request->events);
        }

        return redirect()->route('challenge.show', $challenge)->with('success', 'Challenge berhasil dibuat.');
    }

    public function show(Challenge $challenge)
    {
        $challenge->load('createdBy', 'events');

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

        $excludeIds = $challenge->invitedUsers->pluck('id')->toArray();
        [$groupedInvitable, $invitableNoDivision] = $this->loadGroupedUsers($excludeIds);

        return view('challenge.show', compact('challenge', 'participants', 'groupedInvitable', 'invitableNoDivision'));
    }

    public function edit(Challenge $challenge)
    {
        if (in_array($challenge->status, ['running', 'finish'])) {
            return redirect()->route('challenge.index')->with('error', 'Challenge yang sedang berjalan atau selesai tidak dapat diedit.');
        }

        $challenge->load('events');

        $moduleOptions    = Challenge::moduleOptions();
        $events           = $this->getCompanyEvents();
        $assignedEventIds = $challenge->events->pluck('id')->toArray();

        return view('challenge.createOrEdit', compact('challenge', 'moduleOptions', 'events', 'assignedEventIds'));
    }

    public function update(Request $request, Challenge $challenge)
    {
        if (in_array($challenge->status, ['running', 'finish'])) {
            return redirect()->route('challenge.index')->with('error', 'Challenge yang sedang berjalan atau selesai tidak dapat diupdate.');
        }

        // Load events sebelum validasi agar snapshot $previousEventIds akurat
        $challenge->load('events');

        $request->validate([
            'name'         => 'required|string|max:150',
            'description'  => 'nullable|string|max:500',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'status'       => 'nullable|in:draft,running,finish',
            'reward_point' => 'required|integer|min:0',
            'reward_xp'    => 'required|integer|min:0',
            'module_type'  => 'required|in:' . implode(',', array_keys(Challenge::moduleOptions())),
            'target_count' => 'required|integer|min:1',
            'events'       => 'nullable|array',
            'events.*'     => 'exists:events,id',
        ]);

        $challenge->update($request->only([
            'name', 'description', 'start_date', 'end_date', 'status',
            'reward_point', 'reward_xp', 'module_type', 'target_count',
        ]));

        // ── Hitung diff events ────────────────────────────────
        $previousEventIds = $challenge->events->pluck('id')->toArray();
        $newEventIds      = $request->filled('events') ? $request->events : [];
        $addedEventIds    = array_diff($newEventIds, $previousEventIds);
        $removedEventIds  = array_diff($previousEventIds, $newEventIds);

        $challenge->events()->sync($newEventIds);

        // Event baru ditambah & punya sync_participants → invite peserta event ke challenge
        if (!empty($addedEventIds)) {
            $this->syncEventParticipantsToChallenge($challenge, $addedEventIds);
        }

        // Event dilepas & punya sync_participants → keluarkan peserta event dari challenge
        if (!empty($removedEventIds)) {
            $this->removeEventParticipantsFromChallenge($challenge, $removedEventIds);
        }

        return redirect()->route('challenge.show', $challenge)->with('success', 'Challenge berhasil diperbarui.');
    }

    public function destroy(Challenge $challenge)
    {
        if (in_array($challenge->status, ['running', 'finish'])) {
            return redirect()->route('challenge.index')->with('error', 'Challenge yang sedang berjalan atau selesai tidak dapat dihapus.');
        }

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

    // ── Private helpers ────────────────────────────────────────────────────

    /**
     * Load active users grouped by primary division.
     * $excludeIds: IDs to exclude (already participating).
     * Returns: [$groupedUsers (keyed by division name), $usersNoDivision]
     */
    private function loadGroupedUsers(array $excludeIds = []): array
    {
        $query = User::byCompany(Auth::user()->company_id)
            ->isActive()
            ->with('divisions');

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        $users = $query->orderBy('name')->get();

        $withDiv = $users
            ->filter(fn($u) => $u->divisions->isNotEmpty())
            ->groupBy(fn($u) => ($u->primaryDivision ?? $u->firstDivision)?->name ?? 'Lainnya')
            ->sortKeys();

        $noDivision = $users->filter(fn($u) => $u->divisions->isEmpty())->values();

        return [$withDiv, $noDivision];
    }

    /**
     * Ambil daftar event aktif milik company untuk select di form.
     */
    private function getCompanyEvents()
    {
        return Event::byCompany(Auth::user()->company_id)
            ->where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->get(['id', 'name', 'start_date', 'end_date', 'sync_participants', 'color']);
    }

    /**
     * Untuk setiap event dalam $eventIds yang memiliki sync_participants=true,
     * otomatis invite semua peserta event tersebut ke challenge ini.
     */
    private function syncEventParticipantsToChallenge(Challenge $challenge, array $eventIds): void
    {
        if (empty($eventIds)) return;

        $syncEvents = Event::whereIn('id', $eventIds)
            ->where('sync_participants', true)
            ->with('eventUsers')
            ->get();

        foreach ($syncEvents as $event) {
            foreach ($event->eventUsers as $eu) {
                ChallengeUser::firstOrCreate(
                    ['challenge_id' => $challenge->id, 'user_id' => $eu->user_id],
                    ['invited_by'   => Auth::id()]
                );
            }
        }
    }

    /**
     * Untuk setiap event dalam $eventIds yang memiliki sync_participants=true,
     * keluarkan semua peserta event tersebut dari challenge ini.
     */
    private function removeEventParticipantsFromChallenge(Challenge $challenge, array $eventIds): void
    {
        if (empty($eventIds)) return;

        $syncEvents = Event::whereIn('id', $eventIds)
            ->where('sync_participants', true)
            ->with('eventUsers')
            ->get();

        foreach ($syncEvents as $event) {
            $userIds = $event->eventUsers->pluck('user_id')->toArray();
            if (!empty($userIds)) {
                ChallengeUser::where('challenge_id', $challenge->id)
                             ->whereIn('user_id', $userIds)
                             ->delete();
            }
        }
    }
}
