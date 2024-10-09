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
        $company = Company::where('name', 'BOS 3')->firstOrFail();
        $user = User::where('company_id', $company->id)->whereHas('role',function($query)
        {
            $query->where('name',RoleSchema::BM);
        })->first();
        $toEmails = [];
        $toNames = [];


        if($company && $user)
        {
            $deviceList = $this->deviceService->listDeviceOpen($company->id, $user);
            if ($deviceList['success'] && !empty($deviceList['devices'])) {
                
                $smtpConfig = SettingCompany::byCompany($user->company_id)->get()->pluck('field_value','field_title');
                $fromEmail = $smtpConfig['username'] ?? '';
                $fromName = $smtpConfig['name'] ?? '';

                $toEmails[] = $user->email;
                $toNames[] = $user->name;
                $data = 
                [
                    'name' => $user->name,
                    'devices' => $deviceList['devices'],
                ];


                return EmailNotifHelper::sentEmail(
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
            } else {
                $this->info('No devices available; email not sent.');
            }
        }

    }
}
