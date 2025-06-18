<?php

namespace App\Services;

use App\Models\SettingCompany;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Illuminate\Support\Facades\Log;

class GoogleService
{
    protected $client;
    protected $calendar;
    protected $companyId;
    protected $setting;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
        $this->setting = SettingCompany::byCompany($companyId)->where('menu', 'google')->get()->pluck('field_value', 'field_title');

        $this->client = new GoogleClient();
        $this->client->setClientId($this->setting['google_client_id'] ?? '');
        $this->client->setClientSecret($this->setting['google_client_secret'] ?? '');
        $this->client->setRedirectUri(config('services.google.redirect_uri'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->addScope(Calendar::CALENDAR);
        $this->client->setState($companyId);

        $accessToken = [
            'access_token' => $this->setting['google_access_token'] ?? null,
            'refresh_token' => $this->setting['google_refresh_token'] ?? null,
            'expires_in' => (int) ($this->setting['google_expires_in'] ?? 3600),
            'created' => (int) ($this->setting['google_token_created_at'] ?? now()->subHour()->timestamp),
        ];

        $this->client->setAccessToken($accessToken);

        // Refresh jika expired
        if ($this->client->isAccessTokenExpired() && $this->client->getRefreshToken()) {
            $newToken = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());

            if (!isset($newToken['error'])) {
                SettingCompany::byCompany($companyId)->where('menu', 'google')->where('field_title', 'google_access_token')->update([
                    'field_value' => $newToken['access_token']
                ]);

                SettingCompany::byCompany($companyId)->where('menu', 'google')->where('field_title', 'google_expires_in')->update([
                    'field_value' => $newToken['expires_in']
                ]);

                SettingCompany::byCompany($companyId)->where('menu', 'google')->where('field_title', 'google_token_created_at')->update([
                    'field_value' => now()->timestamp
                ]);
            }
        }

        $this->calendar = new Calendar($this->client);
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function fetchAndSaveToken($authCode): bool
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($authCode);

        if (isset($token['error'])) {
            return false;
        }

        SettingCompany::byCompany($this->companyId)->where('menu', 'google')->where('field_title', 'google_access_token')->update([
            'field_value' => $token['access_token']
        ]);

        SettingCompany::byCompany($this->companyId)->where('menu', 'google')->where('field_title', 'google_refresh_token')->update([
            'field_value' => $token['refresh_token'] ?? ''
        ]);

        SettingCompany::byCompany($this->companyId)->where('menu', 'google')->where('field_title', 'google_expires_in')->update([
            'field_value' => $token['expires_in']
        ]);

        SettingCompany::byCompany($this->companyId)->where('menu', 'google')->where('field_title', 'google_token_created_at')->update([
            'field_value' => now()->timestamp
        ]);

        return true;
    }

    public function getClient(): GoogleClient
    {
        return $this->client;
    }

    public function getCalendarService(): Calendar
    {
        return $this->calendar;
    }

    public function deleteEvent(string $eventId): bool
    {
        try {
            $this->calendar->events->delete('primary', $eventId);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete Google event', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function updateGoogleMeet($meeting, array $payload): bool
    {
        try {
            $event = $this->calendar->events->get('primary', $meeting->google_event_id);

            $start = new \Google\Service\Calendar\EventDateTime();
            $start->setDateTime("{$payload['start_date']}T{$payload['start_time']}:00+07:00");
            $start->setTimeZone('Asia/Jakarta');

            $end = new \Google\Service\Calendar\EventDateTime();
            $end->setDateTime("{$payload['end_date']}T{$payload['end_time']}:00+07:00");
            $end->setTimeZone('Asia/Jakarta');

            $attendees = [];
            foreach ($meeting->combined_participants as $participant) 
            {
                $attendees[] = ['email' => $participant['email']];
            }

            $event->setSummary($payload['meeting_name'] ?? $meeting->meeting_name);
            $event->setDescription($payload['meeting_agenda'] ?? $meeting->meeting_agenda);
            $event->setStart($start);
            $event->setEnd($end);
            $event->setAttendees($attendees);

            $this->calendar->events->update('primary', $meeting->google_event_id, $event, [
                'conferenceDataVersion' => 1,
                'sendUpdates' => 'all',
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to update Google event', ['message' => $e->getMessage()]);
            return false;
        }
    }


    public function createGoogleMeet($meeting)
    {
        try {
            $google = new GoogleService($meeting->company_id);
            $client = $google->getClient();
            $calendar = $google->getCalendarService();
            
            // Attendees dari peserta
            $attendees = [];
            foreach ($meeting->combined_participants as $participant) 
            {
                $attendees[] = ['email' => $participant['email']];
            }

            // Tambahkan PIC (hanya satu, dari user_id)
            if ($meeting->user) 
            {
                $attendees[] = ['email' => $meeting->user->email];
                // $attendees[] = ['email' => "alfaony@gmail.com"];
                // $attendees[] = ['email' => "alfaony.thrive@gmail.com"];
            }

            // Waktu mulai dan akhir
            $startDateTime = new \Google\Service\Calendar\EventDateTime([
                'dateTime' => "{$meeting->start_date}T{$meeting->start_time}:00+07:00",
                'timeZone' => 'Asia/Jakarta'
            ]);

            $endDateTime = new \Google\Service\Calendar\EventDateTime([
                'dateTime' => "{$meeting->end_date}T{$meeting->end_time}:00+07:00",
                'timeZone' => 'Asia/Jakarta'
            ]);

            // Buat event
            $event = new \Google\Service\Calendar\Event([
                'summary' => $meeting->meeting_name,
                'description' => $meeting->meeting_agenda,
                'start' => $startDateTime,
                'end' => $endDateTime,
                'attendees' => $attendees,
                'conferenceData' => [
                    'createRequest' => [
                        'requestId' => uniqid(),
                        'conferenceSolutionKey' => ['type' => 'hangoutsMeet']
                    ]
                ]
            ]);


            $created = $calendar->events->insert('primary', $event, [
                'conferenceDataVersion' => 1,
                'sendUpdates' => 'all'
            ]);

            return response()->json([
                'success' => true,
                'link' => $created->getHangoutLink(),
                'event_id' => $created->getId()
            ]);
        } catch (\Exception $e) {
            Log::error('Create Google Meet error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
