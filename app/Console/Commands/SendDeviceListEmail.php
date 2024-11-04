<?php

// app/Console/Commands/SendDeviceListEmail.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DeviceService;

use App\Models\Company;
use App\Models\User;
use App\Models\SettingCompany;
use App\Schemas\RoleSchema;
use App\Helpers\EmailNotifHelper;

class SendDeviceListEmail extends Command
{
    protected $signature = 'email:send-device-list';
    protected $description = 'Send the list of devices to a specified email if there are devices available';

    protected $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        parent::__construct();
        $this->deviceService = $deviceService;
    }

    public function handle()
    {
        $companies = Company::all();

        
        
        foreach ($companies as $company) 
        {
            $users = User::where('company_id', $company->id)->whereHas('role',function($query)
            {
                $query->where('name',RoleSchema::BM);
            })->get();
            $toEmails = [];
            $toNames = [];
            # code...
            if($company && count($users)> 0)
            {

                $user = $users->first();
                $deviceList = $this->deviceService->listDeviceOpen($company->id, $user);
                if ($deviceList['success'] && !empty($deviceList['devices'])) 
                {
                    
                    $smtpConfig = SettingCompany::byCompany($user->company_id)->get()->pluck('field_value','field_title');
                    $fromEmail = $smtpConfig['username'] ?? '';
                    $fromName = $smtpConfig['name'] ?? '';
                    
                    foreach ($users as $user) 
                    {
                        $toEmails[] = $user->email;
                        $toNames[] = $user->name;
                        $data = 
                        [
                            'name' => $user->name,
                            'devices' => $deviceList['devices'],
                        ];
        
        
                        EmailNotifHelper::sentEmail(
                            $fromEmail,
                            $fromName,
                            $toEmails, 
                            $toNames, 
                            "Laporan Lampu/Pintu Belum dimatikan/ditutup",
                            "email.notif_device_report",
                            $data, 
                            $smtpConfig, 
                            $company->id, 
                        );

                        $this->info('Email sent to '.$user->name.' in '.$company->name);
                    }
                } else 
                {
                    $this->info('No devices available; email not sent. in'.$company->name);
                }
            }
        }

    }
}
