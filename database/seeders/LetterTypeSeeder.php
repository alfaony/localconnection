<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LetterType;
use Illuminate\Support\Str;

class LetterTypeSeeder extends Seeder
{
    public function run()
    {
        // Create or update letter types
        $perjanjianKerja = LetterType::updateOrCreate(
            ['name' => 'Surat Perjanjian Kerja'], // Kondisi pencarian
            [
                'template' => 'perjanjian_kerja_template',
                'is_required' => true,
                'is_duplicate' => true,
                'auto_approve' => false,
                'is_ending' => false,
            ]
        );

        $skJabatan = LetterType::updateOrCreate(
            ['name' => 'Surat Keputusan Jabatan'], 
            [
                'name' => "Surat Keputusan Manajemen",
                'template' => 'sk_management_template',
                'is_required' => false,
                'is_duplicate' => true,
                'auto_approve' => false,
                'is_ending' => false,
            ]
        );

        $skManagemen = LetterType::updateOrCreate(
            ['name' => 'Surat Keputusan Manajemen'], 
            [
                'name' => "Surat Keputusan Manajemen",
                'template' => 'sk_management_template',
                'is_required' => false,
                'is_duplicate' => true,
                'auto_approve' => false,
                'is_ending' => false,
            ]
        );

        $skJabatan = LetterType::updateOrCreate(
            ['name' => 'Surat Kuasa'], 
            [
                'template' => 'sk_kuasa_template',
                'is_required' => false,
                'is_duplicate' => true,
                'auto_approve' => false,
                'is_ending' => false,
            ]
        );

        $skPengantarKerja = LetterType::updateOrCreate(
            ['name' => 'Surat Pengantar Kerja'], 
            [
                'template' => 'sk_pengantar_kerja_template',
                'is_required' => false,
                'is_duplicate' => true,
                'auto_approve' => true,
                'is_ending' => false,
            ]
        );

        $skResign = LetterType::updateOrCreate(
            ['name' => 'Surat Keterangan Resign'], 
            [
                'template' => 'sk_bekerja_resign_template',
                'is_required' => false,
                'is_duplicate' => true,
                'auto_approve' => true,
                'is_ending' => true,
            ]
        );

        $skTugas = LetterType::updateOrCreate(
            ['name' => 'Surat Penugasan'], 
            [
                'template' => 'sk_tugas_template',
                'is_required' => false,
                'is_duplicate' => true,
                'auto_approve' => false,
                'is_ending' => false,
            ]
        );

        $skMagang = LetterType::updateOrCreate(
            ['name' => 'Surat Keterangan Magang'], 
            [
                'template' => 'sk_magang_template',
                'is_required' => true,
                'is_duplicate' => false,
                'auto_approve' => false,
                'is_ending' => false,
            ]
        );

        // Revisi

        // Update head_letter_types_id for all types except 'Surat Keterangan Magang'
        LetterType::where('head_letter_types_id','!=',null)->update([
            'head_letter_types_id' => NULL
        ]);
        
        LetterType::where('name', '!=', 'Surat Keterangan Magang')->where('name','!=','Surat Perjanjian Kerja')->update([
            'head_letter_types_id' => $perjanjianKerja->id
        ]);
    }
}