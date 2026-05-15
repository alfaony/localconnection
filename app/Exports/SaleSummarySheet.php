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

    private function sectionHeader($sheet, int $row, string $label, string $argb = 'FF2563eb', int $spanCols = 6): void
    {
        $endCol = chr(ord('A') + $spanCols - 1);
        $sheet->setCellValue("A{$row}", $label);
        $sheet->mergeCells("A{$row}:{$endCol}{$row}");
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $argb]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(22);
    }

    private function subHeader($sheet, int $row, array $labels): void
    {
        foreach ($labels as $i => $label) {
            $col = chr(ord('A') + $i);
            $sheet->setCellValue("{$col}{$row}", $label);
        }
        $endCol = chr(ord('A') + count($labels) - 1);
        $sheet->getStyle("A{$row}:{$endCol}{$row}")->applyFromArray([
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
                $totalFinalAmount = (clone $query)->sum('final_amount');
                $totalTransaksi   = (clone $query)->count();
                $paymentBreakdown = (clone $query)
                    ->selectRaw('payment_method, SUM(final_amount) as total, COUNT(*) as jumlah')
                    ->groupBy('payment_method')
                    ->get()
                    ->keyBy('payment_method');

                $saleIds      = (clone $query)->pluck('id');
                $productSales = SaleItem::whereIn('sale_id', $saleIds)
                    ->with('productStore')
                    ->selectRaw('product_store_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
                    ->groupBy('product_store_id')
                    ->orderByDesc('total_qty')
                    ->get();

                $row = 1;

                // ══════════════════════════════════════════════════════════════
                // Section 1 – Judul
                // ══════════════════════════════════════════════════════════════
                $this->sectionHeader($sheet, $row, 'RINGKASAN PENJUALAN', 'FF1a3a5c');
                $row++;

                $startDate = $this->filters['start_date'] ?? '';
                $endDate   = $this->filters['end_date']   ?? '';
                if ($startDate || $endDate) {
                    $sheet->setCellValue("A{$row}", 'Periode');
                    $sheet->setCellValue("B{$row}", ($startDate ?: '-') . ' s/d ' . ($endDate ?: '-'));
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                    $row++;
                }

                if (!empty($this->filters['start_time']) || !empty($this->filters['end_time'])) {
                    $sheet->setCellValue("A{$row}", 'Rentang Waktu');
                    $sheet->setCellValue("B{$row}", ($this->filters['start_time'] ?? '-') . ' - ' . ($this->filters['end_time'] ?? '-'));
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                    $row++;
                }

                $pmLabels = ['cash' => 'Cash', 'qris' => 'QRIS', 'debit_credit' => 'Debit/Kredit'];
                if (!empty($this->filters['payment_method'])) {
                    $sheet->setCellValue("A{$row}", 'Filter Metode');
                    $sheet->setCellValue("B{$row}", $pmLabels[$this->filters['payment_method']] ?? $this->filters['payment_method']);
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                    $row++;
                }
                $row++; // blank

                // ══════════════════════════════════════════════════════════════
                // Section 2 – Total Penjualan
                // ══════════════════════════════════════════════════════════════
                $this->sectionHeader($sheet, $row, 'TOTAL PENJUALAN');
                $row++;

                foreach ([
                    ['Total Transaksi', $totalTransaksi, null],
                    ['Total Akhir (Rp)', (float) $totalFinalAmount, '#,##0'],
                ] as [$label, $value, $fmt]) {
                    $sheet->setCellValue("A{$row}", $label);
                    $sheet->setCellValue("B{$row}", $value);
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                    if ($fmt) {
                        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($fmt);
                    }
                    $row++;
                }
                $row++; // blank

                // ══════════════════════════════════════════════════════════════
                // Section 3 – Per Metode Pembayaran
                // ══════════════════════════════════════════════════════════════
                $this->sectionHeader($sheet, $row, 'PER METODE PEMBAYARAN');
                $row++;
                $this->subHeader($sheet, $row, ['Metode Pembayaran', 'Jumlah Transaksi', 'Total (Rp)']);
                $row++;

                foreach ($pmLabels as $key => $label) {
                    $data = $paymentBreakdown->get($key);
                    $sheet->setCellValue("A{$row}", $label);
                    $sheet->setCellValue("B{$row}", $data ? (int) $data->jumlah : 0);
                    $sheet->setCellValue("C{$row}", $data ? (float) $data->total : 0);
                    $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    $row++;
                }

                // Baris total
                $sheet->setCellValue("A{$row}", 'TOTAL');
                $sheet->setCellValue("B{$row}", (int) $totalTransaksi);
                $sheet->setCellValue("C{$row}", (float) $totalFinalAmount);
                $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFe0f2fe']],
                ]);
                $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $row++;
                $row++; // blank

                // ══════════════════════════════════════════════════════════════
                // Section 4 – Produk Terjual
                // ══════════════════════════════════════════════════════════════
                $this->sectionHeader($sheet, $row, 'PRODUK TERJUAL');
                $row++;
                $this->subHeader($sheet, $row, ['No', 'Produk', 'Variant', 'Barcode/Kode', 'Total Terjual', 'Total Revenue (Rp)']);
                $row++;

                $no = 1;
                foreach ($productSales as $ps) {
                    $sheet->setCellValue("A{$row}", $no++);
                    $sheet->setCellValue("B{$row}", $ps->productStore->name ?? '-');
                    $sheet->setCellValue("C{$row}", $ps->productStore->variant ?? '-');
                    $sheet->setCellValue("D{$row}", $ps->productStore->barcode ?? '-');
                    $sheet->setCellValue("E{$row}", (int) $ps->total_qty);
                    $sheet->setCellValue("F{$row}", (float) $ps->total_revenue);
                    $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('#,##0');

                    // Zebra striping
                    if ($no % 2 === 0) {
                        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFf0f9ff']],
                        ]);
                    }
                    $row++;
                }

                // Auto-size columns
                foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
