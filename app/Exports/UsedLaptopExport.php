<?php

namespace App\Exports;

use App\Models\UsedLaptop;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class UsedLaptopExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithDrawings
{
    protected $filters;
    protected $qrCodes = [];
    protected $companyIds;
    protected $rowNumber = 2; // Start from row 2 (after header)

    public function __construct($filters, $companyIds)
    {
        $this->filters = $filters;
        $this->companyIds = $companyIds;
    }

    public function collection()
    {
        $companyIds = $this->companyIds;
        $query = UsedLaptop::with(['rack.zone.warehouse', 'repairs', 'user'])
            ->whereHas('user', function ($q) use ($companyIds) {
                $q->whereIn('company_id', $this->companyIds);
            });

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Apply sold status filter
        if (isset($this->filters['is_sold'])) {
            if ($this->filters['is_sold'] === 'sold') {
                $query->where('is_sold', 1);
            } elseif ($this->filters['is_sold'] === 'available') {
                $query->where('is_sold', 0);
            } elseif ($this->filters['is_sold'] === 'inventory') {
                $query->whereNull('is_sold');
            }
        }

        // Apply rack filter
        if (!empty($this->filters['rack_id'])) {
            $query->where('rack_id', $this->filters['rack_id']);
        }

        // Apply zone filter
        if (!empty($this->filters['zone_id'])) {
            $query->whereHas('rack.zone', function ($q) {
                $q->where('id', $this->filters['zone_id']);
            });
        }

        // Apply warehouse filter
        if (!empty($this->filters['warehouse_id'])) {
            $query->whereHas('rack.zone.warehouse', function ($q) {
                $q->where('id', $this->filters['warehouse_id']);
            });
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
            'QR Code',
            "Url",
            'Serial Number',
            'Nama Laptop',
            'Brand',
            'Processor',
            'RAM',
            'SSD',
            'GPU',
            'Sistem Operasi',
            'Berat (kg)',
            'Lokasi Rak',
            'Warehouse',
            'Zone',
            'Harga Beli',
            'Total Biaya Perbaikan',
            'Harga Jual Disarankan',
            'Harga Jakarta',
            'Harga Jambi',
            'Status Jual',
            'Harga Jual',
            'Tanggal Terjual',
            'Catatan',
            'Dibuat Oleh',
            'Tanggal Dibuat',
        ];
    }

    public function map($laptop): array
    {
        static $no = 0;
        $no++;

        // Generate QR Code
        $url = route('used-laptop.show-qr', $laptop->slug);
        $qrCodePath = $this->generateQrCode($laptop->id, $url);
        
        // Store QR code info for drawing
        $this->qrCodes[] = [
            'path' => $qrCodePath,
            'row' => $this->rowNumber
        ];
        $this->rowNumber++;

        $repairCost = $laptop->repairs->sum('cost');

        return [
            $no,
            '', // QR Code column (will be filled by drawings)
            $url,
            $laptop->serial_number ?? '-',
            $laptop->name ?? '-',
            $laptop->brand ?? '-',
            $laptop->processor ?? '-',
            $laptop->ram ?? '-',
            $laptop->ssd ?? '-',
            $laptop->gpu ?? '-',
            $laptop->operating_system ?? '-',
            $laptop->weight ?? '-',
            $laptop->rack ? $laptop->rack->name : '-',
            $laptop->rack && $laptop->rack->zone && $laptop->rack->zone->warehouse 
                ? $laptop->rack->zone->warehouse->name : '-',
            $laptop->rack && $laptop->rack->zone ? $laptop->rack->zone->name : '-',
            $laptop->purchase_price ? 'Rp ' . number_format($laptop->purchase_price, 0, ',', '.') : '-',
            'Rp ' . number_format($repairCost, 0, ',', '.'),
            'Rp ' . number_format($laptop->suggested_selling_price, 0, ',', '.'),
            'Rp ' . number_format($laptop->jakarta_price, 0, ',', '.'),
            'Rp ' . number_format($laptop->jambi_price, 0, ',', '.'),
            $laptop->sale_status,
            $laptop->sold_price ? 'Rp ' . number_format($laptop->sold_price, 0, ',', '.') : '-',
            $laptop->sold_at ? $laptop->sold_at->format('d-m-Y') : '-',
            $laptop->notes ?? '-',
            $laptop->user ? $laptop->user->name : '-',
            $laptop->created_at ? $laptop->created_at->format('d-m-Y H:i:s') : '-',
        ];
    }

    public function drawings()
    {
        $drawings = [];

        foreach ($this->qrCodes as $qrCode) {
            // Download from S3 to local temp if needed, or use the local temp path
            $localPath = $qrCode['path'];
            
            // Check if file exists in local storage
            if (!Storage::disk('local')->exists($localPath)) {
                continue;
            }

            $drawing = new Drawing();
            $drawing->setName('QR Code');
            $drawing->setDescription('QR Code');
            $drawing->setPath(storage_path('app/' . $localPath));
            $drawing->setHeight(80);
            $drawing->setCoordinates('B' . $qrCode['row']); // Column B
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);

            $drawings[] = $drawing;
        }

        return $drawings;
    }

    public function styles(Worksheet $sheet)
    {
        // Set row height for QR codes
        foreach ($this->qrCodes as $qrCode) {
            $sheet->getRowDimension($qrCode['row'])->setRowHeight(60);
        }

        // Set column width for QR code column
        $sheet->getColumnDimension('B')->setWidth(15);

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

    private function generateQrCode($laptopId, $url)
    {
        // Generate QR code ke local storage dulu (untuk drawing)
        $localPath = 'temp/qrcodes/laptop_' . $laptopId . '_' . time() . '.png';
        
        $qrCode = QrCode::format('png')
            ->size(200)
            ->generate($url);

        // Save to local storage temporarily (for PhpSpreadsheet Drawing)
        Storage::disk('local')->put($localPath, $qrCode);

        return $localPath;
    }

    public function __destruct()
    {
        // Clean up temporary QR codes from local storage
        foreach ($this->qrCodes as $qrCode) {
            if (Storage::disk('local')->exists($qrCode['path'])) {
                Storage::disk('local')->delete($qrCode['path']);
            }
        }
    }
}