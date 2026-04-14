<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\EventUser;
use App\Models\EventView;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::byCompany(Auth::user()->company_id)
            ->withCount(['eventUsers', 'eventViews', 'occurrences'])
            ->with('creator')
            ->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('is_routine')) {
            $query->where('is_routine', $request->is_routine === '1');
        }

        $events = $query->paginate(15)->withQueryString();

        return view('event.index', compact('events'));
    }

    public function create()
    {
        $users      = User::byCompany(Auth::user()->company_id)->isActive()->get(['id', 'name']);
        $challenges = Challenge::byCompany(Auth::user()->company_id)
            ->whereIn('status', ['draft', 'running'])
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'module_type']);

        return view('event.createOrEdit', compact('users', 'challenges'));
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, $this->rules());

        $data['company_id']        = Auth::user()->company_id;
        $data['created_by']        = Auth::id();
        $data['is_routine']        = $request->boolean('is_routine');
        $data['is_active']         = $request->boolean('is_active', true);
        $data['sync_participants'] = $request->boolean('sync_participants');

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $fileName = uniqid('event_') . '.' . $file->getClientOriginalExtension();
            Storage::put('events/' . $fileName, file_get_contents($file));
            $data['image'] = 'events/' . $fileName;
        }

        $event = Event::create($data);

        // Buat occurrence pertama
        EventOccurrence::create([
            'id'         => Uuid::uuid4()->toString(),
            'event_id'   => $event->id,
            'start_date' => $event->start_date->toDateString(),
            'end_date'   => $event->end_date->toDateString(),
        ]);

        // Attach challenges ke event
        if ($request->filled('challenges')) {
            $event->challenges()->sync($request->challenges);
        }

        // Invite users
        if ($request->filled('users')) {
            foreach ($request->users as $userId) {
                EventUser::firstOrCreate(
                    ['event_id' => $event->id, 'user_id' => $userId],
                    ['invited_by' => Auth::id()]
                );
            }

            // Sync ke semua challenge terkait jika sync_participants aktif
            if ($event->sync_participants && $request->filled('challenges')) {
                $this->syncUsersToLinkedChallenges($event, $request->users);
            }
        }

        return redirect()->route('event.detail', $event->id)
            ->with('success', 'Event berhasil dibuat.');
    }

    public function show(Event $event)
    {
        return view('event.show', compact('event'));
    }

    public function detail(Event $event)
    {
        $event->load(['creator', 'eventUsers.user', 'eventUsers.invitedBy', 'occurrences', 'challenges']);

        // Urutkan occurrences dari yang terbaru
        $occurrences = $event->occurrences->sortByDesc('start_date')->values();

        // History view: per user, ambil view terakhir
        $viewHistory = EventView::where('event_id', $event->id)
            ->with(['user', 'occurrence'])
            ->latest()
            ->get()
            ->unique('user_id')
            ->values();

        $invitableUsers = User::byCompany(Auth::user()->company_id)
            ->isActive()
            ->whereNotIn('id', $event->eventUsers->pluck('user_id'))
            ->get(['id', 'name']);

        return view('event.detail', compact('event', 'occurrences', 'viewHistory', 'invitableUsers'));
    }

    public function edit(Event $event)
    {
        $users              = User::byCompany(Auth::user()->company_id)->isActive()->get(['id', 'name']);
        $assignedUserIds    = $event->eventUsers->pluck('user_id')->toArray();
        $challenges         = Challenge::byCompany(Auth::user()->company_id)
            ->whereIn('status', ['draft', 'running'])
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'module_type']);
        $assignedChallengeIds = $event->challenges->pluck('id')->toArray();

        return view('event.createOrEdit', compact('event', 'users', 'assignedUserIds', 'challenges', 'assignedChallengeIds'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $this->validate($request, $this->rules());

        $data['is_routine']        = $request->boolean('is_routine');
        $data['is_active']         = $request->boolean('is_active', true);
        $data['sync_participants'] = $request->boolean('sync_participants');

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $fileName = uniqid('event_') . '.' . $file->getClientOriginalExtension();
            Storage::put('events/' . $fileName, file_get_contents($file));
            $data['image'] = 'events/' . $fileName;
        }

        $event->update($data);

        // Sync challenges (replace seluruh asosiasi)
        $event->challenges()->sync($request->filled('challenges') ? $request->challenges : []);

        if ($event->sync_participants && $request->filled('challenges') && $event->users->count() > 0) {
            $this->syncUsersToLinkedChallenges($event, $event->users->pluck('id')->toArray());
        }

        return redirect()->route('event.detail', $event->id)
            ->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('event.index')->with('success', 'Event berhasil dihapus.');
    }

    public function invite(Request $request, Event $event)
    {
        $request->validate([
            'users'   => 'required|array|min:1',
            'users.*' => 'exists:users,id',
        ]);

        foreach ($request->users as $userId) {
            EventUser::firstOrCreate(
                ['event_id' => $event->id, 'user_id' => $userId],
                ['invited_by' => Auth::id()]
            );
        }

        // Jika sync_participants aktif, otomatis invite ke semua challenge terkait
        if ($event->sync_participants) {
            $this->syncUsersToLinkedChallenges($event, $request->users);
        }

        return back()->with('success', 'User berhasil diundang ke event.');
    }

    public function removeUser(Event $event, string $userId)
    {
        EventUser::where('event_id', $event->id)->where('user_id', $userId)->delete();

        // Jika sync_participants aktif, otomatis keluarkan dari semua challenge terkait
        if ($event->sync_participants) {
            $this->removeUserFromLinkedChallenges($event, $userId);
        }

        return back()->with('success', 'User berhasil dihapus dari event.');
    }

    // ── Private helpers ────────────────────────────────────────────────────

    /**
     * Invite daftar user ke semua challenge yang terkait dengan event ini.
     * Hanya challenge berstatus draft/running yang diproses.
     */
    private function syncUsersToLinkedChallenges(Event $event, array $userIds): void
    {
        $challenges = $event->challenges()->whereIn('status', ['draft', 'running'])->get();

        foreach ($challenges as $challenge) {
            foreach ($userIds as $userId) {
                ChallengeUser::firstOrCreate(
                    ['challenge_id' => $challenge->id, 'user_id' => $userId],
                    ['invited_by'   => Auth::id()]
                );
            }
        }
    }

    /**
     * Keluarkan satu user dari semua challenge yang terkait dengan event ini.
     */
    private function removeUserFromLinkedChallenges(Event $event, string $userId): void
    {
        $challengeIds = $event->challenges()->pluck('challenges.id');

        if ($challengeIds->isNotEmpty()) {
            ChallengeUser::whereIn('challenge_id', $challengeIds)
                         ->where('user_id', $userId)
                         ->delete();
        }
    }

    private function rules(): array
    {
        return [
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'image'              => 'nullable|image|max:5120',
            'color'              => 'nullable|string|max:20',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after_or_equal:start_date',
            'start_time'         => 'nullable|date_format:H:i',
            'end_time'           => 'nullable|date_format:H:i',
            'is_routine'         => 'boolean',
            'routine_end_date'   => 'nullable|date|after:start_date',
            'is_active'          => 'boolean',
            'sync_participants'  => 'boolean',
            'users'              => 'nullable|array',
            'users.*'            => 'exists:users,id',
            'challenges'         => 'nullable|array',
            'challenges.*'       => 'exists:challenges,id',
        ];
    }
}
