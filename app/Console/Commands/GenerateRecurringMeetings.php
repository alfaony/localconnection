<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MeetingRecurrence;
use App\Models\Meeting;
use App\Models\NationalHoliday;
use Carbon\Carbon;

class GenerateRecurringMeetings extends Command
{
    protected $signature = 'recurring:generate-meetings';
    protected $description = 'Generate daily, monthly, and yearly recurring meetings for today';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $today = Carbon::today();
        
        // 1. Cek jika hari ini adalah hari libur nasional
        if ($this->isNationalHoliday($today)) {
            $this->info("Hari ini adalah hari libur nasional. Recurring meeting tidak di-generate.");
            return;
        }

        // 2. Ambil semua recurrences yang aktif
        $activeRecurrences = MeetingRecurrence::where('is_active', true)->with('templateMeeting')->get();

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
                    $newMeeting->status = 'Pending'; // Override status jika perlu
                    $newMeeting->save();

                    // Copy partisipan
                    $participants = $template->participants()->get();
                    if ($participants->isNotEmpty()) {
                        // attach partisipan tanpa nilai yang existing (default baru)
                        foreach ($participants as $participant) {
                            $newMeeting->participants()->attach($participant->id, [
                                'is_attended' => false,
                                'join_time' => null
                            ]);
                        }
                    }

                    $generatedCount++;
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
