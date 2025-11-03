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
        // $workingHours = $this->getWorkingHours($user);
        
        // Get entry date with safe code
        $entryDate = $this->getEntryDate($user);

        return [
            $rowNumber,
            $kye->full_name ?? $user->name ?? '-',
            $kye->call_name ?? '-',
            $divisionPosition,
            $gender,
            $kye->ktp_number ?? $user->id_card ??'-',
            $birthInfo,
            $kye->npwp_number ?? $user->npwp_number ?? '-',
            $kye->address ?? $user->address ?? '-',
            $kye->address_domisili ?? $kye->address ?? '-',
            $kye->email ?? $user->email_gmail ?? '-',
            $user->email ?? '-',
            $kye->phone_number ?? '-',
            $kye->imei_number ?? '-',
            $kye->account_number ?? '-',
            $kye->bank_name ?? '-',
            $kye->bank_account_name ?? '-',
            $maritalStatus,
            $kye->number_of_children ?? '0',
            $kye->emergency_contact ?? '-',
            $kye->emergency_phone ?? '-',
            $entryDate,
            $workingDays,
            $kye ? ucfirst($kye->approval_status ?? 'pending') : '-',
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
        if ($user->last_position_now && $user->last_position_now->position) 
        {
            $positions[] = $user->last_position_now->position->name ?? '';
        }

        $divisionText = !empty($divisions) ? implode(', ', $divisions) : '-';
        $positionText = !empty($positions) ? implode(', ', $positions) : '-';

        return "{$divisionText} / {$positionText}";
    }

    /**
     * Get Gender safely
     */
    private function getGender($kye): string
    {
        if (!$kye || !isset($kye->gender)) {
            return '-';
        }

        return isset($kye->gender) && $kye->gender === 'male' ? 'Laki-laki' : 'Perempuan';
    }

    /**
     * Get Birth Info safely
     */
    private function getBirthInfo($kye): string
    {
        if (!$kye) {
            return '-';
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
            return '-';
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
            return '-';
        }

        $statuses = [
            'single' => 'Belum Menikah',
            'married' => 'Menikah',
            'divorced' => 'Cerai',
            'widow' => 'Janda/Duda',
        ];

        return $statuses[$kye->marital_status] ?? ucfirst($kye->marital_status);
    }

    /**
     * Get Working Days safely
     */
    private function getWorkingDays($user): string
    {
        // Check if last_position_now exists
        if (!isset($user->last_position_now) || is_null($user->last_position_now)) {
            return '-';
        }
        
        // Check if user has letterSubmission with working_hours
        if (isset($user->last_position_now->letterSubmission)) {
            $letterSubmission = $user->last_position_now->letterSubmission;
            
            // Decode field JSON
            if (isset($letterSubmission->field) && !empty($letterSubmission->field)) {
                $fieldData = json_decode($letterSubmission->field, true);
                
                // Check if working_hours exists and not empty
                if (isset($fieldData['working_hours']) && !empty($fieldData['working_hours'])) {
                    return $fieldData['working_hours'];
                }
            }
        }
        
        // Default
        return '-';
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
        // Check if last_position_now exists
        if (!isset($user->first_position) || is_null($user->first_position)) {
            return '-';
        }
        
        // Check if user has letterSubmission with working_hours
        if (isset($user->first_position->letterSubmission)) {
            $letterSubmission = $user->first_position->letterSubmission;
            
            // Decode field JSON
            if (isset($letterSubmission->field) && !empty($letterSubmission->field)) {
                $fieldData = json_decode($letterSubmission->field, true);
                
                // Check if working_hours exists and not empty
                if (isset($fieldData['start_date']) && !empty($fieldData['start_date'])) {
                    return $fieldData['start_date'];
                }
            }
        }
        
        // Default
        return '-';
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