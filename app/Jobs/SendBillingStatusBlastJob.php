<?php

namespace App\Jobs;

use App\Console\Commands\BlastBillingByStatusCommand;
use App\Models\SettingCompany;
use App\Models\UserCustomer;
use App\Models\WablasLog;
use App\Services\Weblas\Message;
use App\Services\Weblas\WablasClient;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBillingStatusBlastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $logId,
        private string $customerId,
        private string $expectedStatus,
        private string $event,
        private string $effectiveDate,
    ) {
    }

    public function handle(): void
    {
        $log = WablasLog::find($this->logId);

        if (!$log || $log->status === 'success') {
            return;
        }

        try {
            $customer = UserCustomer::with([
                'internetCustomer.internetPackage',
                'internetCustomer.purchases',
            ])->findOrFail($this->customerId);
            $internetCustomer = $customer->internetCustomer;

            if (!$internetCustomer) {
                $this->failLog($log, 'Internet customer tidak ditemukan.');
                return;
            }

            if ($internetCustomer->status !== $this->expectedStatus) {
                $this->failLog($log, "Status berubah menjadi {$internetCustomer->status}.");
                return;
            }

            if ($internetCustomer->is_paid) {
                $this->failLog($log, 'Customer sudah membayar.');
                return;
            }

            $effectiveDate = Carbon::createFromFormat('Y-m-d', $this->effectiveDate)->startOfDay();
            if (BlastBillingByStatusCommand::resolveEvent($customer, $effectiveDate) !== $this->event) {
                $this->failLog($log, 'Tanggal billing customer berubah sebelum pesan dikirim.');
                return;
            }

            $settings = SettingCompany::byCompany($internetCustomer->company_id)
                ->get()
                ->pluck('field_value', 'field_title');
            $templateKey = BlastBillingByStatusCommand::EVENTS[$this->event] ?? null;
            $template = trim(html_to_wa($settings[$templateKey] ?? ''));

            if (!$templateKey || $template === '') {
                $this->failLog($log, "Template {$templateKey} kosong.");
                return;
            }

            if (empty($settings['server_wablas']) || empty($settings['token_wablas'])) {
                $this->failLog($log, 'Konfigurasi Wablas belum lengkap.');
                return;
            }

            $client = new WablasClient(
                $settings['server_wablas'],
                $settings['token_wablas'],
                $settings['webhook_key_wablas'] ?? null
            );

            if (!$client->status()) {
                $this->failLog($log, 'Wablas client tidak aktif.');
                return;
            }

            $message = $this->buildMessage($template, $customer);
            $response = (new Message($client))->single_text($customer->phone_number, $message);
            $success = !empty($response['status']) && $response['status'] === true;

            $log->update([
                'phone' => $customer->phone_number,
                'message' => $message,
                'status' => $success ? 'success' : 'failed',
                'response' => $response ?? [],
                'sent_at' => $success ? now() : null,
            ]);

            Log::info('Billing status blast selesai diproses.', [
                'wablas_log_id' => $log->id,
                'customer_code' => $internetCustomer->code,
                'event' => $this->event,
                'effective_date' => $this->effectiveDate,
                'status' => $success ? 'success' : 'failed',
            ]);
        } catch (\Throwable $throwable) {
            $this->failLog($log, $throwable->getMessage());
            Log::error('Billing status blast gagal.', [
                'wablas_log_id' => $this->logId,
                'customer_id' => $this->customerId,
                'error' => $throwable->getMessage(),
            ]);
        }
    }

    private function buildMessage(string $template, UserCustomer $customer): string
    {
        $internetCustomer = $customer->internetCustomer;
        $package = $internetCustomer->internetPackage;
        $priceData = $package->getPriceForRegion(
            $internetCustomer->province_id,
            $internetCustomer->city_id,
            $internetCustomer->district_id,
            $internetCustomer->subdistrict_id
        );
        $latestPurchase = $internetCustomer->purchases()->latest()->first();
        $billingAmount = $latestPurchase?->amount_paid ?? ($priceData['price_nett'] ?? 0);
        $dueDate = $customer->end_billing_date
            ? Carbon::parse($customer->end_billing_date)->locale('id')->translatedFormat('d F Y')
            : '-';

        return strtr($template, [
            '{{nama}}' => $customer->name,
            '{{kode}}' => $internetCustomer->code,
            '{{faktur}}' => $latestPurchase?->code ?? '-',
            '{{paket}}' => $package->name,
            '{{jatuh_tempo}}' => $dueDate,
            '{{tagihan}}' => 'Rp. '.number_format($billingAmount, 2, ',', '.'),
            '{{url}}' => route('internet-customer.customer.show', $internetCustomer->code),
            '{{tutorial}}' => config('services.internet_custom.tutorial_payment', ''),
        ]);
    }

    private function failLog(WablasLog $log, string $reason): void
    {
        $log->update([
            'status' => 'failed',
            'response' => ['error' => $reason],
            'sent_at' => null,
        ]);
    }

    public function tags(): array
    {
        return [
            'billing-status-blast',
            'customer:'.$this->customerId,
            'event:'.$this->event,
            'date:'.$this->effectiveDate,
        ];
    }
}
