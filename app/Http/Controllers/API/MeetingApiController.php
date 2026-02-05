<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\GoogleService;
use App\Enums\ParamSchema; 

class MeetingApiController extends Controller
{
    /**
     * List Meeting
     */
    public function index()
    {
        $meetings = Meeting::where('company_id', Auth::user()->company_id)
            ->with('participants')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $meetings,
            'message' => 'Daftar meeting berhasil diambil'
        ]);
    }

    /**
     * Create Meeting
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'meeting_name' => 'required|string',
            'meeting_type' => 'required|string',
            'meeting_link' => 'nullable|url',
            'meeting_agenda' => 'required|string',
            'meeting_location' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'notes' => 'nullable|string',
            'participant' => 'required|array',
            'participant.*' => 'required|email',
            'project_id' => 'nullable|exists:projects,id',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'attachment_link' => 'nullable|url'
        ]);

        DB::beginTransaction();
        try {
            $validated['slug'] = Str::slug($validated['meeting_name']) . '-' . Str::random(5);
            $validated['user_id'] = Auth::id();
            $validated['company_id'] = Auth::user()->company_id;
            $validated['participant'] = json_encode($request->participant);
            $validated['google_meet_link'] = $request->meeting_link ?? null;

            if ($request->hasFile('attachment')) {
                $validated['attachment'] = $request->file('attachment')->store('meeting-attachments');
            }

            $meeting = Meeting::create($validated);

            /**
             * Create Google Meet
             */
            if (in_array($request->meeting_type, ['google_meet', 'online']) && empty($request->meeting_link)) {
                $googleService = new GoogleService(Auth::user()->company_id);
                $googleMeet = $googleService->createGoogleMeet($meeting);
                $googleMeetData = $googleMeet->getData();

                if ($googleMeetData->success) {
                    $meeting->update([
                        'google_meet_link' => $googleMeetData->link,
                        'google_event_id' => $googleMeetData->event_id
                    ]);
                }
            }

            $participantIds = User::whereIn('email', $request->participant)
                ->orWhereIn('email_gmail', $request->participant)
                ->pluck('id')
                ->toArray();

            $participantIds[] = Auth::id();
            $meeting->participants()->sync($participantIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $meeting->load('participants'),
                'message' => 'Meeting berhasil dibuat'
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Read Detail Meeting
     */
    public function show($id)
    {
        $meeting = Meeting::where('company_id', Auth::user()->company_id)
            ->with('participants')
            ->find($id);

        if (!$meeting) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Meeting tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $meeting,
            'message' => 'Detail meeting berhasil diambil'
        ]);
    }

    /**
     * Update Meeting
     */
    public function update(Request $request, $id)
    {
        $meeting = Meeting::where('company_id', Auth::user()->company_id)->find($id);

        if (!$meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Meeting tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'meeting_name' => 'required|string',
            'meeting_type' => 'required|string',
            'meeting_link' => 'nullable|url',
            'meeting_agenda' => 'required|string',
            'meeting_location' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'notes' => 'nullable|string',
            'participant' => 'required|array',
            'participant.*' => 'required|email',
            'project_id' => 'nullable|exists:projects,id',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'attachment_link' => 'nullable|url'
        ]);

        DB::beginTransaction();
        try {
            $validated['participant'] = json_encode($request->participant);
            $validated['google_meet_link'] = $request->meeting_link ?? null;

            if ($request->hasFile('attachment')) {
                if ($meeting->attachment && Storage::exists($meeting->attachment)) {
                    Storage::delete($meeting->attachment);
                }
                $validated['attachment'] = $request->file('attachment')->store('meeting-attachments');
            }

            $meeting->update($validated);

            /**
             * Update Google Meet
             */
            if (in_array($request->meeting_type, ['google_meet', 'online']) && empty($request->meeting_link)) {
                $googleService = new GoogleService(Auth::user()->company_id);

                if ($meeting->google_event_id) {
                    $googleService->updateGoogleMeet($meeting, $request->all());
                } else {
                    $googleMeet = $googleService->createGoogleMeet($meeting);
                    $googleMeetData = $googleMeet->getData();

                    if ($googleMeetData->success) {
                        $meeting->update([
                            'google_meet_link' => $googleMeetData->link,
                            'google_event_id' => $googleMeetData->event_id
                        ]);
                    }
                }
            }

            $participantIds = User::whereIn('email', $request->participant)
                ->orWhereIn('email_gmail', $request->participant)
                ->pluck('id')
                ->toArray();

            $participantIds[] = $meeting->user_id;
            $meeting->participants()->sync($participantIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $meeting->load('participants'),
                'message' => 'Meeting berhasil diperbarui'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Delete Meeting
     */
    public function destroy($id)
    {
        $meeting = Meeting::where('company_id', Auth::user()->company_id)->find($id);

        if (!$meeting) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Meeting tidak ditemukan'
            ], 404);
        }

        $meeting->participants()->detach();
        $meeting->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Meeting berhasil dihapus'
        ]);
    }
}
