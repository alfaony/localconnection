<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\EmployeeChecking;
use App\Events\EmployeeCheckinActivated;
use App\Events\EmployeeCheckinDeactivated;
use Carbon\Carbon;

/**
 * Simulasi employee check-in untuk testing WebSocket.
 *
 * Mode:
 *  --count=N   → buat N jadwal mulai 2 menit dari sekarang (window 5 menit, gap 1 menit)
 *  (tanpa flag) → buat 1 jadwal dan langsung broadcast SEKARANG
 *  --deactivate=ID → paksa tutup popup
 *  --list      → lihat semua checkin hari ini
 */
class SimulateEmployeeCheckin extends Command
{
    protected $signature = 'checkin:simulate
                            {--email=      : Email user yang akan disimulasikan}
                            {--count=      : Buat N jadwal (1-10) mulai 2 menit dari sekarang}
                            {--deactivate= : ID checkin yang akan di-deactivate}
                            {--list        : Lihat daftar checkin hari ini}
                            {--duration=30 : Durasi window (detik) untuk mode tanpa --count}';

    protected $description = 'Simulasikan employee check-in via WebSocket untuk testing';

    // Konstanta jadwal --count
    private const DELAY_MINUTES  = 2; // mulai berapa menit dari sekarang
    private const WINDOW_MINUTES = 5; // durasi tiap window
    private const GAP_MINUTES    = 1; // jeda antar window

    public function handle(): int
    {
        $email = $this->option('email');

        if (!$email) {
            $this->error('Opsi --email wajib. Contoh: php artisan checkin:simulate --email=budi@example.com --count=4');
            return 1;
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User [{$email}] tidak ditemukan.");
            return 1;
        }

        if ($this->option('list'))       return $this->listTodayCheckins($user);
        if ($this->option('deactivate')) return $this->deactivateCheckin($user, (int) $this->option('deactivate'));
        if ($this->option('count'))      return $this->createCountCheckins($user, (int) $this->option('count'));

        // Default: langsung broadcast 1 checkin sekarang
        return $this->fireNow($user);
    }

    // ──────────────────────────────────────────────────────────────
    // MODE --count=N
    // Buat N records dengan jadwal staggered, biarkan scheduler trigger
    // ──────────────────────────────────────────────────────────────
    private function createCountCheckins(User $user, int $count): int
    {
        if ($count < 1 || $count > 10) {
            $this->error('--count harus antara 1 dan 10.');
            return 1;
        }

        $division = $user->divisions->first();
        if (!$division) {
            $this->error("User [{$user->name}] belum punya division.");
            return 1;
        }

        if (!$user->is_checkin) {
            $this->warn("User [{$user->name}] memiliki is_checkin = false.");
            if (!$this->confirm('Lanjutkan?')) return 0;
        }

        $now      = Carbon::now();
        $rows     = [];
        $created  = [];

        for ($i = 0; $i < $count; $i++) {
            // Checkin ke-i mulai setelah DELAY + (WINDOW + GAP) * i menit
            $startOffset = self::DELAY_MINUTES + $i * (self::WINDOW_MINUTES + self::GAP_MINUTES);
            $start   = $now->copy()->addMinutes($startOffset);
            $timeout = $start->copy()->addMinutes(self::WINDOW_MINUTES);

            $checkin = EmployeeChecking::create([
                'user_id'           => $user->id,
                'division_id'       => $division->id,
                'scheduled_time'    => $start,
                'scheduled_timeout' => $timeout,
                'is_active'         => true,
                'is_completed'      => false,
            ]);

            $created[] = $checkin;
            $rows[]    = [
                $i + 1,
                $checkin->id,
                $start->format('H:i'),
                $timeout->format('H:i'),
                "php artisan checkin:active --id={$checkin->id}",
            ];
        }

        $this->newLine();
        $this->line("  <info>User:</info> {$user->name} ({$user->email})");
        $this->line("  <info>Dibuat:</info> {$count} jadwal checkin");
        $this->newLine();

        $this->table(
            ['#', 'ID', 'Mulai', 'Timeout', 'Trigger manual (jika scheduler off)'],
            $rows
        );

        $this->newLine();
        $this->info('✅ Jadwal berhasil dibuat.');
        $this->line('');
        $this->line('  <comment>Scheduler berjalan?</comment> Popup akan muncul otomatis di browser pada waktu yang tertera.');
        $this->line('  <comment>Scheduler off?</comment>    Jalankan perintah "Trigger manual" di kolom terakhir saat waktunya tiba.');
        $this->line('');
        $this->line('  Lihat status: <comment>php artisan checkin:simulate --email=' . $user->email . ' --list</comment>');
        $this->newLine();

        return 0;
    }

    // ──────────────────────────────────────────────────────────────
    // MODE default — langsung broadcast 1 checkin SEKARANG
    // ──────────────────────────────────────────────────────────────
    private function fireNow(User $user): int
    {
        $division = $user->divisions->first();
        if (!$division) {
            $this->error("User [{$user->name}] belum punya division.");
            return 1;
        }

        if (!$user->is_checkin) {
            $this->warn("User [{$user->name}] is_checkin = false.");
            if (!$this->confirm('Lanjutkan?')) return 0;
        }

        $durationSec = max(10, (int) $this->option('duration'));
        $now         = Carbon::now();
        $timeout     = $now->copy()->addSeconds($durationSec);

        $checkin = EmployeeChecking::create([
            'user_id'           => $user->id,
            'division_id'       => $division->id,
            'scheduled_time'    => $now,
            'scheduled_timeout' => $timeout,
            'is_active'         => true,
            'is_completed'      => false,
        ]);

        $this->line("  Record dibuat: <info>ID={$checkin->id}</info>");
        $this->line("  Window: <info>{$now->format('H:i:s')} – {$timeout->format('H:i:s')}</info> ({$durationSec}s)");

        try {
            broadcast(new EmployeeCheckinActivated($checkin));
            $this->info('✅ Broadcast dikirim — popup harus muncul di browser sekarang.');
        } catch (\Throwable $e) {
            $this->error('❌ Broadcast gagal: ' . $e->getMessage());
            $this->warn('   Pastikan Reverb server berjalan.');
            return 1;
        }

        $this->newLine();
        $this->table(
            ['Info', 'Value'],
            [
                ['ID', $checkin->id],
                ['Window habis', $timeout->format('H:i:s')],
                ['Deactivate', "php artisan checkin:simulate --email={$user->email} --deactivate={$checkin->id}"],
            ]
        );

        return 0;
    }

    // ──────────────────────────────────────────────────────────────
    // MODE --deactivate=ID
    // ──────────────────────────────────────────────────────────────
    private function deactivateCheckin(User $user, int $checkinId): int
    {
        $checkin = EmployeeChecking::where('id', $checkinId)
            ->where('user_id', $user->id)
            ->first();

        if (!$checkin) {
            $this->error("Checkin ID={$checkinId} tidak ditemukan untuk [{$user->email}].");
            return 1;
        }

        $checkin->is_active = false;
        $checkin->save();

        try {
            broadcast(new EmployeeCheckinDeactivated($user->id, $checkinId));
            $this->info('✅ Broadcast Deactivated dikirim — popup di browser harus tertutup.');
        } catch (\Throwable $e) {
            $this->error('❌ Broadcast gagal: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    // ──────────────────────────────────────────────────────────────
    // MODE --list
    // ──────────────────────────────────────────────────────────────
    private function listTodayCheckins(User $user): int
    {
        $checkins = EmployeeChecking::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->orderBy('scheduled_time')
            ->get(['id', 'scheduled_time', 'scheduled_timeout', 'is_active', 'is_completed', 'is_dayoff']);

        if ($checkins->isEmpty()) {
            $this->warn("Tidak ada checkin hari ini untuk [{$user->email}].");
            return 0;
        }

        $this->line("Checkin hari ini — <info>{$user->name}</info> ({$user->email}):");
        $this->table(
            ['ID', 'Mulai', 'Timeout', 'Active', 'Completed', 'Dayoff'],
            $checkins->map(fn ($c) => [
                $c->id,
                Carbon::parse($c->scheduled_time)->format('H:i:s'),
                $c->scheduled_timeout ? Carbon::parse($c->scheduled_timeout)->format('H:i:s') : '–',
                $c->is_active    ? '<info>✓</info>'    : '–',
                $c->is_completed ? '<info>✓</info>'    : '–',
                $c->is_dayoff    ? '<comment>✓</comment>' : '–',
            ])->toArray()
        );

        return 0;
    }
}
