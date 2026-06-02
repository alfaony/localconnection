<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SettingCompany;
use App\Models\User;

class FieldSettingCompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $field = ['name' => '','director'=>'','address' => '','npwp_number' => '','currency'=>'Rp','currency_usd'=>"",'nib_file'=>'','acta_file'=> '','npwp_file' => '','signature_file' => ''];
        $user = User::first();

        foreach ($field as $key => $value) 
        {
            $fieldExists = SettingCompany::where('field_title',$key)->first();
            if(!$fieldExists)
            {
                $field = new SettingCompany();
                $field->user_id = $user->id;
                $field->field_title = $key;
                $field->field_value = $value;
                $field->save();
            }else
            {
                $fieldExists->field_title = $key;
                $fieldExists->field_value = $value;
                $fieldExists->save();
            }
            
        }
        
    }
//     Nama Perusahaan = PT. Gema

// 2. Alamat = Jl.S.Parman

// 3. Direktur = Eddy

// 4. Mata Uang Dasar = Rp

// 5. Nilai Tukar 1 USD = 15000

// 6. Upload File NIB
// 7. Upload File Akta
// 8. Upload File NPWP

// 9. No.NPWP = 0707979797
}
