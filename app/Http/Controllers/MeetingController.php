<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rating;
use App\Models\Meeting;
use App\Models\Project;

use Google\Service\Calendar;
use Illuminate\Http\Request;
use App\Mail\RatingInvitation;
use App\Mail\MeetingInvitation;
use App\Models\MeetingParticipant;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

use App\Helpers\InboxHelper;

class MeetingController extends Controller
{

    protected $googleMeetController;

    public function __construct(GoogleMeetController $googleMeetController)
    {
        $this->googleMeetController = $googleMeetController;
    }
    public function index()
    {
        return view('meeting.index');
    }

    public function create()
    {
        $users = User::byCompany(Auth::user()->company_id)->get();
        $projects = Project::byCompany(Auth::user()->company_id)->get();
        return view('meeting.createOrEdit', compact('users', 'projects'));
    }

    public function upload(Meeting $meeting, Request $request)
    {
        try {
            $request->validate([
                'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'attachment_link' => 'nullable|url'
            ]);
            
            $data = [];
            
            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('meeting-attachments', 'public');
                $data['attachment'] = $path;
                Log::info('File path: ' . $path);
            }
            
            if ($request->filled('attachment_link')) {
                $data['attachment_link'] = $request->attachment_link;
            }
            
            if (empty($data)) {
                return back()->with('error', 'Please provide either a file or a link');
            }
    
            
            $updated = $meeting->update($data);

            
            if (!$updated) {
                return back()->with('error', 'Gagal menyimpan data');
            }
            
            return redirect()->route('meeting.show', $meeting->id)
                ->with('success', 'Attachment uploaded successfully.');
                
        } catch (\Exception $e) {
            Log::error('Error in upload method: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    

    public function complete(Meeting $meeting)
    {
        try {
            // Update status meeting ke 'completed'
            $updated = $meeting->update([
                'status' => 'completed'
            ]);
            
            if (!$updated) {
                return back()->with('error', 'Gagal mengubah status meeting');
            }

            return redirect()->route('meeting.show', $meeting->id)
                ->with('success', 'Meeting telah selesai.');
        } catch (\Exception $e) {
            Log::error('Error in complete method: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }



    public function getUsers(Request $request)
    {
        $search = $request->name;
        
        if ($search) {
            $peserta = User::where('name', 'LIKE', "%$search%")->get();
        } else {
            $peserta = User::all();
        }
        
        return response()->json($peserta);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'meeting_name' => 'required|string',
            'meeting_type' => 'required|in:offline,online',
            'google_meet_link' => 'nullable|url',
            'google_event_id' => 'nullable|string',
            'meeting_agenda' => 'required|string',
            'meeting_location' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'notes' => 'nullable|string',
            'participant' => 'required|array',
            'participant.*' => 'required',
            'attachment_link' => 'nullable|url',
            'attachment' => 'nullable|file|max:2048'
        ]);

        try {
            $validated['participant'] = json_encode($request->participant);
            $validated['user_id'] = Auth::user()->id;
            $validated['company_id'] = Auth::user()->company_id;
    
            if ($request->hasFile('attachment')) 
            {
                $validated['attachment'] = $request->file('attachment')->store('attachments');
            }
    
            $meeting = Meeting::create($validated);
    
            $participantIds = [];
            $externalEmails = [];

            foreach ($request->participant as $p) {
                if (User::where('id', $p)->exists())
                 {
                    $participantIds[] = $p;
                    $message = "Undangan Meeting - " . $meeting->meeting_name;
                    $url = route('meeting.show',$meeting->slug);

                    $this->sentMessage($p, Auth::user()->id, $message, $url, false, 'high');
                } elseif (filter_var($p, FILTER_VALIDATE_EMAIL)) {
                    $externalEmails[] = $p;
                }
            }

            // Sync user internal
            $meeting->participants()->sync($participantIds);
    
            if (!empty($externalEmails)) 
            {
                $meeting->participants = $externalEmails;
                $meeting->save();
            }
    
        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
            Log::error('Error in store method: ' . $th->getMessage());
            return redirect()->route('meeting.index')->with('error', $th->getMessage());
        }

        return redirect()->route('meeting.index')->with('success', 'Meeting successfully created and invitations sent.');
    }


    public function show($slug)
    {       
        $meeting = Meeting::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
         // Decode 'nama_pic' to get the array of user IDs
        // $namaPicIds = json_decode($meeting->nama_pic, true);
        // $namaPicUsers = User::whereIn('id', $namaPicIds)->get();

        // // Decode 'peserta' to get the array of user IDs for participants
        // $pesertaIds = json_decode($meeting->peserta, true);
        // $pesertaUsers = User::whereIn('id', $pesertaIds)->get();
        return view('meeting.show', compact('meeting'));
    }



    public function edit($slug)
    {
        $meeting = Meeting::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $users = User::byCompany(Auth::user()->company_id)->get();
        $projects = Project::byCompany(Auth::user()->company_id)->get();
        return view('meeting.createOrEdit', compact('meeting', 'users', 'projects'));
    }



    public function update(Request $request, $slug)
    {
        try {
             $validated = $request->validate([
                'meeting_name' => 'required|string',
                'meeting_type' => 'required|in:offline,online',
                'google_meet_link' => 'nullable|url',
                'google_event_id' => 'nullable|string',
                'meeting_agenda' => 'required|string',
                'meeting_location' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
                'notes' => 'nullable|string',
                'participant' => 'required|array',
                'participant.*' => 'required|uuid|exists:users,id',
                'attachment_link' => 'nullable|url',
                'attachment' => 'nullable|file|max:2048'
            ]);
            $meeting = Meeting::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            

            // if ($meeting->meeting_type === 'online' && $meeting->google_event_id) {
            //     try {
            //         $googleMeetResponse = $this->googleMeetController->updateGoogleMeet($meeting, $request);
            //         $responseData = json_decode($googleMeetResponse->getContent(), true);

            //         if (!$responseData['success']) {
            //             Log::error('Failed to update Google Meet', $responseData);
            //             return redirect()->back()->with('error', $responseData['message'])->withInput();
            //         }
            //     } catch (\Exception $e) {
            //         Log::error('Exception when updating Google Meet', [
            //             'error' => $e->getMessage(),
            //             'trace' => $e->getTraceAsString()
            //         ]);
            //         return redirect()->back()->with('error', 'Failed to update Google Meet: ' . $e->getMessage())->withInput();
            //     }
            // }
            

            if ($request->hasFile('attachment')) 
            {
                $validated['attachment'] = $request->file('attachment')->store('attachments');
            }

            $meeting->update($validated);


            $meeting->participants()->sync($request->participant);


            return redirect()->route('meeting.index')->with('success', 'Meeting successfully updated');

        } catch (\Exception $e) {
            dd($e);
            Log::error('Error in update method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }
    }


    public function destroy($slug)
    {
        // if ($meeting->jenis_rapat === 'online' && $meeting->google_event_id) {
        //     try {
        //         $googleMeetResponse = $this->googleMeetController->deleteGoogleMeet($meeting->google_event_id);
        //         $responseData = json_decode($googleMeetResponse->getContent(), true);
                
        //         if (!$responseData['success']) {
        //             return redirect()->back()->with('error', 'Gagal menghapus Google Meet');
        //         }
        //     } catch (\Exception $e) {
        //         return redirect()->back()->with('error', 'Gagal menghapus Google Meet: ' . $e->getMessage());
        //     }
        // }
        // Hapus meeting participants
        $meeting = Meeting::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $meeting->participants()->delete();
        $meeting->delete();
        return redirect()->route('meeting.index')->with('success', 'Rapat berhasil dihapus');
        
    }


    public function ratingPage()
    {        
        return view('rating');
    }

    public function saveNotes(Request $request, $id)
    {
        $meeting = Meeting::findOrFail($id); // Ambil data rapat berdasarkan ID
        $meeting->notes = $request->input('notes'); // Perbarui field `notes`
        $meeting->save(); // Simpan perubahan ke database

        return redirect()->back()->with('success', 'Notulensi berhasil diperbarui!');
    }

    protected function sentMessage($userToId, $userFromId, $message, $directUrl = null, $isRead = false, $category = "entry")
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $userToId,
            $userFromId,
            $message,
            $directUrl,
            $isRead,
            $category
        );
    }
}
