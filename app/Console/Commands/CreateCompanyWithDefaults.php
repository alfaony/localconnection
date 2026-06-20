<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Company;
use App\Models\User;
use App\Models\Role;
use App\Models\SettingCompany;
use App\Schemas\RoleSchema;
use Ramsey\Uuid\Uuid;

class CreateCompanyWithDefaults extends Command
{
    protected $signature = 'create:company
                            {namaCompany : Nama perusahaan}
                            {email : Email untuk akun Root}
                            {--password= : Password akun Root (default: root123!)}
                            {--province= : Nama provinsi untuk setting company}
                            {--city= : Nama kota untuk setting company}
                            {--district= : Nama kecamatan untuk setting company}
                            {--subdistrict= : Nama kelurahan untuk setting company}
                            ';

    protected $description = 'Buat Company, User Root, dan SettingCompany default sekaligus.';

    public function handle()
    {
        $namaCompany = $this->argument('namaCompany');
        $email       = $this->argument('email');
        $password    = $this->option('password') ?? 'root123!';
        $province    = $this->option('province');
        $city        = $this->option('city');
        $district    = $this->option('district');
        $subdistrict = $this->option('subdistrict');

        // 1. Validasi template roles ada (company_id = null)
        $templateRoles = Role::whereNull('company_id')->with('permissions')->get();
        if ($templateRoles->isEmpty()) {
            $this->error('Tidak ada template role (company_id = null). Jalankan seeder terlebih dahulu.');
            return Command::FAILURE;
        }

        $rootRole = $templateRoles->firstWhere('name', RoleSchema::ROOT);
        if (!$rootRole) {
            $this->error('Role "Root" tidak ditemukan di template. Jalankan seeder terlebih dahulu.');
            return Command::FAILURE;
        }

        // 2. Cek company duplikat
        if (Company::where('name', $namaCompany)->withTrashed()->exists()) {
            $this->error("Company dengan nama \"{$namaCompany}\" sudah ada.");
            return Command::FAILURE;
        }

        // 3. Cek email duplikat
        if (User::where('email', $email)->withTrashed()->exists()) {
            $this->error("Email \"{$email}\" sudah digunakan.");
            return Command::FAILURE;
        }

        // 4. Buat Company
        $company = new Company();
        $company->name = $namaCompany;
        $company->save();

        $this->info("✓ Company dibuat: {$company->name} (slug: {$company->slug})");

        // 5. Copy semua template roles ke company baru
        $copiedRoles = collect(); // map: template role id => new role id
        foreach ($templateRoles as $template) {
            $newRole = new Role();
            $newRole->name       = $template->name;
            $newRole->desc       = $template->desc;
            $newRole->guard_name = $template->guard_name;
            $newRole->company_id = $company->id;
            $newRole->save(); // slug di-generate otomatis di setNameAttribute

            // Copy permissions
            $permissionIds = $template->permissions->pluck('id');
            if ($permissionIds->isNotEmpty()) {
                $insertData = $permissionIds->map(fn ($pid) => [
                    'id'            => Uuid::uuid4()->toString(),
                    'role_id'       => $newRole->id,
                    'permission_id' => $pid,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ])->all();

                foreach (array_chunk($insertData, 500) as $chunk) {
                    DB::table('permission_role')->insert($chunk);
                }
            }

            $copiedRoles[$template->id] = $newRole;
        }

        $companyRootRole = $copiedRoles[$rootRole->id];
        $this->info("✓ Role di-copy: {$templateRoles->count()} role ke company baru.");

        // 6. Buat User Root dengan role milik company baru
        $user = new User();
        $user->name       = 'Root ' . $namaCompany;
        $user->email      = $email;
        $user->password   = Hash::make($password);
        $user->role_id    = $companyRootRole->id;
        $user->company_id = $company->id;
        $user->delete_able = 0;
        $user->save();

        $this->info("✓ User Root dibuat: {$user->email} (slug: {$user->slug})");

        // 7. Buat SettingCompany default (profile)
        $profileFields = [
            'name'          => $namaCompany,
            'director'      => '',
            'address'       => '',
            'npwp_number'   => '',
            'currency'      => 'Rp',
            'currency_usd'  => '',
            'nib_file'      => '',
            'acta_file'     => '',
            'npwp_file'     => '',
            'signature_file'=> '',
        ];

        foreach ($profileFields as $key => $value) {
            $setting = new SettingCompany();
            $setting->user_id     = $user->id;
            $setting->field_title = $key;
            $setting->field_value = $value;
            $setting->menu        = 'profile';
            $setting->save();
        }

        $this->info('✓ SettingCompany (profile) dibuat: ' . count($profileFields) . ' field.');

        // 8. Buat SettingCompany default (internet_customer_setting)
        $internetFields = [
            'internet_icon'                    => '',
            'internet_company_name'            => $namaCompany,
            'internet_company_address'         => '',
            'internet_phone'                   => '',
            'internet_footer_message'          => '',
            'internet_message_blast'           => '',
            'manual_payment_status'            => '',
            'internet_remainder_billing'       => '',
            'internet_remainder_billing_3'     => '',
            'internet_remainder_billing_1'     => '',
            'internet_remainder_billing_0'     => '',
            'internet_remainder_billing_isolir'=> '',
            'internet_message_success'         => '',
        ];

        foreach ($internetFields as $key => $value) {
            $setting = new SettingCompany();
            $setting->user_id     = $user->id;
            $setting->field_title = $key;
            $setting->field_value = $value;
            $setting->menu        = 'internet_customer_setting';
            $setting->save();
        }


        $this->info('✓ SettingCompany (internet_customer_setting) dibuat: ' . count($internetFields) . ' field.');

        $midtransFields = 
        [
            'server_key_midtrans' => '', 
            'client_key_midtrans' => '', 
            'environment_midtrans' => 'sandbox',
            'midtrans_pay_with_ppn' => '0'
        ];

        foreach ($midtransFields as $key => $value) {
            $setting = new SettingCompany();
            $setting->user_id     = $user->id;
            $setting->field_title = $key;
            $setting->field_value = $value;
            $setting->menu        = 'midtrans_internet_customer';
            $setting->save();
        }

        $this->info('✓ SettingCompany (midtrans_internet_customer) dibuat: ' . count($midtransFields) . ' field.');

        $wablasFields = ['server_wablas' => null,'token_wablas' => null, 'webhook_key_wablas' => null];

        foreach ($wablasFields as $key => $value) {
            $setting = new SettingCompany();
            $setting->user_id     = $user->id;
            $setting->field_title = $key;
            $setting->field_value = $value;
            $setting->menu        = 'wablas_internet_customer';
            $setting->save();
        }

        $this->info('✓ SettingCompany (wablas_internet_customer) dibuat: ' . count($wablasFields) . ' field.');

        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['Company Name',  $company->name],
                ['Company Slug',  $company->slug],
                ['User Email',    $user->email],
                ['User Slug',     $user->slug],
                ['Role',          RoleSchema::ROOT],
                ['Password',      $password],
                ['Profile Fields', count($profileFields)],
                ['Internet Fields', count($internetFields)],
            ]
        );

        $this->call('import:wilayah-csv', [
                '--province' => $province,
                '--city' => $city,
                '--district' => $district,
                '--subdistrict' => $subdistrict
        ]);
        return Command::SUCCESS;
    }
}
