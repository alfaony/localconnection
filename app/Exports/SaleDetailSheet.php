<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SaleDetailSheet implements WithTitle, WithEvents
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
        return 'Sales';
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
                        ->orWhere('transaction_number', 'like', "%{$search}%")
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
            })
            ->with(['user', 'items.productStore'])
            ->latest();
    }

    private function formatPaymentMethod(string $method): string
    {
        return match ($method) {
            'cash'         => 'Cash',
            'qris'         => 'QRIS',
            'debit_credit' => 'Debit/Kredit',
            default        => $method,
        };
    }

    private function formatDiskon($item): string
    {
        if ($item->discount_type === 'flat' && $item->discount_amount > 0) {
            return 'Rp ' . number_format((float) $item->discount_amount, 0, ',', '.');
        }
        if ($item->discount_percent > 0) {
            return number_format((float) $item->discount_percent, 0, ',', '.') . '%';
        }
        return '-';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $sales   = $this->buildQuery()->get();
                $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M','N'];
                $lastCol = 'N';
                $row     = 1;
                $no      = 1;

                // ── Header row ────────────────────────────────────────────────
                $headers = [
                    'No','Kode Transaksi', 'Email Pelanggan', 'Total', 'Total Akhir',
                    'Status', 'Metode Bayar', 'Kasir',
                    'Produk', 'Variant', 'Jumlah', 'Harga Satuan', 'Diskon', 'Subtotal',
                    'Total', 'PPN', 'Total Akhir',
                ];
                foreach ($headers as $i => $header) {
                    $sheet->setCellValue($columns[$i] . $row, $header);
                }
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1a3a5c']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(24);
                $row++;

                // ── Data rows ─────────────────────────────────────────────────
                foreach ($sales as $sale) {
                    $transactionRow = $row;
                    $calculatedFinalAmount = (float) $sale->total_amount + (float) $sale->tax_amount;

                    // Transaction row
                    $sheet->setCellValue("A{$row}", $no);
                    $sheet->setCellValue("B{$row}", $sale->transaction_code);
                    $sheet->setCellValue("C{$row}", $sale->customer_email ?? '-');
                    $sheet->setCellValue("D{$row}", (float) $sale->total_amount);
                    $sheet->setCellValue("E{$row}", $calculatedFinalAmount);
                    $sheet->setCellValue("F{$row}", ucfirst($sale->status));
                    $sheet->setCellValue("G{$row}", $this->formatPaymentMethod($sale->payment_method));
                    $sheet->setCellValue("H{$row}", $sale->user->name ?? '-');

                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFdbeafe']],
                    ]);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    $sheet->getStyle("D{$row}:E{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    $row++;

                    // Item rows
                    foreach ($sale->items as $item) {
                        $sheet->setCellValue("I{$row}", $item->productStore->name ?? '-');
                        $sheet->setCellValue("J{$row}", $item->productStore->variant ?? '-');
                        $sheet->setCellValue("K{$row}", (int) $item->quantity);
                        $sheet->setCellValue("L{$row}", (float) $item->unit_price);
                        $sheet->setCellValue("M{$row}", $this->formatDiskon($item));
                        $sheet->setCellValue("N{$row}", (float) $item->subtotal);

                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFf8fafc']],
                        ]);
                        $sheet->getStyle("L{$row}")->getNumberFormat()->setFormatCode('#,##0');
                        $sheet->getStyle("N{$row}")->getNumberFormat()->setFormatCode('#,##0');
                        $row++;
                    }

                    // Border around entire transaction block
                    if ($row > $transactionRow) {
                        $sheet->getStyle("A{$transactionRow}:{$lastCol}" . ($row - 1))->applyFromArray([
                            'borders' => [
                                'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFbfdbfe']],
                            ],
                        ]);
                    }

                    $row++; // empty separator
                    $no++;
                }

                // Auto-size columns
                foreach ($columns as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->freezePane('A2');
            },
        ];
    }
}