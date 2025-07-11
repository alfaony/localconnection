<?php

namespace App\Services;

use App\Models\SettingCompany;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\EventAttendee;
use Illuminate\Support\Facades\Log;
use App\Schemas\ParamSchema;

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
            'access_token' => $this->setting['google_access_token'] ?? '',
            'refresh_token' => $this->setting['google_refresh_token'] ?? '',
            'expires_in' => (int) ($this->setting['google_expires_in'] ?? 3600),
            'created' => (int) ($this->setting['google_token_created_at'] ?? now()->subHour()->timestamp),
            'scope' => 'https://www.googleapis.com/auth/calendar',
            'token_type' => 'Bearer',
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

           $startDateTime = new \Google\Service\Calendar\EventDateTime([
                'dateTime' => \Carbon\Carbon::parse("{$payload['start_date']} {$payload['start_time']}", 'Asia/Jakarta')->toRfc3339String(),
                'timeZone' => 'Asia/Jakarta'
            ]);

            $endDateTime = new \Google\Service\Calendar\EventDateTime([
                'dateTime' => \Carbon\Carbon::parse("{$payload['end_date']} {$payload['end_time']}", 'Asia/Jakarta')->toRfc3339String(),
                'timeZone' => 'Asia/Jakarta'
            ]);

            $attendees = [];
            foreach ($meeting->combined_participants as $participant) 
            {
                $attendees[] = ['email' => $participant['email']];
            }

            // Set description
            $description = $payload['meeting_agenda'] ?? $meeting->meeting_agenda;
            if (($payload['meeting_type'] ?? $meeting->meeting_type) === 'online' && $meeting->google_meet_link) {
                $description .= "\n\nMeeting Link: " . $meeting->google_meet_link;
            }

            // Set event attributes
            $event->setSummary($payload['meeting_name'] ?? $meeting->meeting_name);
            $event->setDescription($description);
            $event->setStart($startDateTime);
            $event->setEnd($endDateTime);
            $event->setAttendees($attendees);

            // OPTIONS default
            $options = ['sendUpdates' => 'all'];

            // Jika GOOGLE_MEET dan belum ada conferenceData, generate baru
            if (($payload['meeting_type'] ?? $meeting->meeting_type) === 'google_meet') {
                if (!$event->getConferenceData()) {
                    $conferenceRequest = new \Google\Service\Calendar\CreateConferenceRequest([
                        'requestId' => uniqid(),
                        'conferenceSolutionKey' => new \Google\Service\Calendar\ConferenceSolutionKey([
                            'type' => 'hangoutsMeet'
                        ])
                    ]);
                    $conferenceData = new \Google\Service\Calendar\ConferenceData([
                        'createRequest' => $conferenceRequest
                    ]);
                    $event->setConferenceData($conferenceData);
                }

                $options['conferenceDataVersion'] = 1;
            }

            $this->calendar->events->update('primary', $meeting->google_event_id, $event, $options);
            
            return true;
        } catch (\Exception $e) {
            // dd($e);
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

            // Deskripsi
            $description = $meeting->meeting_agenda;
            if ($meeting->meeting_type === 'online' && $meeting->google_meet_link) {
                $description .= "\n\nMeeting Link: " . $meeting->google_meet_link;
            }

            // Buat event
            $eventData = [
                'summary' => $meeting->meeting_name,
                'description' => $description,
                'start' => $startDateTime,
                'end' => $endDateTime,
                'attendees' => $attendees,
            ];


            if ($meeting->meeting_type === ParamSchema::GOOGLE_MEET) {
                $eventData['conferenceData'] = [
                    'createRequest' => [
                        'requestId' => uniqid(),
                        'conferenceSolutionKey' => ['type' => 'hangoutsMeet']
                    ]
                ];
            }

            $event = new \Google\Service\Calendar\Event($eventData);

            $created = $calendar->events->insert('primary', $event, [
                'conferenceDataVersion' => 1,
                'sendUpdates' => 'all'
            ]);


            return response()->json([
                'success' => true,
                'link' => $meeting->meeting_type === ParamSchema::GOOGLE_MEET ? $created->getHangoutLink() : $meeting->google_meet_link,
                'event_id' => $created->getId(),
            ]);
        } catch (\Exception $e) {
            // dd($e);
            Log::error('Create Google Meet error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function addAttendeeToEvent(string $eventId, string $email, ?string $name = null): bool
    {
        try {
            $service = new Calendar($this->client);
            $calendarId = 'primary';

            // Ambil event yang sudah ada
            $event = $service->events->get($calendarId, $eventId);

            // Tambah attendee baru ke list yang sudah ada
            $attendees = $event->getAttendees() ?? [];

            // Cek duplikat email
            $alreadyAdded = collect($attendees)->contains(function ($a) use ($email) {
                return strtolower($a->email) === strtolower($email);
            });

            if ($alreadyAdded) {
                return true; // Jangan duplikat
            }

            // Buat attendee baru
            $attendees[] = new EventAttendee([
                'email' => $email,
                'displayName' => $name,
                'responseStatus' => 'accepted'
            ]);

            // Set kembali ke event
            $event->setAttendees($attendees);

            // Update ke Google
            $service->events->update($calendarId, $event->getId(), $event);

            return true;
        } catch (\Exception $e) {
            // dd($e);
            \Log::error('Failed to add attendee to event: ' . $e->getMessage());
            return false;
        }
    }

    public static function getPublicAuthUrl(string $redirectUri, $meeting): string
    {
        $state = base64_encode("$meeting->slug|$meeting->public_token");

        $google = new GoogleService($meeting->company_id);
        $client = $google->getClient();
        $client->setRedirectUri($redirectUri);
        $client->addScope(['email', 'profile']);
        $client->setState($state);


        return $client->createAuthUrl();
    }

    public static function getUserInfoFromCode(string $authCode, string $redirectUri, $meeting): ?array
    {
        try {
            $google = new GoogleService($meeting->company_id);
            $client = $google->getClient();
            $client->setRedirectUri($redirectUri);
            $client->addScope(['openid', 'email', 'profile']);

            $token = $client->fetchAccessTokenWithAuthCode($authCode);

            if (isset($token['error'])) {
                \Log::error('Google OAuth Token Error', ['error' => $token]);
                return null;
            }

            $client->setAccessToken($token);

            $oauth2 = new \Google\Service\Oauth2($client);
            $userInfo = $oauth2->userinfo->get();

            return [
                'email' => $userInfo->email,
                'name' => $userInfo->name,
            ];
        } catch (\Exception $e) {
            \Log::error('Google OAuth Info Error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public static function checkGoogleConnection($companyId): bool
    {
        try {
            $service = new GoogleService($companyId);
            // Test panggil calendar list → jika error, berarti tidak valid
            $calendarList = $service->getCalendarService()->calendarList->listCalendarList();
            return true;
        } catch (\Exception $e) {
            \Log::warning("[GoogleService] Tidak terhubung ke Google: " . $e->getMessage());
            return false;
        }
    }
}
