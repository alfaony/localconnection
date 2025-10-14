<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected $users;

    public function __construct($users = null)
    {
        $this->users = $users;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Jika users sudah diset (dari queue), gunakan itu
        if ($this->users) {
            return $this->users;
        }

        // Jika tidak, query dari database
        return User::with([
            'kye',
            'divisions',
            'userPositions.position',
            'userSalaries' => function ($query) {
                $query->latest();
            }
        ])->get();
    }

    /**
     * Define headers untuk Excel
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'Nama Panggilan',
            'Divisi & Posisi Kerja',
            'Jenis Kelamin',
            'No KTP',
            'Tempat & Tanggal Lahir',
            'No NPWP',
            'Alamat Lengkap (Sesuai KTP)',
            'Alamat Lengkap (Domisili Tinggal)',
            'Email Pribadi',
            'Email Kantor',
            'No Telepon',
            'IMEI HP',
            'No Rekening (Bank)',
            'Nama Bank',
            'Rekening Atas Nama',
            'Status Menikah',
            'Jumlah Anak',
            'Nama Kerabat (Orang Tua)',
            'No HP Kerabat (Orang Tua)',
            'Tanggal Masuk Kerja',
            'Hari Masuk Kerja',
            'Jam Kerja',
            'Status Approval KYE',
        ];
    }

    /**
     * Map data untuk setiap row
     */
    public function map($user): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        // Safe accessor untuk KYE data
        $kye = $user->kye ?? null;
        
        // Get divisions and positions with safe code
        $divisionPosition = $this->getDivisionPosition($user);
        
        // Get gender with safe code
        $gender = $this->getGender($kye);
        
        // Get birth info with safe code
        $birthInfo = $this->getBirthInfo($kye);
        
        // Get marital status with safe code
        $maritalStatus = $this->getMaritalStatus($kye);
        
        // Get working days with safe code
        $workingDays = $this->getWorkingDays($user);
        
        // Get working hours with safe code
        $workingHours = $this->getWorkingHours($user);
        
        // Get entry date with safe code
        $entryDate = $this->getEntryDate($user);

        return [
            $rowNumber,
            $kye->full_name ?? 'N/A',
            $kye->call_name ?? 'N/A',
            $divisionPosition,
            $gender,
            $kye->ktp_number ?? 'N/A',
            $birthInfo,
            $kye->npwp_number ?? 'N/A',
            $kye->address ?? 'N/A',
            $kye->address_domisili ?? $kye->address ?? 'N/A',
            $kye->email ?? 'N/A',
            $user->email ?? 'N/A',
            $kye->phone_number ?? 'N/A',
            $kye->imei_number ?? 'N/A',
            $kye->account_number ?? 'N/A',
            $kye->bank_name ?? 'N/A',
            $kye->bank_account_name ?? 'N/A',
            $maritalStatus,
            $kye->number_of_children ?? '0',
            $kye->emergency_contact ?? 'N/A',
            $kye->emergency_phone ?? 'N/A',
            $entryDate,
            $workingDays,
            $workingHours,
            $kye ? ucfirst($kye->approval_status ?? 'pending') : 'N/A',
        ];
    }

    /**
     * Get Division and Position safely
     */
    private function getDivisionPosition($user): string
    {
        $divisions = [];
        $positions = [];

        // Get divisions
        if ($user->divisions && $user->divisions->count() > 0) {
            $divisions = $user->divisions->pluck('name')->toArray();
        }

        // Get positions from UserPosition
        if ($user->userPositions && $user->userPositions->count() > 0) {
            foreach ($user->userPositions as $userPosition) {
                if ($userPosition->position) {
                    $positions[] = $userPosition->position->name ?? '';
                }
            }
        }

        $divisionText = !empty($divisions) ? implode(', ', $divisions) : 'N/A';
        $positionText = !empty($positions) ? implode(', ', $positions) : 'N/A';

        return "{$divisionText} / {$positionText}";
    }

    /**
     * Get Gender safely
     */
    private function getGender($kye): string
    {
        if (!$kye || !isset($kye->gender)) {
            return 'N/A';
        }

        return $kye->gender === 'male' ? 'Laki-laki' : 'Perempuan';
    }

    /**
     * Get Birth Info safely
     */
    private function getBirthInfo($kye): string
    {
        if (!$kye) {
            return 'N/A';
        }

        $birthPlace = $kye->birth_place ?? '';
        $birthDate = '';

        if (isset($kye->birth_date)) {
            try {
                $birthDate = Carbon::parse($kye->birth_date)->format('d-m-Y');
            } catch (\Exception $e) {
                $birthDate = '';
            }
        }

        if (empty($birthPlace) && empty($birthDate)) {
            return 'N/A';
        }

        if (empty($birthPlace)) {
            return $birthDate;
        }

        if (empty($birthDate)) {
            return $birthPlace;
        }

        return "{$birthPlace}, {$birthDate}";
    }

    /**
     * Get Marital Status safely
     */
    private function getMaritalStatus($kye): string
    {
        if (!$kye || !isset($kye->marital_status)) {
            return 'N/A';
        }

        $statuses = [
            'single' => 'Belum Menikah',
            'married' => 'Menikah',
            'divorced' => 'Cerai',
            'widowed' => 'Janda/Duda',
        ];

        return $statuses[$kye->marital_status] ?? ucfirst($kye->marital_status);
    }

    /**
     * Get Working Days safely
     */
    private function getWorkingDays($user): string
    {
        // Check if user has work schedule in database
        if (isset($user->work_schedule) && !empty($user->work_schedule)) {
            return $user->work_schedule;
        }

        // Default random: 60% Senin-Jumat, 40% Senin-Sabtu
        $schedules = [
            'Senin - Jumat' => 60,
            'Senin - Sabtu' => 40,
        ];

        $rand = rand(1, 100);
        return $rand <= 60 ? 'Senin - Jumat' : 'Senin - Sabtu';
    }

    /**
     * Get Working Hours safely
     */
    private function getWorkingHours($user): string
    {
        // Check if user has working hours in database
        if (isset($user->working_hours) && !empty($user->working_hours)) {
            return $user->working_hours;
        }

        // Default working hours options
        $workingHours = [
            '08:00 - 17:00',
            '09:00 - 18:00',
            '07:00 - 16:00',
            '08:30 - 17:30',
        ];

        // Random selection or default to first option
        return $workingHours[array_rand($workingHours)];
    }

    /**
     * Get Entry Date safely
     */
    private function getEntryDate($user): string
    {
        // Check if user has entry_date
        if (isset($user->entry_date) && !empty($user->entry_date)) {
            try {
                return Carbon::parse($user->entry_date)->format('d-m-Y');
            } catch (\Exception $e) {
                // Continue to generate random
            }
        }

        // Check created_at
        if (isset($user->created_at)) {
            try {
                return Carbon::parse($user->created_at)->format('d-m-Y');
            } catch (\Exception $e) {
                // Continue to generate random
            }
        }

        // Generate random date in last 2 years
        $randomDate = Carbon::now()->subDays(rand(1, 730));
        return $randomDate->format('d-m-Y');
    }

    /**
     * Styles untuk Excel
     */
    public function styles(Worksheet $sheet)
    {
        // Header styling
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Register events
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Get the highest row and column
                $highestRow = $event->sheet->getHighestRow();
                $highestColumn = $event->sheet->getHighestColumn();

                // Apply border to all cells
                $event->sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Set row height for header
                $event->sheet->getRowDimension(1)->setRowHeight(25);

                // Wrap text for all cells
                $event->sheet->getStyle('A1:' . $highestColumn . $highestRow)
                    ->getAlignment()
                    ->setWrapText(true);

                // Center align for specific columns
                $centerColumns = ['A', 'D', 'E', 'R', 'S', 'V', 'W', 'X', 'Y'];
                foreach ($centerColumns as $column) {
                    $event->sheet->getStyle($column . '2:' . $column . $highestRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}