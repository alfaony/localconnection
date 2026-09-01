<?php

namespace App\Console\Commands;

use App\Jobs\SendBillingStatusBlastJob;
use App\Models\SettingCompany;
use App\Models\UserCustomer;
use App\Models\WablasLog;
use App\Schemas\ParamSchema;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BlastBillingByStatusCommand extends Command
{
    public const SOURCE = 'billing_status_blast';

    public const EVENTS = [
        'h_0' => 'internet_remainder_billing_0',
        'h_1' => 'internet_remainder_billing_1',
        'h_3' => 'internet_remainder_billing_3',
        'billing_created' => 'internet_remainder_billing',
    ];

    protected $signature = 'billing:blast-by-status
                            {status : Status internet customer}
                            {date : Tanggal efektif dalam format Y-m-d}
                            {--company_id= : Batasi ke satu company}
                            {--dry-run : Tampilkan calon pengiriman tanpa dispatch job}
                            {--force : Kirim ulang walaupun sudah pending atau sukses}';

    protected $description = 'Kirim satu WA billing per customer berdasarkan status dan kejadian pada tanggal efektif';

    public function handle(): int
    {
        $status = (string) $this->argument('status');
        $date = $this->parseDate((string) $this->argument('date'));

        if (!$date) {
            $this->error('Tanggal tidak valid. Gunakan format Y-m-d, contoh: 2026-09-01.');
            return self::FAILURE;
        }

        if (!in_array($status, $this->allowedStatuses(), true)) {
            $this->error("Status '{$status}' tidak dikenal sebagai status internet customer.");
            return self::FAILURE;
        }

        $companyId = $this->option('company_id');
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $query = UserCustomer::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->whereHas('internetCustomer', fn ($query) => $query->where('status', $status))
            ->where(function ($query) use ($date) {
                $query->whereDate('start_billing_date', $date->toDateString())
                    ->orWhereDate('end_billing_date', $date->toDateString())
                    ->orWhereDate('end_billing_date', $date->copy()->addDay()->toDateString())
                    ->orWhereDate('end_billing_date', $date->copy()->addDays(3)->toDateString());
            })
            ->with(['internetCustomer.internetPackage'])
            ->orderBy('company_id')
            ->orderBy('name');

        $customers = $query->get();
        $settingCache = [];
        $counts = [
            'matched' => $customers->count(),
            'queued' => 0,
            'dry_run' => 0,
            'paid' => 0,
            'empty_template' => 0,
            'already_sent' => 0,
            'failed_dispatch' => 0,
        ];
        $rows = [];
        $delaySeconds = 0;

        foreach ($customers as $customer) {
            $internetCustomer = $customer->internetCustomer;
            $event = self::resolveEvent($customer, $date);

            if (!$internetCustomer || !$event) {
                continue;
            }

            if ($internetCustomer->is_paid) {
                $counts['paid']++;
                $rows[] = $this->row($customer, $event, 'SKIP: sudah bayar');
                continue;
            }

            $templateKey = self::EVENTS[$event];
            $settings = $settingCache[$customer->company_id]
                ??= SettingCompany::byCompany($customer->company_id)
                    ->get()
                    ->pluck('field_value', 'field_title');
            $template = trim(html_to_wa($settings[$templateKey] ?? ''));

            if ($template === '') {
                $counts['empty_template']++;
                $rows[] = $this->row($customer, $event, "SKIP: {$templateKey} kosong");
                continue;
            }

            $alreadySent = WablasLog::query()
                ->where('source', self::SOURCE)
                ->where('source_id', $internetCustomer->id)
                ->whereDate('effective_date', $date)
                ->whereIn('status', ['pending', 'success'])
                ->exists();

            if ($alreadySent && !$force) {
                $counts['already_sent']++;
                $rows[] = $this->row($customer, $event, 'SKIP: sudah pending/terkirim');
                continue;
            }

            if ($dryRun) {
                $counts['dry_run']++;
                $rows[] = $this->row($customer, $event, 'DRY-RUN');
                continue;
            }

            $log = WablasLog::create([
                'company_id' => $customer->company_id,
                'source' => self::SOURCE,
                'source_id' => $internetCustomer->id,
                'phone' => $customer->phone_number,
                'message' => "[pending] {$event}",
                'type' => 'text',
                'status' => 'pending',
                'event_key' => $event,
                'effective_date' => $date->toDateString(),
                'template_key' => $templateKey,
            ]);

            try {
                $delaySeconds += 2;
                SendBillingStatusBlastJob::dispatch(
                    $log->id,
                    $customer->id,
                    $status,
                    $event,
                    $date->toDateString()
                )->delay(now()->addSeconds($delaySeconds));

                $counts['queued']++;
                $rows[] = $this->row($customer, $event, 'QUEUED');
            } catch (\Throwable $throwable) {
                $log->update([
                    'status' => 'failed',
                    'response' => ['error' => $throwable->getMessage()],
                ]);
                $counts['failed_dispatch']++;
                $rows[] = $this->row($customer, $event, 'FAILED: dispatch');
            }
        }

        $this->info('Billing blast berdasarkan status');
        $this->line("Status: {$status}");
        $this->line('Tanggal efektif: '.$date->toDateString());
        $this->line('Mode: '.($dryRun ? 'DRY-RUN' : 'DISPATCH'));

        if ($rows) {
            $this->table(['Kode', 'Nama', 'Company', 'Event', 'Hasil'], $rows);
        }

        $this->newLine();
        $this->line("Kandidat: {$counts['matched']}");
        $this->line("Queued: {$counts['queued']}");
        $this->line("Dry-run: {$counts['dry_run']}");
        $this->line("Skip sudah bayar: {$counts['paid']}");
        $this->line("Skip template kosong: {$counts['empty_template']}");
        $this->line("Skip sudah pending/terkirim: {$counts['already_sent']}");
        $this->line("Gagal dispatch: {$counts['failed_dispatch']}");

        return $counts['failed_dispatch'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    public static function resolveEvent(UserCustomer $customer, Carbon $date): ?string
    {
        $effectiveDate = $date->toDateString();
        $endDate = $customer->end_billing_date
            ? Carbon::parse($customer->end_billing_date)->toDateString()
            : null;

        if ($endDate === $effectiveDate) {
            return 'h_0';
        }

        if ($endDate === $date->copy()->addDay()->toDateString()) {
            return 'h_1';
        }

        if ($endDate === $date->copy()->addDays(3)->toDateString()) {
            return 'h_3';
        }

        $startDate = $customer->start_billing_date
            ? Carbon::parse($customer->start_billing_date)->toDateString()
            : null;

        return $startDate === $effectiveDate ? 'billing_created' : null;
    }

    private function parseDate(string $value): ?Carbon
    {
        try {
            $date = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            return $date->format('Y-m-d') === $value ? $date : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function allowedStatuses(): array
    {
        return [
            ParamSchema::PENDING,
            ParamSchema::WAITING_PAYMENT_SUBSCRIPTION,
            ParamSchema::WAITING_PAYMENT_CONFIRMATION,
            ParamSchema::PROCESS_INSTALLATION,
            ParamSchema::INSTALLED,
            ParamSchema::ACTIVE,
            ParamSchema::EXPIRED,
            ParamSchema::CANCELLED,
            ParamSchema::CLOSED,
            ParamSchema::SUSPENDED,
            ParamSchema::REACTIVATED,
            ParamSchema::DISCONNECTED,
            ParamSchema::CUSTOMER_EXISTING,
            ParamSchema::INACTIVE,
            ParamSchema::CHALLENGE,
            ParamSchema::DIRECT_POINT,
        ];
    }

    private function row(UserCustomer $customer, string $event, string $result): array
    {
        return [
            $customer->internetCustomer->code ?? '-',
            $customer->name,
            $customer->company_id,
            $event,
            $result,
        ];
    }
}
