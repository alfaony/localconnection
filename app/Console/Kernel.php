<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\{
    SyncRouterInventoryJob, 
    SyncActiveSessionsJob, 
    SyncInstalledCustomersJob,
    RouterHealthCheckJob,
    BatchSyncInstalledCustomersJob // ✅ NEW: Batch job
};
use App\Models\{Router, InternetCustomer, Company, EmployeeChecking, SettingCompany};
use Carbon\Carbon;

use App\Schemas\ParamSchema;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //=============== SYNC ROUTER ===============
        // $routers = Router::cursor();

        // foreach ($routers as $router) {
        //     if ($router->active != 'UP') continue;
        //     $off = ((int) $router->id) % 30;

        //     // 1) Harian
        //     $schedule->call(function () use ($router) {
        //             dispatch((new SyncRouterInventoryJob(
        //                 routerId: $router->id,
        //                 withProfiles: false,
        //                 withSecrets:  false,
        //                 withSessions: false,
        //                 withPppoe:    true,
        //             ))->onQueue('mikrotik'));
        //         })
        //         ->name("router-sync-{$router->id}")      // ← WAJIB untuk onOneServer/withoutOverlapping
        //         ->dailyAt(sprintf('03:%02d', $off))
        //         ->onOneServer()
        //         ->withoutOverlapping()                   // ← tanpa argumen di versi baru
        //         ->appendOutputTo(storage_path("logs/sync_router_{$router->id}.log"));

        //     // 2) Tiap jam
        //     $schedule->call(function () use ($router) {
        //             dispatch((new SyncRouterInventoryJob(
        //                 routerId: $router->id,
        //                 withProfiles: true,
        //                 withSecrets:  true,
        //             ))->onQueue('mikrotik'));
        //         })
        //         ->name("router-profsec-{$router->id}")
        //         ->hourlyAt($off)
        //         ->onOneServer()
        //         ->withoutOverlapping()
        //         ->appendOutputTo(storage_path("logs/sync_profsec_{$router->id}.log"));

        //     // 3) Tiap 5 menit
        //     $schedule->job((new SyncActiveSessionsJob($router->id))->onQueue('mikrotik'))
        //         ->name("router-sessions-{$router->id}")
        //         ->everyFiveMinutes()
        //         ->onOneServer()
        //         ->withoutOverlapping();
        // }


        // // Checking Customer
        // $customerToActive = InternetCustomer::query()
        //     ->with('router')
        //     ->whereIn('status', [ParamSchema::INSTALLED, ParamSchema::REACTIVATED])
        //     ->whereNotNull('router_id')
        //     ->whereNotNull('username')
        //     ->get()
        //     ;

        // foreach ($customerToActive as $customer) {
        //     $schedule->job(new SyncInstalledCustomersJob([$customer->id]))
        //         ->name("sync-installed-{$customer->id}")   // kunci mutex unik per customer
        //         ->everyMinute()
        //         ->onOneServer()
        //         ->withoutOverlapping();
        // }

        $schedule->command('customers:check-active')
        ->hourly()
        ->withoutOverlapping(10) // Prevent overlap, timeout after 10 mins
        ->runInBackground()
        ->onOneServer(); // Only run on one server if multiple server

         // =============== ROUTER HEALTH CHECKS ===============
        // ✅ Run every 2 minutes, dispatch jobs untuk check all routers
        $schedule->call(function () {
            $this->scheduleRouterHealthChecks();
        })
            ->name('dispatch-router-health-checks')
            ->everyTwoMinutes()
            ->withoutOverlapping(5); // 5 min expiry

        // =============== ROUTER SYNC JOBS ===============
        // ✅ Run once per hour, dispatch sync jobs untuk online routers only
        $schedule->call(function () {
            $this->scheduleRouterSyncJobs();
        })
            ->name('dispatch-router-sync-jobs')
            ->hourly()
            ->withoutOverlapping(10);

        // =============== CUSTOMER SYNC ===============
        // ✅ IMPROVED: Batch processing instead of individual schedules
        $schedule->job(new BatchSyncInstalledCustomersJob())
            ->name('batch-sync-installed-customers')
            ->everyFiveMinutes()
            // ->everyMinute()
            ->withoutOverlapping();
        
        // =============== END SYNC ROUTER ===============



        // Tetapkan zona waktu Asia/Jakarta
        // Jadwalkan pekerjaan 'project:reccuring' setiap hari pada pukul 00:00
        // $schedule->command('project:reccuring')->timezone('Asia/Jakarta')->dailyAt('00:00');

        // =============== BILLING & ISOLIR SCHEDULE ===============
        // Generate billing untuk customer yang start_billing_date = today (jam 07:00 pagi)
        $schedule->command('billing-or-isolir:generate --type=billing')
            ->timezone('Asia/Jakarta')
            ->dailyAt('07:00')
            ->withoutOverlapping(10)
            ->appendOutputTo(storage_path('logs/billing.log'));

        // Generate isolir untuk customer yang end_billing_date = today (jam 23:45 malam)
        $schedule->command('billing-or-isolir:generate --type=isolir')
            ->timezone('Asia/Jakarta')
            ->dailyAt('23:45')
            ->withoutOverlapping(10)
            ->appendOutputTo(storage_path('logs/isolir.log'));

        // =============== SUBSCRIPTION EXPIRY =================
        // Notifikasi 7 hari, 3 hari, dan hari-H sekaligus
        $schedule->command('subscription:notify-expiry --days=all')
            ->timezone('Asia/Jakarta')
            ->dailyAt('08:00')
            ->withoutOverlapping(5)
            ->appendOutputTo(storage_path('logs/subscription_expiry.log'));

        // Expired: set status expired untuk subscription yang sudah melewati tanggal_expired
        $schedule->command('subscription:expire-overdue')
            ->timezone('Asia/Jakarta')
            ->dailyAt('00:05')
            ->withoutOverlapping(5);

        // Auto-release: bebaskan slot dari subscription unpaid yang melewati deadline reservasi
        $schedule->command('subscription:auto-release-slots')
            ->timezone('Asia/Jakarta')
            ->hourly()
            ->withoutOverlapping(5)
            ->appendOutputTo(storage_path('logs/subscription_auto_release.log'));
        // =============== END SUBSCRIPTION EXPIRY =============
        // =============== END BILLING & ISOLIR ===============

        // Send billing reminder untuk customer yang end_billing_date = today (jam 17:00)
        $schedule->command('billing:send-reminder')->timezone('Asia/Jakarta')->dailyAt('18:00');

        $schedule->command('project:set-status-sent-time')->timezone('Asia/Jakarta')->dailyAt('00:00');
        $schedule->command('tasks:process-recurring')->timezone('Asia/Jakarta')->dailyAt('00:00');
        $schedule->command('recurring:generate')->timezone('Asia/Jakarta')->dailyAt('01:00');
        $schedule->command('media:cleanup-temporary')->timezone('Asia/Jakarta')->dailyAt('00:00');
        $schedule->command('dayoff:reset-quota')->timezone('Asia/Jakarta')->yearlyOn(1, 1, '02:00');
        $schedule->command('weekly:check-compliance')->timezone('Asia/Jakarta')->mondays()->at('3:00');
        $schedule->command('dailytask:check-status')->timezone('Asia/Jakarta')->dailyAt('00:00');
        
        $company = Company::all();
        foreach ($company as $a) 
        {
            $schedule->command("validity:userOfCompany --id={$a->id} --type=wfo")->timezone('Asia/Jakarta')
            ->dailyAt('23:00')
                        // ->dailyAt('14:36')
            ;

            

            $settingCompany = SettingCompany::byCompany($a->id)->get()->pluck('field_value','field_title');
            
            $rangeEndDate = $settingCompany['range_end_date'] ?? NULL;
            if($rangeEndDate != "")
            {
                $dateRun = Carbon::now()->startOfMonth()->setDay($rangeEndDate);

                // Jika hari ini adalah $dateRun, jadwalkan command pada pukul 23:00
                if (Carbon::now('Asia/Jakarta')->isSameDay($dateRun)) {
                    $schedule->command("validity:userOfCompany --id={$a->id} --type=wfh")
                        ->timezone('Asia/Jakarta')
                        // ->dailyAt('14:36')
                        ->dailyAt('23:00')
                        ;
                }
            }

            $sentTime = $settingCompany['sent_time'] ?? NULL;
            if($sentTime != "")
            {
                $schedule->command('project:send-expiration-notifications')->timezone('Asia/Jakarta')->dailyAt($sentTime);
            }

            $schedule->command('quota:ensure-locks --company_id=' . $a->id)
            ->timezone('Asia/Jakarta')
            ->dailyAt('01:00')
            ->withoutOverlapping(10)
            ->runInBackground(); // Optional: prevent blocking
        }

        // Run Scheduler
        $schedule->command('schedule:employee-checkin')->dailyAt('07:00');

        $employeeCheckings = EmployeeChecking::where('is_active', true)
            ->where('is_dayoff', false)
            ->whereDate('scheduled_time', Carbon::today()) // Filter today's check-ins
            ->whereBetween('scheduled_time', [
                Carbon::now('Asia/Jakarta')->format('Y-m-d H:i'),
                Carbon::now('Asia/Jakarta')->addMinute(2)->format('Y-m-d H:i')
            ])
            ->get();
        

        foreach ($employeeCheckings as $checking) 
        {
            // Calculate the notification time (1 minute before scheduled time)
            $checkinNotificationTime = Carbon::parse($checking->scheduled_time);
            // $checkinDeactivateTime = Carbon::parse($checking->scheduled_timeout);
        
            $schedule->command("checkin:active --id={$checking->id}")->timezone('Asia/Jakarta')->dailyAt($checkinNotificationTime->format('H:i'));
            // $schedule->command("checkin:deactivate --id={$checking->id}")->timezone('Asia/Jakarta')->dailyAt($checkinDeactivateTime->format('H:i'));
        }

        $employeeCheckingDeactives = EmployeeChecking::where('is_active', true)
            ->where('is_dayoff', false)
            ->whereDate('scheduled_timeout', Carbon::today()) // Filter today's check-ins
            ->whereBetween('scheduled_timeout', [
                Carbon::now('Asia/Jakarta')->format('Y-m-d H:i'),
                Carbon::now('Asia/Jakarta')->addMinute(2)->format('Y-m-d H:i')
            ])
            ->get();

        foreach ($employeeCheckingDeactives as $checking) 
        {
            // Calculate the notification time (1 minute before scheduled time)
            $checkinDeactivateTime = Carbon::parse($checking->scheduled_timeout);
            $schedule->command("checkin:deactivate --id={$checking->id}")->timezone('Asia/Jakarta')->dailyAt($checkinDeactivateTime->format('H:i'));
        }

        // foreach ($employeeCheckings as $checking) 
        // {
        //     // Calculate the notification time (1 minute before scheduled time)
        //     $checkinNotificationTime = Carbon::parse($checking->scheduled_time);
            
        //     // Schedule the notification 1 minute before check-in time
        //     $schedule->command('checkin:notifyAndSentPopup')
        //         ->timezone('Asia/Jakarta')
        //         ->dailyAt($checkinNotificationTime->format('H:i'));

        //     // Calculate the deactivation time (2 minutes after scheduled time)
        //     $checkinDeactivateTime = Carbon::parse($checking->scheduled_timeout);

        //     // Schedule the deactivation 2 minutes after check-in time
        //     $schedule->command('checkin:deactivateAndRemove')
        //         ->timezone('Asia/Jakarta')
        //         ->dailyAt($checkinDeactivateTime->format('H:i'));
        // }
    }

    /**
     * ✅ NEW: Dispatch health check jobs untuk semua routers
     */
    protected function scheduleRouterHealthChecks(): void
    {
        // Only check routers yang perlu di-check
        Router::query()
            ->whereNotNull('host')
            ->chunk(50, function ($routers) {
                foreach ($routers as $router) {
                    if ($router->needsHealthCheck()) {
                        dispatch(new RouterHealthCheckJob($router->id))
                            // ->onQueue('health-checks')
                            ; // Dedicated queue
                    }
                }
            });
    }

    /**
     * ✅ IMPROVED: Dispatch sync jobs only for online routers
     */
    protected function scheduleRouterSyncJobs(): void
    {
        $hour = now()->hour;

        // Chunk untuk prevent memory overflow
        Router::online()
            ->chunk(20, function ($routers) use ($hour) {
                foreach ($routers as $router) {
                    $off = ((int) $router->id) % 30;

                    // Daily sync at 03:00
                    if ($hour === 3 && now()->minute === $off) {
                        dispatch((new SyncRouterInventoryJob(
                            routerId: $router->id,
                            withProfiles: false,
                            withSecrets:  false,
                            withSessions: false,
                            withPppoe:    true,
                        ))
                        // ->onQueue('mikrotik')
                        )
                        ;
                    }

                    // Hourly sync
                    if (now()->minute === $off) {
                        dispatch((new SyncRouterInventoryJob(
                            routerId: $router->id,
                            withProfiles: true,
                            withSecrets:  true,
                        ))
                        // ->onQueue('mikrotik')
                        )
                        ;
                    }
                }
            });

        // Session sync every 5 minutes
        if (now()->minute % 5 === 0) {
            Router::online()
                ->chunk(50, function ($routers) {
                    foreach ($routers as $router) {
                        dispatch((new SyncActiveSessionsJob($router->id))
                            // ->onQueue('mikrotik')
                            );
                    }
                });
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
