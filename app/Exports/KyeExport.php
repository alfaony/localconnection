<?php

namespace App\Exports;

use App\Models\Kye;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KyeExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;
    protected $companyIds;

    public function __construct($filters, $companyIds)
    {
        $this->filters = $filters;
        $this->companyIds = $companyIds;
    }

    public function collection()
    {
        $companyIds = $this->companyIds;
        $query = Kye::with('user')->whereHas('user', function ($query) use ($companyIds) {
            $query->whereIn('company_id', $companyIds);
        });

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('ktp_number', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if (!empty($this->filters['status'])) {
            $query->where('approval_status', $this->filters['status']);
        }

        // Apply date range filter
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'Nama Panggilan',
            "Posisi",
            "Divisi",
            "Tanggal Masuk",
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat KTP',
            'Alamat Domisili',
            'Status Pernikahan',
            'Jumlah Anak',
            'No. KTP',
            'Foto KTP',
            'Foto Selfie KTP',
            'Foto KK',
            'No. NPWP',
            'Foto NPWP',
            'Google Maps',
            'Foto Rumah',
            'SKCK',
            'No. Telepon',
            'Email',
            'No. IMEI',
            'Kontak Darurat',
            'No. Darurat',
            'Nama Bank',
            'Nama Pemilik Rekening',
            'No. Rekening',
            'Foto Karyawan',
            'Status Persetujuan',
            'Catatan Persetujuan',
            'Tanggal Dibuat',
        ];
    }

    public function map($kye): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $kye->full_name ?? '-',
            $kye->call_name ?? '-',
            $kye->gender ?? '-',
            isset($kye->user->first_position) && isset($kye->user->first_position->position) && isset($kye->user->first_position->position->name) ? $kye->user->first_position->position->name : '-',
            isset($kye->user->first_position) ? $kye->user->first_position->start_date : '-',
            $kye->birth_place ?? '-',
            $kye->birth_date ? $kye->birth_date->format('d-m-Y') : '-',
            $kye->address ?? '-',
            $kye->address_domisili ?? '-',
            $kye->marital_status ?? '-',
            $kye->number_of_children ?? '-',
            $kye->ktp_number ?? '-',
            $kye->ktp_photo ? 'Tersedia' : 'Tidak Tersedia',
            $kye->selfie_ktp ? 'Tersedia' : 'Tidak Tersedia',
            $kye->ktp_family ? 'Tersedia' : 'Tidak Tersedia',
            $kye->npwp_number ?? '-',
            $kye->npwp_photo ? 'Tersedia' : 'Tidak Tersedia',
            $kye->google_maps ?? '-',
            $kye->house_photo ? 'Tersedia' : 'Tidak Tersedia',
            $kye->skck ? 'Tersedia' : 'Tidak Tersedia',
            $kye->phone_number ?? '-',
            $kye->email ?? '-',
            $kye->imei_number ?? '-',
            $kye->emergency_contact ?? '-',
            $kye->emergency_phone ?? '-',
            $kye->bank_name ?? '-',
            $kye->bank_account_name ?? '-',
            $kye->account_number ?? '-',
            $kye->employee_photo ? 'Tersedia' : 'Tidak Tersedia',
            $this->getStatusLabel($kye->approval_status),
            $kye->approval_note ?? '-',
            $kye->created_at ? $kye->created_at->format('d-m-Y H:i:s') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ]
            ],
        ];
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];

        return $labels[$status] ?? $status;
    }
}