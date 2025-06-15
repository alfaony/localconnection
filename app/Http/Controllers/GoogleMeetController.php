<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Meeting;
use Google\Service\Calendar;
use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Google\Service\Calendar\Event;
use Illuminate\Support\Facades\Log;
use Google\Service\Calendar\EventDateTime;

class GoogleMeetController extends Controller
{   
    public function redirectToGoogle()
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->setAccessType('offline'); 
        $client->setPrompt('consent'); 
        $client->addScope(Calendar::CALENDAR);

        // $client->addScope([Calendar::CALENDAR, Calendar::CALENDAR_EVENTS]);

        
        // Mengarahkan pengguna ke Google login
        $authUrl = $client->createAuthUrl();
        return redirect()->to($authUrl);
    }
    public function handleGoogleCallback(Request $request)
    {
        // Pastikan parameter kode ada di request
        $authCode = $request->input('code');
        if (!$authCode) {
            return redirect()->route('google.error')->with('error', 'Authorization code tidak ditemukan.');
        }

        // Inisialisasi Google Client
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));

        try {
            // Tukar kode otorisasi dengan token
            $token = $client->fetchAccessTokenWithAuthCode($authCode);

            // Periksa jika ada error saat menukar token
            if (isset($token['error'])) {
                return redirect()->route('google.error')->with('error', $token['error_description']);
            }

            // Simpan token ke file token.json
            $tokenPath = storage_path('app/token.json');
            file_put_contents($tokenPath, json_encode($token));

            // Redirect ke halaman sukses
            return redirect()->route('google.success')->with('success', 'Token berhasil disimpan.');
        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('Error saat menukar token:', ['message' => $e->getMessage()]);
            return redirect()->route('google.error')->with('error', 'Terjadi kesalahan saat memproses token.');
        }
    }

    public function createGoogleMeet(Request $request)
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));

        // Load token dari token.json
        $tokenPath = storage_path('app/token.json');
        $accessToken = null;

        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // Jika token kedaluwarsa, refresh token
        if ($client->isAccessTokenExpired() && $accessToken) {
            $client->fetchAccessTokenWithRefreshToken($accessToken['refresh_token']);
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }


        // Ambil email dari peserta berdasarkan ID yang ada
        $attendees = [];
        foreach ($request->peserta as $pesertaId) {
            $user = User::find($pesertaId);
            if ($user) {
                $attendees[] = ['email' => $user->email];
            }
        }

        foreach ($request->nama_pic as $picId) {
            $user = User::find($picId);
            if ($user) {
                $attendees[] = ['email' => $user->email];
            }
        }

        $startDateTime = new EventDateTime();
        $startDateTime->setDateTime(sprintf(
            '%sT%s:00+07:00', // Format ISO 8601
            $request->tanggal_mulai, // Input tanggal, contoh: 2025-01-20
            $request->jam_mulai      // Input jam, contoh: 10:00
        ));
        $startDateTime->setTimeZone('Asia/Jakarta'); // Zona waktu

        $endDateTime = new EventDateTime();
        $endDateTime->setDateTime(sprintf(
            '%sT%s:00+07:00', // Format ISO 8601
            $request->tanggal_berakhir, // Input tanggal, contoh: 2025-01-20
            $request->jam_berakhir      // Input jam, contoh: 11:00
        ));
        $endDateTime->setTimeZone('Asia/Jakarta');


        $service = new Calendar($client);

        Log::info('Event Data:', [
            'summary' => $request->nama_rapat,
            'start' => sprintf('%sT%s+07:00', $request->tanggal_mulai, $request->jam_mulai),
            'end' => sprintf('%sT%s+07:00', $request->tanggal_berakhir, $request->jam_berakhir),
            'attendees' => $attendees,
        ]);
        

        // Membuat event di Google Calendar
        $event = new Event([
            'summary' => $request->nama_rapat,
            'start' => $startDateTime,
            'description' => $request->agenda_rapat,
            'end' => $endDateTime,
            'attendees' => $attendees,
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => uniqid(),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet']
                ],
            ],
        ]);

        // Log::info('Final Event Data:', [
        //     'summary' => $request->nama_rapat,
        //     'start' => $startDateTime->getDateTime(),
        //     'description' => $event->description,
        //     'end' => $endDateTime->getDateTime(),
        //     'attendees' => $attendees,
        //     'conferenceData' => [
        //         'createRequest' => [
        //             'requestId' => uniqid(),
        //             'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
        //         ],
        //     ],
        // ]);
        Log::info('Access Token:', $client->getAccessToken());
        


        // Menyimpan event dan mendapatkan link Google Meet
        try {
            $createdEvent = $service->events->insert(
                'primary', 
                $event, 
                [
                    'conferenceDataVersion' => 1,
                    'sendUpdates' => 'all',
                ]
            );
            $googleMeetLink = $createdEvent->getHangoutLink();  // Mendapatkan link Google Meet
            $eventId = $createdEvent->getId(); // Ini adalah google_event_id
            // Debug untuk memastikan data yang didapat
            // Log::info('Created Google Meet Event:', [
            //     'event_id' => $eventId,
            //     'meet_link' => $googleMeetLink
            // ]);
            
            return response()->json([
                'success' => true, 
                'link' => $googleMeetLink,
                'event_id' => $eventId 
            ]);
            
        } catch (\Exception $e) {
            
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function getGoogleClient()
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));

        // Load token dari token.json
        $tokenPath = storage_path('app/token.json');
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // Jika token kedaluwarsa, refresh token
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            }
        }

        return $client;
    }

    public function deleteGoogleMeet($eventId)
    {
        try {
            // Inisialisasi Google Client
            $client = $this->getGoogleClient();
            
            // Buat instance Calendar Service
            $service = new Calendar($client);
            
            Log::info('Attempting to delete Google Calendar event', ['event_id' => $eventId]);

            // Hapus event dari Google Calendar
            $service->events->delete('primary', $eventId);
            
            Log::info('Successfully deleted Google Calendar event', ['event_id' => $eventId]);
            
            return response()->json([
                'success' => true,
                'message' => 'Event successfully deleted from Google Calendar'
            ]);
            
        } catch (\Google\Service\Exception $e) {
            Log::error('Google Service Exception when deleting event', [
                'event_id' => $eventId,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            
            // Jika event sudah tidak ada di Google Calendar (404)
            if ($e->getCode() === 404) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event not found in Google Calendar'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Google Calendar event: ' . $e->getMessage()
            ]);
            
        } catch (\Exception $e) {
            Log::error('General Exception when deleting event', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Google Calendar event: ' . $e->getMessage()
            ]);
        }
    }

    public function updateGoogleMeet(Meeting $meeting, Request $request)
    {
        try {
            $client = new GoogleClient();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(config('services.google.redirect_uri'));

            // Load token
            $tokenPath = storage_path('app/token.json');
            if (file_exists($tokenPath)) {
                $accessToken = json_decode(file_get_contents($tokenPath), true);
                $client->setAccessToken($accessToken);
            }

            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($accessToken['refresh_token']);
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            }

            $service = new Calendar($client);

            // Get existing event first
            $event = $service->events->get('primary', $meeting->google_event_id);

            // Prepare date times
            $startDateTime = new EventDateTime();
            $startDateTime->setDateTime(sprintf(
                '%sT%s+07:00', // Format ISO 8601
                $request->tanggal_mulai, // Contoh: 2025-01-17
                $request->jam_mulai      // Contoh: 10:00:00
            ));
            $startDateTime->setTimeZone('Asia/Jakarta'); // Zona waktu Asia/Jakarta

            $endDateTime = new EventDateTime();
            $endDateTime->setDateTime(sprintf(
                '%sT%s+07:00',
                $request->tanggal_berakhir, // Contoh: 2025-01-17
                $request->jam_berakhir      // Contoh: 11:00:00
            ));
            $endDateTime->setTimeZone('Asia/Jakarta');


            // Get updated attendees
            $attendees = [];
            foreach ($request->peserta as $pesertaId) {
                $user = User::find($pesertaId);
                if ($user) {
                    $attendees[] = ['email' => $user->email];
                }
            }

            foreach ($request->nama_pic as $picId) {
                $user = User::find($picId);
                if ($user) {
                    $attendees[] = ['email' => $user->email];
                }
            }

            // Update event properties
            $event->setSummary($request->nama_rapat);
            $event->setDescription($request->agenda_rapat);
            $event->setStart($startDateTime);
            $event->setEnd($endDateTime);
            $event->setAttendees($attendees);

            // Update the event
            $updatedEvent = $service->events->update(
                'primary', 
                $meeting->google_event_id, 
                $event, 
                ['conferenceDataVersion' => 1, 'sendUpdates' => 'all']
            );

            if (!$updatedEvent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate Google Meet'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Google Meet berhasil diupdate'
            ]);

        } catch (\Exception $e) {
            Log::error('Google Calendar Update Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate Google Meet: ' . $e->getMessage()
            ]);
        }
    }

}