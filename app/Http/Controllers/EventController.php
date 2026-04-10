<?php

namespace App\Http\Controllers;

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
        $users = User::byCompany(Auth::user()->company_id)->isActive()->get(['id', 'name']);
        return view('event.createOrEdit', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, $this->rules());

        $data['company_id'] = Auth::user()->company_id;
        $data['created_by'] = Auth::id();
        $data['is_routine'] = $request->boolean('is_routine');
        $data['is_active']  = $request->boolean('is_active', true);

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

        // Invite users
        if ($request->filled('users')) {
            foreach ($request->users as $userId) {
                EventUser::firstOrCreate(
                    ['event_id' => $event->id, 'user_id' => $userId],
                    ['invited_by' => Auth::id()]
                );
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
        $event->load(['creator', 'eventUsers.user', 'eventUsers.invitedBy', 'occurrences']);

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
        $users           = User::byCompany(Auth::user()->company_id)->isActive()->get(['id', 'name']);
        $assignedUserIds = $event->eventUsers->pluck('user_id')->toArray();
        return view('event.createOrEdit', compact('event', 'users', 'assignedUserIds'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $this->validate($request, $this->rules());

        $data['is_routine'] = $request->boolean('is_routine');
        $data['is_active']  = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $fileName = uniqid('event_') . '.' . $file->getClientOriginalExtension();
            Storage::put('events/' . $fileName, file_get_contents($file));
            $data['image'] = 'events/' . $fileName;
        }

        $event->update($data);

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

        return back()->with('success', 'User berhasil diundang ke event.');
    }

    public function removeUser(Event $event, string $userId)
    {
        EventUser::where('event_id', $event->id)->where('user_id', $userId)->delete();
        return back()->with('success', 'User berhasil dihapus dari event.');
    }

    private function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'image'             => 'nullable|image|max:5120',
            'color'             => 'nullable|string|max:20',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'start_time'        => 'nullable|date_format:H:i',
            'end_time'          => 'nullable|date_format:H:i',
            'is_routine'        => 'boolean',
            'routine_end_date'  => 'nullable|date|after:start_date',
            'is_active'         => 'boolean',
            'users'             => 'nullable|array',
            'users.*'           => 'exists:users,id',
        ];
    }
}
