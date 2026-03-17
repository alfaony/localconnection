<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MeetingRecurrence;
use App\Models\Meeting;
use App\Models\NationalHoliday;
use Carbon\Carbon;
use App\Services\GoogleService;
use App\Schemas\ParamSchema;
use Illuminate\Support\Str;
use App\Helpers\InboxHelper;

class GenerateRecurringMeetings extends Command
{
    protected $signature = 'recurring:generate-meetings {slug?} {--date=} {--simulate} {--test}';
    protected $description = 'Generate daily, monthly, and yearly recurring meetings for today or simulate/test by slug';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $slug = $this->argument('slug');
        $dateOpt = $this->option('date');
        $isSimulation = $this->option('simulate');
        $isTest = $this->option('test');

        $today = $dateOpt ? Carbon::parse($dateOpt) : Carbon::today();
        
        $this->info("Menjalankan scheduler untuk tanggal: " . $today->toDateString());

        // 1. Cek jika hari ini adalah hari libur nasional
        if ($this->isNationalHoliday($today)) {
            $this->info("Hari ini adalah hari libur nasional. Recurring meeting tidak di-generate.");
            return;
        }

        // 2. Ambil semua recurrences yang aktif
        $query = MeetingRecurrence::where('is_active', true)->with('templateMeeting');
        
        if ($slug) {
            $query->whereHas('templateMeeting', function ($q) use ($slug) {
                $q->where('slug', $slug);
            });
            $this->info("Memfilter data untuk simulasi dengan slug meeting: {$slug}");
        }

        $activeRecurrences = $query->get();

        $generatedCount = 0;

        foreach ($activeRecurrences as $recurrence) {
            $template = $recurrence->templateMeeting;
            
            // Skip jika template terhapus
            if (!$template) {
                continue;
            }

            $shouldGenerate = false;

            switch ($recurrence->recurring_type) {
                case 'daily': // Harian
                    $dayNameId = $this->getIndonesianDayName($today->dayOfWeek);
                    $days = $recurrence->recurring_daily_days ?? [];
                    
                    if (in_array($dayNameId, $days) || in_array($today->format('l'), $days)) {
                        $shouldGenerate = true;
                    }
                    if (empty($days)) {
                        $shouldGenerate = true; // Jika tidak ada hari terpilih, default tiap hari untuk 'daily'
                    }
                    break;
                case 'monthly': // Bulanan
                    if ($today->day == $recurrence->recurring_monthly_date) {
                        $shouldGenerate = true;
                    }
                    break;
                case 'yearly': // Tahunan
                    if ($today->month == $recurrence->recurring_yearly_month && $today->day == $recurrence->recurring_yearly_date) {
                        $shouldGenerate = true;
                    }
                    break;
            }

            if ($shouldGenerate) {
                // Cek apakah hari ini sudah di-generate dari recurrence ini
                $alreadyGenerated = Meeting::where('meeting_recurrence_id', $recurrence->id)
                    ->whereDate('start_date', $today->toDateString())
                    ->exists();

                if (!$alreadyGenerated) {
                    if ($isSimulation) {
                        $this->info("[SIMULASI] Meeting '{$template->meeting_name}' akan di-generate untuk tanggal {$today->toDateString()}.");
                        $generatedCount++;
                        continue;
                    }

                    $newMeeting = $template->replicate();
                    // Set parameter baru
                    $newMeeting->meeting_name = $template->meeting_name; // trigger slug regenerasi jika butuh
                    $newMeeting->start_date = $today->toDateString();
                    $newMeeting->end_date = $today->toDateString(); 
                    
                    // Pertahankan durasi jika ada
                    if ($template->start_date && $template->end_date) {
                        $diffInDays = Carbon::parse($template->start_date)->diffInDays(Carbon::parse($template->end_date));
                        if ($diffInDays > 0) {
                            $newMeeting->end_date = $today->copy()->addDays($diffInDays)->toDateString();
                        }
                    }

                    $newMeeting->meeting_recurrence_id = $recurrence->id;
                    $newMeeting->status = 'scheduled'; // Override status
                    
                    // Clear google event details initially so that it's generated anew
                    if ($template->meeting_type === ParamSchema::GOOGLE_MEET || $template->meeting_type === 'online') {
                         $newMeeting->google_meet_link = null;
                         $newMeeting->google_event_id = null;
                         $newMeeting->public_token = null;
                         $newMeeting->public_code = null;
                         $newMeeting->public_token_generated_at = null;
                    }
                    
                    $newMeeting->save();

                    // Generate New Google Meet Event if needed
                    if ($template->meeting_type === ParamSchema::GOOGLE_MEET || $template->meeting_type === 'online') {
                        $googleService = new GoogleService($template->company_id);

                        $googleMeet = $googleService->createGoogleMeet($newMeeting);
                        $googleMeetData = $googleMeet->getData();
                        if ($googleMeetData->success) {
                            $newMeeting->update([
                                'google_meet_link' => $googleMeetData->link,
                                'google_event_id' => $googleMeetData->event_id,
                                'public_token' => Str::random(10),
                                'public_code' => Str::random(5),
                                'public_token_generated_at' => now(),
                            ]);
                        }
                    }
                    // Copy partisipan
                    $participants = $template->participants()->get();
                    if ($participants->isNotEmpty()) {
                        $inboxHelper = new InboxHelper();
                        $message = "Undangan Rapat Rutin - " . $newMeeting->meeting_name;
                        // Since newMeeting corresponds to the template but replicated, its slug may not exist yet if not created?
                        // Model usually handles slug via boot traits upon save, so it should be there.
                        // Let's ensure slug exists
                        $url = route('meeting.show', $newMeeting->slug ?? '');

                        // attach partisipan tanpa nilai yang existing (default baru)
                        foreach ($participants as $participant) {
                            $newMeeting->participants()->attach($participant->id, [
                                'is_attended' => false,
                                'join_time' => null
                            ]);
                            
                            // Kirim inbox ke partisipan (kecuali creator / sender sendiri)
                            if ($participant->id !== $newMeeting->user_id) {
                                $inboxHelper->sent(
                                    $participant->id,
                                    $newMeeting->user_id,
                                    $message,
                                    $url,
                                    false,
                                    'email'
                                );
                            }
                        }
                    }

                    if ($isTest) {
                        $this->info("🧪 [TEST] Meeting '{$template->meeting_name}' telah BENAR-BENAR dibuat di database untuk tanggal {$today->toDateString()}.");
                    } else {
                        $this->info("✅ Meeting '{$template->meeting_name}' berhasil di-generate untuk tanggal {$today->toDateString()}.");
                    }
                    
                    $generatedCount++;
                } else {
                    $this->info("⚠ Meeting '{$template->meeting_name}' sudah ada untuk tanggal {$today->toDateString()}. (Skip)");
                }
            } else {
                if ($slug) {
                    $this->info("❌ Meeting '{$template->meeting_name}' tidak masuk kriteria jadwal pada tanggal {$today->toDateString()}.");
                }
            }
        }

        $this->info("Berhasil men-generate {$generatedCount} recurring meeting.");
    }

    private function isNationalHoliday($date)
    {
        return NationalHoliday::where('date', $date->toDateString())->exists();
    }

    private function getIndonesianDayName($dayOfWeekNumber)
    {
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        return $days[$dayOfWeekNumber] ?? '';
    }
}
