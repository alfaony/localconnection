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
                'template' => 'sk_jabatan_template',
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
                'auto_approve' => true,
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

        // Update head_letter_types_id for all types except 'Surat Keterangan Magang'
        LetterType::where('name', '!=', 'Surat Keterangan Magang')->update([
            'head_letter_types_id' => $perjanjianKerja->id
        ]);
    }
}