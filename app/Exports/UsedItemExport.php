<?php

namespace App\Exports;

use App\Models\UsedItem;
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

class UsedItemExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithDrawings
{
    protected $filters;
    protected $qrCodes = [];
    protected $rowNumber = 2;
    protected $companyIds; 

    public function __construct($filters, $companyIds)
    {
        $this->filters = $filters;
        $this->companyIds = $companyIds;
    }

    public function collection()
    {
        $companyIds = $this->companyIds;
        $query = UsedItem::with(['rack.zone.warehouse', 'repairs', 'user', 'categories'])
                ->whereHas('user', function ($q) use ($companyIds) {
                    $q->whereIn('company_id', $companyIds);
                });

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (isset($this->filters['is_sold'])) {
            if ($this->filters['is_sold'] === 'sold') {
                $query->where('is_sold', 1);
            } elseif ($this->filters['is_sold'] === 'available') {
                $query->where('is_sold', 0);
            }
        }

        if (!empty($this->filters['category_id'])) {
            $query->whereHas('categories', function ($q) {
                $q->where('item_categories.id', $this->filters['category_id']);
            });
        }

        if (!empty($this->filters['rack_id'])) {
            $query->where('rack_id', $this->filters['rack_id']);
        }

        if (!empty($this->filters['warehouse_id'])) {
            $query->whereHas('rack.zone.warehouse', function ($q) {
                $q->where('id', $this->filters['warehouse_id']);
            });
        }

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
            'Serial Number',
            'Nama Item',
            'Kategori',
            'Lokasi Rak',
            'Warehouse',
            'Zone',
            'Harga Beli',
            'Total Biaya Perbaikan',
            'Harga Jual Disarankan',
            'Status Jual',
            'Harga Jual',
            'Tanggal Terjual',
            'Catatan',
            'Dibuat Oleh',
            'Tanggal Dibuat',
        ];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;

        $url = route('used-item.show-qr', $item->slug);
        $qrCodePath = $this->generateQrCode($item->id, $url);
        
        $this->qrCodes[] = [
            'path' => $qrCodePath,
            'row' => $this->rowNumber
        ];
        $this->rowNumber++;

        $repairCost = $item->repairs->sum('cost');
        $categories = $item->categories->pluck('name')->join(', ');

        return [
            $no,
            '',
            $item->serial_number ?? '-',
            $item->name ?? '-',
            $categories ?: '-',
            $item->rack ? $item->rack->name : '-',
            $item->rack && $item->rack->zone && $item->rack->zone->warehouse 
                ? $item->rack->zone->warehouse->name : '-',
            $item->rack && $item->rack->zone ? $item->rack->zone->name : '-',
            $item->purchase_price ? 'Rp ' . number_format($item->purchase_price, 0, ',', '.') : '-',
            'Rp ' . number_format($repairCost, 0, ',', '.'),
            'Rp ' . number_format($item->suggested_selling_price, 0, ',', '.'),
            $item->sale_status,
            $item->sold_price ? 'Rp ' . number_format($item->sold_price, 0, ',', '.') : '-',
            $item->sold_at ? $item->sold_at->format('d-m-Y') : '-',
            $item->notes ?? '-',
            $item->user ? $item->user->name : '-',
            $item->created_at ? $item->created_at->format('d-m-Y H:i:s') : '-',
        ];
    }

    public function drawings()
    {
        $drawings = [];

        foreach ($this->qrCodes as $qrCode) {
            $drawing = new Drawing();
            $drawing->setName('QR Code');
            $drawing->setDescription('QR Code');
            $drawing->setPath(storage_path('app/' . $qrCode['path']));
            $drawing->setHeight(80);
            $drawing->setCoordinates('B' . $qrCode['row']);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);

            $drawings[] = $drawing;
        }

        return $drawings;
    }

    public function styles(Worksheet $sheet)
    {
        foreach ($this->qrCodes as $qrCode) {
            $sheet->getRowDimension($qrCode['row'])->setRowHeight(60);
        }

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
        $localPath = 'temp/qrcodes/item_' . $laptopId . '_' . time() . '.png';
        
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