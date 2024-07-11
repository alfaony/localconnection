<?php

namespace App\Exports;

use App\Models\User;
use App\Models\DailyTaskCategory;
use App\Models\DailyTaskType;
use App\Schemas\RoleSchema;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DailyTaskTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new DailyTaskSheetExport();
        $sheets[] = new CategoriesSheetExport();
        $sheets[] = new TypeSheetExport();
        $sheets[] = new UsersSheetExport();

        return $sheets;
    }
}

class DailyTaskSheetExport implements FromArray, WithTitle, WithColumnFormatting
{
    public function array(): array
    {
        return [
            ['TANGGAL MULAI (D-M-Y)', 'TANGGAL BERAKHIR (D-M-Y)', 'KATEGORI TUGAS HARIAN', 'TIPE', 'User Email Ditugaskan', 'Tanggal Submit', 'Nama Tugas', 'Deskripsi Tugas', 'Laporan Tugas']
        ];
    }

    public function title(): string
    {
        return 'Daily Tasks';
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DMYSLASH,
            'B' => NumberFormat::FORMAT_DATE_DMYSLASH,
            'F' => NumberFormat::FORMAT_DATE_DMYSLASH,
        ];
    }
}

class UsersSheetExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return User::select('name', 'email')
            ->byCompany(Auth::user()->company_id)
            ->where(function($query) {
                $query->whereHas('role', function ($query) {
                    $query->whereNotIn('name', [
                        RoleSchema::ROOT, 
                        RoleSchema::OB, 
                        RoleSchema::BM, 
                        RoleSchema::SECURITY
                    ]);
                });
            })
            ->orderBy('name','asc')
            ->get();
    }

    public function headings(): array
    {
        return ['Name', 'Email'];
    }

    public function title(): string
    {
        return 'Users';
    }
}

class CategoriesSheetExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return DailyTaskCategory::select('name')->byCompany(Auth::user()->company_id)->get();
    }

    public function headings(): array
    {
        return ['KATEGORI'];
    }

    public function title(): string
    {
        return 'Kategori Tugas Harian';
    }
}

class TypeSheetExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return DailyTaskType::select('name')->get();
    }

    public function headings(): array
    {
        return ['TIPE'];
    }

    public function title(): string
    {
        return 'Tipe Tugas Harian';
    }
}