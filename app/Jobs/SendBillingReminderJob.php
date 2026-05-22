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
                ->where('menu', 'wablas')
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

                // Get latest purchase for invoice number
                $latestPurchase = $customer->internetCustomer->purchases()->latest()->first();
                $invoiceNumber = $latestPurchase->code ?? '-';

                // Template message untuk reminder (1 hari sebelum jatuh tempo)
                $url = route('internet-customer.customer.show', $customer->internetCustomer->code);
                $dateJatuhTempo = Carbon::parse($customer->end_billing_date)->format('d') . ' ' . Carbon::parse($customer->end_billing_date)->locale('id')->monthName . ' ' . Carbon::parse($customer->end_billing_date)->year;

                $tutorialPayment = config('services.internet_custom.tutorial_payment');
                $message = null;

                if ($daysBeforeDue == 0) {
                    $message = "*Ringkasan Tagihan Layanan Internet*\n\n"
                            . "*Yth. Bapak/Ibu {$customer->name},*\n"
                            . "Kami informasikan bahwa tagihan internet Anda *jatuh tempo HARI INI*. Mohon segera lakukan pembayaran untuk menghindari pemutusan layanan.\n\n"
                            . "Berikut detail tagihan Anda:\n\n"
                            . "ID Pelanggan: {$customer->internetCustomer->code}\n"
                            . "Paket Layanan: {$customer->internetCustomer->internetPackage->name}\n"
                            . "Jatuh Tempo Pembayaran: {$dateJatuhTempo}\n"
                            . "Total Tagihan: Rp. " . number_format($customer->internetCustomer->internetPackage->price_nett, 2, ',', '.') . "\n\n"
                            . "⛔ *PERHATIAN:* Layanan internet Anda akan dihentikan jika pembayaran tidak dilakukan hari ini.\n\n"
                            . "Untuk melakukan pembayaran atau konfirmasi, silakan klik tautan berikut:\n\n"
                            . "{$url}\n\n"
                            . "{$tutorialPayment}"
                            . "Terima kasih atas perhatian dan kerjasama nya 🙏.\n\n"
                            . "*Hormat kami,*\n"
                            . "*Hikarinet by KAILI Global*";
                } elseif ($daysBeforeDue == 1) {
                    $message = "*Ringkasan Tagihan Layanan Internet*\n\n"
                            . "*Yth. Bapak/Ibu {$customer->name},*\n"
                            . "Kami informasikan bahwa jatuh tempo pembayaran tagihan internet akan berakhir kurang dari 1 hari lagi .\n\n"
                            . "Berikut ini adalah pengingat tagihan Anda dengan detail sebagai berikut:\n\n"
                            . "ID Pelanggan: {$customer->internetCustomer->code}\n"
                            . "Paket Layanan: {$customer->internetCustomer->internetPackage->name}\n"
                            . "Jatuh Tempo Pembayaran: {$dateJatuhTempo}\n"
                            . "Total Tagihan: Rp. " . number_format($customer->internetCustomer->internetPackage->price_nett, 2, ',', '.') . "\n\n"
                            . "⛔ Mohon segera lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari penghentian layanan dan pemutusan koneksi internet.\n\n"
                            . "Untuk melakukan pembayaran atau konfirmasi, silakan klik tautan berikut:\n\n"
                            . "{$url}\n\n"
                            . "{$tutorialPayment}"
                            . "Terima kasih atas perhatian dan kerjasama nya 🙏.\n\n"
                            . "*Hormat kami,*\n"
                            . "*Hikarinet by KAILI Global*";
                } elseif ($daysBeforeDue == 3) {
                    $message = "*Ringkasan Tagihan Layanan Internet*\n\n"
                            . "*Yth. Bapak/Ibu {$customer->name},*\n"
                            . "Kami informasikan bahwa jatuh tempo pembayaran tagihan internet Anda tinggal *3 hari lagi*.\n\n"
                            . "Berikut ini adalah pengingat tagihan Anda dengan detail sebagai berikut:\n\n"
                            . "ID Pelanggan: {$customer->internetCustomer->code}\n"
                            . "Paket Layanan: {$customer->internetCustomer->internetPackage->name}\n"
                            . "Jatuh Tempo Pembayaran: {$dateJatuhTempo}\n"
                            . "Total Tagihan: Rp. " . number_format($customer->internetCustomer->internetPackage->price_nett, 2, ',', '.') . "\n\n"
                            . "⛔ Mohon segera lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari penghentian layanan dan pemutusan koneksi internet.\n\n"
                            . "Untuk melakukan pembayaran atau konfirmasi, silakan klik tautan berikut:\n\n"
                            . "{$url}\n\n"
                            . "{$tutorialPayment}"
                            . "Terima kasih atas perhatian dan kerjasama nya 🙏.\n\n"
                            . "*Hormat kami,*\n"
                            . "*Hikarinet by KAILI Global*";
                }

                if($message){
                    $response = $this->sendMessage($client, $customer->phone_number, $message);

                    \App\Models\WablasLog::record(
                        source: 'internet_customer',
                        sourceId: $customer->internetCustomer->id,
                        phone: $customer->phone_number,
                        message: $message,
                        response: $response ?? [],
                        type: 'text'
                    );
                }

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
