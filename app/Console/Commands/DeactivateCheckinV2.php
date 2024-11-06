<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmployeeChecking;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;
use Carbon\Carbon;

class DeactivateCheckinV2 extends Command
{
    protected $signature = 'checkin:deactivate {--id= : The ID of the employee checking}';
    protected $description = 'Deactivate check-ins and remove them from Firebase after the check-in timeout period';

    protected $firebase;

    public function __construct()
    {
        parent::__construct();

        // Initialize Firebase
        $firebase = (new Factory)
        ->withServiceAccount(storage_path(config('services.firebase.service_account')))
        ->withDatabaseUri(config('services.firebase.service_database_checkin_url'));

        $this->firebase = $firebase->createDatabase();
    }

    public function handle()
    {
        // Fetch all active check-ins that are scheduled for today and are not completed
        $id = $this->option('id');
        $checkin = EmployeeChecking::find($id);    

        if($checkin)
        {
            // Get the current time and schedule timeout
            $timeoutTime = $checkin->scheduled_timeout ? Carbon::parse($checkin->scheduled_timeout)->format('H:i') : NULL;
            $currentTime = Carbon::now()->tz('Asia/Jakarta')->format('H:i');
    
            // If the current time is greater than the timeout time, deactivate the check-in
            if (isset($checkin->scheduled_timeout) &&  isset($timeoutTime) && $currentTime == $timeoutTime) {
                // Update `is_active` to false in the local database
                $checkin->is_active = false;
                $checkin->save();
    
                // Remove the record from Firebase
                $this->removeFromFirebase($checkin);
    
                $this->info("Check-in deactivated and removed from Firebase for user: {$checkin->user_id}");
            }
    
            $this->info('Check-in deactivations and removals processed successfully.');
        }
    }

    private function removeFromFirebase($checkin)
    {
        // Try to remove from Firebase if available
        if ($this->firebase) {
            try {
                $this->firebase->getReference('employee_checkins/' . $checkin->user_id . '/' . $checkin->id)->remove();
            } catch (FirebaseException $e) {
                $this->error("Failed to remove from Firebase for user {$checkin->user_id}: " . $e->getMessage());
            }
        }
    }
}
