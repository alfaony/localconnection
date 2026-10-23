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
use App\Services\Billing\BillingReminderMessage;

class SendBillingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $customer;
    protected $daysBeforeDue;
    protected array $siblingIds;

    /**
     * @param UserCustomer $customer
     * @param array $siblingIds ID UserCustomer lain dengan nomor HP yang sama, digabung dalam 1 pesan
     */
    public function __construct(UserCustomer $customer, $daysBeforeDue, array $siblingIds = [])
    {
        $this->customer = $customer;
        $this->daysBeforeDue = $daysBeforeDue;
        $this->siblingIds = $siblingIds;
    }

    /**
     * Execute the job.
     * Mengirim peringatan WhatsApp 1 hari sebelum jatuh tempo pembayaran
     */
    public function handle()
    {
        try {
            $customers = $this->eligibleCustomers();

            if ($customers->isEmpty()) {
                return;
            }

            $internetCustomer = $customers->first()->internetCustomer;

            // Get company settings untuk Wablas
            $settingCompany = SettingCompany::byCompany($internetCustomer->company_id)
                // ->where('menu', 'wablas')
                ->get()
                ->pluck('field_value', 'field_title');

            if ($settingCompany['server_wablas'] && $settingCompany['token_wablas']) {
                $this->sentWaReminder($settingCompany, $customers, $this->daysBeforeDue);
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
    private function sentWaReminder($settingCompany, $customers, $daysBeforeDue)
    {
        $customer = $customers->first();

        try {
            $client = new WablasClient(
                $settingCompany['server_wablas'],
                $settingCompany['token_wablas'],
                $settingCompany['webhook_key_wablas']
            );

            if ($client->status()) {
                $settingKey = match((int)$daysBeforeDue) {
                    0 => 'internet_remainder_billing_0',
                    1 => 'internet_remainder_billing_1',
                    3 => 'internet_remainder_billing_3',
                    default => 'internet_remainder_billing_isolir',
                };
                $template = html_to_wa($settingCompany[$settingKey] ?? '');

                // Jika pesan kosong di setting, tidak perlu kirim
                if (empty($template)) {
                    Log::info("Reminder template kosong untuk key={$settingKey}, skip kirim WA", [
                        'customer_code' => $customer->internetCustomer->code,
                    ]);
                    return;
                }

                $message = BillingReminderMessage::build($customers, $template);

                $response = $this->sendMessage($client, $customer->phone_number, $message);

                foreach ($customers as $c) {
                    \App\Models\WablasLog::record(
                        source: 'internet_customer',
                        sourceId: $c->internetCustomer->id,
                        phone: $c->phone_number,
                        message: $message,
                        response: $response ?? [],
                        type: 'text'
                    );
                }

                Log::info("WhatsApp reminder sent successfully", [
                    'customer_codes' => $customers->map(fn ($c) => $c->internetCustomer->code)->all(),
                    'phone' => $customer->phone_number,
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
     * Customer utama + sibling yang masih perlu diingatkan (belum bayar, status WAITING_PAYMENT).
     */
    private function eligibleCustomers()
    {
        $all = collect([$this->customer]);

        if ($this->siblingIds) {
            $all = $all->concat(
                UserCustomer::whereIn('id', $this->siblingIds)
                    ->with(['internetCustomer.internetPackage', 'internetCustomer.purchases'])
                    ->get()
            );
        }

        return $all->filter(function ($c) {
            $internetCustomer = $c->internetCustomer;

            if (!$internetCustomer) {
                Log::warning("Internet customer not found for UserCustomer ID: {$c->id}");
                return false;
            }

            if ($internetCustomer->is_paid) {
                Log::info("Customer {$internetCustomer->code} sudah bayar, skip reminder");
                return false;
            }

            if ($internetCustomer->status != ParamSchema::WAITING_PAYMENT_SUBSCRIPTION) {
                Log::info("Customer {$internetCustomer->code} status tidak waiting payment, skip reminder");
                return false;
            }

            return true;
        })->values();
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
