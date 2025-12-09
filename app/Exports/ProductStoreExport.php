<?php

namespace App\Exports;

use App\Models\ProductStore;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductStoreExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Get collection of products based on filters
     * FILTER INI SAMA PERSIS DENGAN DI CONTROLLER INDEX
     */
    public function collection()
    {
        $query = ProductStore::with(['category', 'brand', 'rack.zone.warehouse'])
            ->select([
                'id',
                'name',
                'category_product_store_id',
                'brand_product_store_id',
                'variant',
                'specification',
                'length',
                'width',
                'height',
                'weight',
                'selling_price',
                'rack_id',
                'created_at'
            ]);

        // Apply search filter - SAMA seperti di ProductStore model scope search()
        if (!empty($this->filters['search'])) {
            $query->search($this->filters['search']);
        }

        // Apply category filter - SAMA seperti di render()
        if (!empty($this->filters['category'])) {
            $query->where('category_product_store_id', $this->filters['category']);
        }

        // Apply warehouse filter - SAMA seperti di render()
        if (!empty($this->filters['warehouse'])) {
            $query->whereHas('rack.zone.warehouse', function ($q) {
                $q->where('id', $this->filters['warehouse']);
            });
        }

        // Apply zone filter - SAMA seperti di render()
        if (!empty($this->filters['zone'])) {
            $query->whereHas('rack.zone', function ($q) {
                $q->where('id', $this->filters['zone']);
            });
        }

        // Apply sorting - SAMA seperti di render()
        $sortField = $this->filters['sortField'] ?? 'created_at';
        $sortDirection = $this->filters['sortDirection'] ?? 'desc';
        
        $query->orderBy($sortField, $sortDirection);

        return $query->get();
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'No',
            'Product Name',
            'Category',
            'Brand',
            'Variant',
            'Specification',
            'Dimensions (L x W x H)',
            'Weight (kg)',
            'Selling Price',
            'Warehouse',
            'Zone',
            'Rack',
            'Created At'
        ];
    }

    /**
     * Map data for each row
     */
    public function map($product): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $product->name ?? '-',
            $product->category->name ?? '-',
            $product->brand->name ?? '-',
            $product->variant ?? '-',
            $product->specification ?? '-',
            $this->formatDimensions($product),
            $product->weight ? number_format($product->weight, 2) : '-',
            $product->selling_price ? 'Rp ' . number_format($product->selling_price, 0, ',', '.') : '-',
            $product->rack->zone->warehouse->name ?? '-',
            $product->rack->zone->name ?? '-',
            $product->rack->name ?? '-',
            $product->stock ?? 0,
            $product->created_at ? $product->created_at->format('d/m/Y H:i') : '-'
        ];
    }

    /**
     * Format dimensions string
     */
    protected function formatDimensions($product)
    {
        if ($product->length && $product->width && $product->height) {
            return "{$product->length} x {$product->width} x {$product->height} cm";
        }
        return '-';
    }

    /**
     * Apply styles to worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style header row
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 30,  // Product Name
            'C' => 20,  // Category
            'D' => 20,  // Brand
            'E' => 20,  // Variant
            'F' => 35,  // Specification
            'G' => 20,  // Dimensions
            'H' => 12,  // Weight
            'I' => 18,  // Selling Price
            'J' => 20,  // Warehouse
            'K' => 15,  // Zone
            'L' => 15,  // Rack
            'M' => 10,  // Stock
            'N' => 18,  // Created At
        ];
    }

    /**
     * Sheet title
     */
    public function title(): string
    {
        return 'Product Store';
    }
}