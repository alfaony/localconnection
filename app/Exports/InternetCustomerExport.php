<?php

namespace App\Exports;

use App\Models\InternetCustomer;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;

class InternetCustomerExport implements FromQuery, WithMapping, WithHeadings, WithCustomChunkSize
{
    use Exportable;

    protected array $filters;
    protected $company_id;
    protected int $row = 0;

    public function __construct(array $filters, $company_id)
    {
        $this->filters   = $filters;
        $this->company_id = $company_id;
    }

    public function query()
    {
        $query = InternetCustomer::query()
            ->byCompanyJob($this->company_id)
            ->select([
                'id', 'code', 'name', 'ktp_number', 'npwp_number',
                'address', 'customer_type', 'username', 'grouping_id',
                'status', 'created_at', 'internet_package_id',
                'user_customer_id', 'company_id',
            ])
            ->with([
                'userCustomer:id,name,email,phone_number,start_billing_date,end_billing_date',
                'internetPackage:id,name',
                'latestPurchase' => fn ($q) => $q->select([
                    'internet_customer_purchases.id',
                    'internet_customer_purchases.internet_customer_id',
                    'internet_customer_purchases.payment_method',
                    'internet_customer_purchases.transfer_date',
                    'internet_customer_purchases.transfer_from_bank',
                    'internet_customer_purchases.transfer_from_account_name',
                    'internet_customer_purchases.transfer_notes',
                    'internet_customer_purchases.amount_paid',
                    'internet_customer_purchases.payment_months',
                    'internet_customer_purchases.period_start',
                    'internet_customer_purchases.period_end',
                    'internet_customer_purchases.confirmation_finance_at',
                    'internet_customer_purchases.user_finance_id',
                ]),
                'latestPurchase.userFinance:id,name',
            ])
            ->orderBy('created_at', 'desc');

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('userCustomer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                  })
                  ->orWhere('ktp_number', 'like', "%{$search}%")
                  ->orWhere('grouping_id', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['selectedPackage'])) {
            $query->where('internet_package_id', $this->filters['selectedPackage']);
        }

        if (!empty($this->filters['statusFilter'])) {
            $query->where('status', $this->filters['statusFilter']);
        }

        if (!empty($this->filters['customerTypeFilter'])) {
            $query->where('customer_type', $this->filters['customerTypeFilter']);
        }

        if (!empty($this->filters['dateFrom']) || !empty($this->filters['dateTo'])) {
            $from = $this->filters['dateFrom'] ?? '1970-01-01';
            $to   = $this->filters['dateTo']   ?? now()->toDateString();
            $type = $this->filters['dateType'] ?? 'billing';

            if ($type === 'installation') {
                $query->whereHas('installation', function ($q) use ($from, $to) {
                    $q->whereBetween('installed_at', ["{$from} 00:00:00", "{$to} 23:59:59"]);
                });
            } elseif ($type === 'registration') {
                $query->whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"]);
            } elseif ($type === 'suspended') {
                $query->whereHas('userCustomer', function ($q) use ($from, $to) {
                    $q->whereBetween('end_billing_date', [$from, $to]);
                });
            } else {
                $query->whereHas('userCustomer', function ($q) use ($from, $to) {
                    $q->whereBetween('start_billing_date', [$from, $to]);
                });
            }
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode',
            'Status',
            'Nama',
            'No. KTP',
            'No. NPWP',
            'Email',
            'No. HP',
            'Alamat',
            'Tipe Pelanggan',
            'Paket Internet',
            'Username PPPoE',
            'Grouping ID',
            'Tgl. Daftar',
            'Tgl. Mulai Billing',
            'Tgl. Selesai Billing',
            'Pembayaran Terakhir - Metode',
            'Pembayaran Terakhir - Tgl. Transfer',
            'Pembayaran Terakhir - Bank Asal',
            'Pembayaran Terakhir - Nama Rekening',
            'Pembayaran Terakhir - Catatan',
            'Pembayaran Terakhir - Nominal (Rp)',
            'Pembayaran Terakhir - Jumlah Bulan',
            'Pembayaran Terakhir - Periode',
            'Pembayaran Terakhir - Tgl. Dikonfirmasi',
            'Pembayaran Terakhir - Dikonfirmasi Oleh',
        ];
    }

    public function map($customer): array
    {
        $this->row++;
        $lp = $customer->latestPurchase;

        $period = null;
        if ($lp?->period_start && $lp?->period_end) {
            $period = Carbon::parse($lp->period_start)->format('d/m/Y')
                    . ' - '
                    . Carbon::parse($lp->period_end)->format('d/m/Y');
        }

        return [
            $this->row,
            $customer->code,
            $customer->status,
            $customer->name,
            $customer->ktp_number,
            $customer->npwp_number,
            $customer->userCustomer?->email,
            $customer->userCustomer?->phone_number,
            $customer->address,
            ucfirst($customer->customer_type ?? 'rumah'),
            $customer->internetPackage?->name,
            $customer->username,
            $customer->grouping_id,
            $customer->created_at?->format('d/m/Y'),
            $customer->userCustomer?->start_billing_date
                ? Carbon::parse($customer->userCustomer->start_billing_date)->format('d/m/Y')
                : null,
            $customer->userCustomer?->end_billing_date
                ? Carbon::parse($customer->userCustomer->end_billing_date)->format('d/m/Y')
                : null,
            $lp?->payment_method,
            $lp?->transfer_date
                ? Carbon::parse($lp->transfer_date)->format('d/m/Y')
                : null,
            $lp?->transfer_from_bank,
            $lp?->transfer_from_account_name,
            $lp?->transfer_notes,
            $lp?->amount_paid !== null
                ? 'Rp. ' . number_format($lp->amount_paid, 0, ',', '.')
                : null,
            $lp?->payment_months,
            $period,
            $lp?->confirmation_finance_at
                ? Carbon::parse($lp->confirmation_finance_at)->format('d/m/Y H:i')
                : null,
            $lp?->userFinance?->name,
        ];
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
