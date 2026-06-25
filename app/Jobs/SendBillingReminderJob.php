<?php

namespace App\Jobs;

use App\Models\UserCustomer;
use App\Models\InternetCustomerPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Schemas\ParamSchema;
use App\Models\SettingCompany;
use App\Services\Weblas\Message;
use App\Services\Weblas\WablasClient;

class SendBillingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $customer;
    protected $daysBeforeDue;

    /**
     * Create a new job instance.
     *
     * @param UserCustomer $customer
     */
    public function __construct(UserCustomer $customer, $daysBeforeDue)
    {
        $this->customer = $customer;
        $this->daysBeforeDue = $daysBeforeDue;
    }

    /**
     * Execute the job.
     * Mengirim peringatan WhatsApp 1 hari sebelum jatuh tempo pembayaran
     */
    public function handle()
    {
        try {
            $internetCustomer = $this->customer->internetCustomer;

            // Skip jika customer tidak ada atau sudah tidak aktif
            if (!$internetCustomer) {
                Log::warning("Internet customer not found for UserCustomer ID: {$this->customer->id}");
                return;
            }

            // Skip jika customer sudah bayar (is_paid = true)
            if ($internetCustomer->is_paid) {
                Log::info("Customer {$internetCustomer->code} sudah bayar, skip reminder");
                return;
            }

            // Skip jika status bukan WAITING_PAYMENT
            if (!in_array($internetCustomer->status, [
                ParamSchema::WAITING_PAYMENT_SUBSCRIPTION,
            ])) {
                Log::info("Customer {$internetCustomer->code} status tidak waiting payment, skip reminder");
                return;
            }

            // Get company settings untuk Wablas
            $settingCompany = SettingCompany::byCompany($internetCustomer->company_id)
                // ->where('menu', 'wablas')
                ->get()
                ->pluck('field_value', 'field_title');

            if ($settingCompany['server_wablas'] && $settingCompany['token_wablas']) {
                $this->sentWaReminder($settingCompany, $this->customer, $this->daysBeforeDue);
                Log::info("Billing reminder sent to customer: {$internetCustomer->code}");
            } else {
                Log::warning("Wablas settings not configured for company: {$internetCustomer->company_id}");
            }

        } catch (\Throwable $th) {
            Log::error("Failed to send billing reminder: " . $th->getMessage(), [
                'customer_id' => $this->customer->id ?? null,
                'trace' => $th->getTraceAsString()
            ]);
        }
    }

    /**
     * Send WhatsApp reminder message
     */
    private function sentWaReminder($settingCompany, $customer, $daysBeforeDue)
    {
        try {
            $client = new WablasClient(
                $settingCompany['server_wablas'],
                $settingCompany['token_wablas'],
                $settingCompany['webhook_key_wablas']
            );

            if ($client->status()) {
                $url = route('internet-customer.customer.show', $customer->internetCustomer->code);
                $dateJatuhTempo = Carbon::parse($customer->end_billing_date)->format('d')
                    . ' ' . Carbon::parse($customer->end_billing_date)->locale('id')->monthName
                    . ' ' . Carbon::parse($customer->end_billing_date)->year;
                $tutorialPayment = config('services.internet_custom.tutorial_payment');

                // Ambil pesan template dari setting
                $internetSetting = SettingCompany::byCompany($customer->internetCustomer->company_id)
                    // ->where('menu', 'wablas')
                    ->get()
                    ->pluck('field_value', 'field_title');

                $settingKey = match((int)$daysBeforeDue) {
                    0 => 'internet_remainder_billing_0',
                    1 => 'internet_remainder_billing_1',
                    3 => 'internet_remainder_billing_3',
                    default => 'internet_remainder_billing_isolir',
                };
                $template = html_to_wa($internetSetting[$settingKey] ?? '');
                
                // Jika pesan kosong di setting, tidak perlu kirim
                if (empty($template)) {
                    Log::info("Reminder template kosong untuk key={$settingKey}, skip kirim WA", [
                        'customer_code' => $customer->internetCustomer->code,
                    ]);
                    return;
                }            

                $message = strtr($template, [
                    '{{nama}}'       => $customer->name,
                    '{{kode}}'       => $customer->internetCustomer->code,
                    '{{paket}}'      => $customer->internetCustomer->internetPackage->name,
                    '{{jatuh_tempo}}' => $dateJatuhTempo,
                    '{{tagihan}}'    => 'Rp. ' . number_format($customer->internetCustomer->internetPackage->price_nett, 2, ',', '.'),
                    '{{url}}'        => $url,
                ]);

                $response = $this->sendMessage($client, $customer->phone_number, $message);

                \App\Models\WablasLog::record(
                    source: 'internet_customer',
                    sourceId: $customer->internetCustomer->id,
                    phone: $customer->phone_number,
                    message: $message,
                    response: $response ?? [],
                    type: 'text'
                );

                Log::info("WhatsApp reminder sent successfully", [
                    'customer_code' => $customer->internetCustomer->code,
                    'phone' => $customer->phone_number,
                    'due_date' => $customer->end_billing_date
                ]);
            } else {
                Log::error("Wablas client status check failed");
            }
        } catch (\Throwable $th) {
            Log::error("Failed to send WhatsApp reminder: " . $th->getMessage(), [
                'customer_id' => $customer->id,
                'trace' => $th->getTraceAsString()
            ]);
            throw $th;
        }
    }

    /**
     * Send message via Wablas
     */
    private function sendMessage($client, $phone, $message)
    {
        $send = new Message($client);
        $send_text = $send->single_text($phone, $message);

        return $send_text;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return ['billing-reminder', 'customer:' . $this->customer->id];
    }
}
