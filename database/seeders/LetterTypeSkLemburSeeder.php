<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\LetterType;

class LetterTypeSkLemburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $skPerjanjianKerja = LetterType::where('name', 'Surat Perjanjian Kerja')->where('template','perjanjian_kerja_template')->first();

        if($skPerjanjianKerja)
        {
            $skLembur = LetterType::updateOrCreate(
                ['name' => 'Surat Keputusan Lembur'], 
                [
                    'head_letter_types_id' => $skPerjanjianKerja->id,
                    'template' => 'sk_lembur_template',
                    'is_required' => false,
                    'is_duplicate' => true,
                    'auto_approve' => false,
                    'is_ending' => false,
                ]
            );

            return "done";
        }else
        {
            return 'Not Found Surat Perjanjian Kerja';
        }
    }
}
