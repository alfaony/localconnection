<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\SaleItem;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SaleSummarySheet implements WithTitle, WithEvents
{
    protected $filters;
    protected $companyIds;

    public function __construct($filters, $companyIds)
    {
        $this->filters    = $filters;
        $this->companyIds = $companyIds;
    }

    public function title(): string
    {
        return 'Summary';
    }

    private function buildQuery()
    {
        $companyIds = $this->companyIds;

        return Sale::whereHas('user', function ($q) use ($companyIds) {
                $q->whereIn('company_id', $companyIds);
            })
            ->where('status', 'completed')
            ->when(!empty($this->filters['search']), function ($q) {
                $search = $this->filters['search'];
                $q->where(function ($inner) use ($search) {
                    $inner->where('transaction_code', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('payment_method', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(!empty($this->filters['payment_method']), function ($q) {
                $q->where('payment_method', $this->filters['payment_method']);
            })
            ->when(!empty($this->filters['start_date']), function ($q) {
                $q->whereDate('created_at', '>=', $this->filters['start_date']);
            })
            ->when(!empty($this->filters['end_date']), function ($q) {
                $q->whereDate('created_at', '<=', $this->filters['end_date']);
            })
            ->when(!empty($this->filters['start_time']), function ($q) {
                $q->whereTime('created_at', '>=', $this->filters['start_time']);
            })
            ->when(!empty($this->filters['end_time']), function ($q) {
                $q->whereTime('created_at', '<=', $this->filters['end_time']);
            })
            ->when(!empty($this->filters['user_id']), function ($q) {
                $q->where('user_id', $this->filters['user_id']);
            });
    }

    private function sectionHeader($sheet, int $row, string $label, string $argb = 'FF2563eb', int $spanCols = 6, string $startCol = 'A'): void
    {
        $endCol = chr(ord($startCol) + $spanCols - 1);
        $sheet->setCellValue("{$startCol}{$row}", $label);
        $sheet->mergeCells("{$startCol}{$row}:{$endCol}{$row}");
        $sheet->getStyle("{$startCol}{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $argb]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(22);
    }

    private function subHeader($sheet, int $row, array $labels, string $startCol = 'A'): void
    {
        foreach ($labels as $i => $label) {
            $col = chr(ord($startCol) + $i);
            $sheet->setCellValue("{$col}{$row}", $label);
        }
        $startOrd = ord($startCol);
        $endCol   = chr($startOrd + count($labels) - 1);
        $sheet->getStyle("{$startCol}{$row}:{$endCol}{$row}")->applyFromArray([
            'font'    => ['bold' => true],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFdbeafe']],
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF93c5fd']],
            ],
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $query = $this->buildQuery();

                // Aggregate data
                $totalSubtotal        = (clone $query)->sum('total_amount');
                $totalPpn             = (clone $query)->sum('tax_amount');
                $totalDeduction       = (clone $query)->sum('cash_deduction');
                $totalBeforeDeduction = $totalSubtotal + $totalPpn;
                $totalFinalAmount     = (clone $query)->sum('final_amount');
                $totalTransaksi       = (clone $query)->count();

                $paymentBreakdown = (clone $query)
                    ->selectRaw('payment_method, SUM(final_amount) as total, COUNT(*) as jumlah')
                    ->groupBy('payment_method')
                    ->get()
                    ->keyBy('payment_method');

                $saleIds      = (clone $query)->pluck('id');
                $productSales = SaleItem::whereIn('sale_items.sale_id', $saleIds)
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->with('productStore')
                    ->selectRaw('
                        sale_items.product_store_id,
                        SUM(sale_items.quantity) as total_qty,
                        SUM(sale_items.original_price * sale_items.quantity) as total_original,
                        SUM(sale_items.discount_amount) as total_discount,
                        SUM(
                            CASE WHEN sales.total_amount > 0
                            THEN sale_items.subtotal / sales.total_amount * COALESCE(sales.tax_amount, 0)
                            ELSE 0 END
                        ) as total_ppn,
                        SUM(sale_items.subtotal) as total_revenue
                    ')
                    ->groupBy('sale_items.product_store_id')
                    ->orderByDesc('total_qty')
                    ->get();

                $row = 1;

                // ══════════════════════════════════════════════════════════════
                // Section 1 – Judul
                // ══════════════════════════════════════════════════════════════
                $this->sectionHeader($sheet, $row, 'RINGKASAN PENJUALAN', 'FF1a3a5c', 6, 'B');
                $row++;

                $startDate = $this->filters['start_date'] ?? '';
                $endDate   = $this->filters['end_date']   ?? '';
                if ($startDate || $endDate) {
                    $sheet->setCellValue("B{$row}", 'Periode');
                    $sheet->setCellValue("C{$row}", ($startDate ?: '-') . ' s/d ' . ($endDate ?: '-'));
                    $sheet->getStyle("B{$row}")->getFont()->setBold(true);
                    $row++;
                }

                if (!empty($this->filters['start_time']) || !empty($this->filters['end_time'])) {
                    $sheet->setCellValue("B{$row}", 'Rentang Waktu');
                    $sheet->setCellValue("C{$row}", ($this->filters['start_time'] ?? '-') . ' - ' . ($this->filters['end_time'] ?? '-'));
                    $sheet->getStyle("B{$row}")->getFont()->setBold(true);
                    $row++;
                }

                $pmLabels = ['cash' => 'Cash', 'qris' => 'QRIS', 'debit_credit' => 'Debit Card/Credit Card'];
                if (!empty($this->filters['payment_method'])) {
                    $sheet->setCellValue("B{$row}", 'Filter Metode');
                    $sheet->setCellValue("C{$row}", $pmLabels[$this->filters['payment_method']] ?? $this->filters['payment_method']);
                    $sheet->getStyle("B{$row}")->getFont()->setBold(true);
                    $row++;
                }
                $row++; // blank

                // ══════════════════════════════════════════════════════════════
                // Section 2 – Total Penjualan
                // ══════════════════════════════════════════════════════════════
                $this->sectionHeader($sheet, $row, 'TOTAL PENJUALAN', 'FF2563eb', 6, 'B');
                $row++;

                foreach ([
                    ['Total Transaksi', $totalTransaksi, null],
                    ['Subtotal (Rp)', (float) $totalSubtotal, '#,##0'],
                    ['Total PPN (Rp)', (float) $totalPpn, '#,##0'],
                    ['Total Sebelum Deduction (Rp)', (float) $totalBeforeDeduction, '#,##0'],
                    ['Potongan Pembulatan (Rp)', (float) $totalDeduction, '#,##0'],
                    ['Total Akhir Setelah Deduction (Rp)', (float) $totalFinalAmount, '#,##0'],
                ] as [$label, $value, $fmt]) {
                    $sheet->setCellValue("B{$row}", $label);
                    $sheet->setCellValue("C{$row}", $value);
                    $sheet->getStyle("B{$row}")->getFont()->setBold(true);
                    if ($fmt) {
                        $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode($fmt);
                    }
                    $row++;
                }
                $row++; // blank

                // ══════════════════════════════════════════════════════════════
                // Section 3 – Per Metode Pembayaran
                // ══════════════════════════════════════════════════════════════
                $this->sectionHeader($sheet, $row, 'PER METODE PEMBAYARAN', 'FF2563eb', 6, 'B');
                $row++;
                $this->subHeader($sheet, $row, ['Metode Pembayaran', 'Jumlah Transaksi', 'Total (Rp)'], 'B');
                $row++;

                foreach ($pmLabels as $key => $label) {
                    $data = $paymentBreakdown->get($key);
                    $sheet->setCellValue("B{$row}", $label);
                    $sheet->setCellValue("C{$row}", $data ? (int) $data->jumlah : 0);
                    $sheet->setCellValue("D{$row}", $data ? (float) $data->total : 0);
                    $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    $row++;
                }

                // Baris total
                $sheet->setCellValue("B{$row}", 'TOTAL');
                $sheet->setCellValue("C{$row}", (int) $totalTransaksi);
                $sheet->setCellValue("D{$row}", (float) $totalFinalAmount);
                $sheet->getStyle("B{$row}:D{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFe0f2fe']],
                ]);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $row++;
                $row++; // blank

                // ══════════════════════════════════════════════════════════════
                // Section 4 – Produk Terjual
                // ══════════════════════════════════════════════════════════════
                $this->sectionHeader($sheet, $row, 'PRODUK TERJUAL', 'FF2563eb', 10);
                $row++;
                $this->subHeader($sheet, $row, ['No', 'Produk', 'Variant', 'Barcode/Kode', 'Total Terjual', 'Original Price', 'Discount', 'Subtotal', 'PPN', 'Total']);
                $row++;

                $no = 1;
                foreach ($productSales as $ps) {
                    $subtotal = (float) $ps->total_revenue;
                    $ppn      = (float) $ps->total_ppn;

                    $sheet->setCellValue("A{$row}", $no++);
                    $sheet->setCellValue("B{$row}", $ps->productStore->name ?? '-');
                    $sheet->setCellValue("C{$row}", $ps->productStore->variant ?? '-');
                    $sheet->getCell("D{$row}")->setValueExplicit(
                        (string) ($ps->productStore->barcode ?? '-'),
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                    );
                    $sheet->setCellValue("E{$row}", (int) $ps->total_qty);
                    $sheet->setCellValue("F{$row}", (float) $ps->total_original);
                    $sheet->setCellValue("G{$row}", (float) $ps->total_discount);
                    $sheet->setCellValue("H{$row}", $subtotal);
                    $sheet->setCellValue("I{$row}", $ppn);
                    $sheet->setCellValue("J{$row}", $subtotal + $ppn);
                    $sheet->getStyle("F{$row}:J{$row}")->getNumberFormat()->setFormatCode('#,##0');

                    // Zebra striping
                    if ($no % 2 === 0) {
                        $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFf0f9ff']],
                        ]);
                    }
                    $row++;
                }

                // Auto-size columns
                foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'] as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}