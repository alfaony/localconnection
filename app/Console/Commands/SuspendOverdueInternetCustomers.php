<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InternetCustomer;
use App\Models\SettingCompany;
use App\Models\WablasLog;
use App\Schemas\ParamSchema;
use App\Services\Weblas\WablasClient;
use App\Services\Weblas\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuspendOverdueInternetCustomers extends Command
{
    protected $signature = 'internet:suspend-overdue
                            {--count : Tampilkan jumlah customer tanpa mengeksekusi}
                            {--execute : Jalankan suspend dan kirim notifikasi WA}';

    protected $description = 'Suspend internet customers yang end_billing_date sudah lewat dan kirim notifikasi WA';

    public function handle()
    {
        $this->info('Mencari customer dengan tagihan jatuh tempo...');
        $this->newLine();

        $customers = $this->getOverdueCustomers();

        if ($customers->isEmpty()) {
            $this->info('Tidak ada customer yang melewati tanggal jatuh tempo.');
            return Command::SUCCESS;
        }

        $count = $customers->count();
        $this->info("Ditemukan {$count} customer dengan pembayaran jatuh tempo.");
        $this->newLine();

        $this->displayTable($customers);

        if ($this->option('count')) {
            $this->newLine();
            $this->warn('Ini adalah dry run. Gunakan --execute untuk menjalankan suspend.');
            return Command::SUCCESS;
        }

        if ($this->option('execute')) {
            if (!$this->confirm("Lanjutkan suspend {$count} customer dan kirim notifikasi WA?", false)) {
                $this->info('Operasi dibatalkan.');
                return Command::SUCCESS;
            }

            $this->newLine();
            $this->info('Memproses suspend...');

            $result = $this->processSuspend($customers);

            $this->newLine();
            $this->info("Selesai. Suspended: {$result['suspended']}, WA Terkirim: {$result['wa_sent']}, WA Gagal: {$result['wa_failed']}");

            return Command::SUCCESS;
        }

        $this->newLine();
        $this->warn('Gunakan --count untuk preview atau --execute untuk menjalankan suspend.');

        return Command::SUCCESS;
    }

    protected function getOverdueCustomers()
    {
        return InternetCustomer::with([
            'userCustomer',
            'internetPackage',
            'company',
        ])
        ->whereIn('status', [ParamSchema::SUSPENDED])
        ->whereHas('userCustomer', function ($q) {
            $q->whereNotNull('end_billing_date')
              ->where('end_billing_date', '<', now()->startOfDay());
        })
        ->orderBy('company_id')
        ->get();
    }

    protected function displayTable($customers)
    {
        $rows = $customers->map(function ($c) {
            return [
                $c->code,
                $c->name,
                $c->status,
                $c->userCustomer->end_billing_date
                    ? Carbon::parse($c->userCustomer->end_billing_date)->format('d M Y')
                    : '-',
                $c->userCustomer->phone_number ?? '-',
                $c->company->name ?? '-',
            ];
        })->toArray();

        $this->table(
            ['Kode', 'Nama', 'Status', 'Jatuh Tempo', 'No. HP', 'Company'],
            $rows
        );
    }

    protected function processSuspend($customers)
    {
        $suspended = 0;
        $waSent    = 0;
        $waFailed  = 0;

        // Cache wablas settings per company agar tidak query berulang
        $wablasSettings = [];

        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        foreach ($customers as $customer) {
            DB::beginTransaction();
            try {
                $customer->update(['status' => ParamSchema::SUSPENDED]);
                $suspended++;

                $result = $this->sendWaNotification($customer, $wablasSettings);
                if ($result) {
                    $waSent++;
                } else {
                    $waFailed++;
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $waFailed++;
                Log::error('SuspendOverdueInternetCustomers: gagal proses customer', [
                    'customer_id'   => $customer->id,
                    'customer_code' => $customer->code,
                    'error'         => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();

        return [
            'suspended' => $suspended,
            'wa_sent'   => $waSent,
            'wa_failed' => $waFailed,
        ];
    }

    protected function sendWaNotification(InternetCustomer $customer, array &$wablasSettings): bool
    {
        $companyId = $customer->company_id;

        if (!isset($wablasSettings[$companyId])) {
            $wablasSettings[$companyId] = SettingCompany::byCompany($companyId)
                ->where('menu', 'wablas')
                ->get()
                ->pluck('field_value', 'field_title')
                ->toArray();
        }

        $settings = $wablasSettings[$companyId];
        $phone    = $customer->userCustomer->phone_number ?? null;

        if (!$phone) {
            Log::warning('SuspendOverdueInternetCustomers: no phone', ['customer_id' => $customer->id]);
            return false;
        }

        if (empty($settings['server_wablas']) || empty($settings['token_wablas'])) {
            Log::warning('SuspendOverdueInternetCustomers: wablas tidak dikonfigurasi', [
                'company_id' => $companyId,
            ]);
        }

        $message = $this->buildMessage($customer);

        try {
            $client  = new WablasClient(
                $settings['server_wablas'],
                $settings['token_wablas'],
                $settings['webhook_key_wablas'] ?? null
            );
            $sender   = new Message($client);
            $response = $sender->single_text($phone, $message);


            WablasLog::record(
                source: 'internet_customer',
                sourceId: $customer->id,
                phone: $phone,
                message: $message,
                response: $response ?? [],
                type: 'text'
            );


            return !empty($response['status']);
        } catch (\Throwable $e) {
            Log::error('SuspendOverdueInternetCustomers: gagal kirim WA', [
                'customer_id' => $customer->id,
                'error'       => $e->getMessage(),
            ]);

            WablasLog::create([
                'company_id' => $companyId,
                'source'     => 'internet_customer',
                'source_id'  => $customer->id,
                'phone'      => $phone,
                'message'    => $message,
                'type'       => 'text',
                'status'     => 'failed',
                'response'   => ['error' => $e->getMessage()],
            ]);

            return false;
        }
    }

    protected function buildMessage(InternetCustomer $customer): string
    {
        $name         = $customer->userCustomer->name ?? $customer->name;
        $code         = $customer->code;
        $packageName  = $customer->internetPackage->name ?? '-';
        $endBilling   = $customer->userCustomer->end_billing_date
            ? Carbon::parse($customer->userCustomer->end_billing_date)->locale('id')->isoFormat('D MMMM Y')
            : '-';
        $companyName  = $customer->company->name ?? 'Kami';

        $url = route('internet-customer.customer.show', $customer->code);
        $tutorialPayment = config('services.internet_custom.tutorial_payment');

       return "*Pemberitahuan Tagihan Layanan Internet*\n\n"
            . "*Yth. Bapak/Ibu {$name},*\n"
            . "Kami informasikan bahwa tagihan layanan internet Anda sudah melewati tanggal jatuh tempo pembayaran.\n\n"
            . "Berikut detail tagihan Anda:\n\n"
            . "ID Pelanggan: {$code}\n"
            . "Paket Layanan: {$packageName}\n"
            . "Jatuh Tempo Pembayaran: {$endBilling}\n\n"
            . "{$url}\n\n"
            . "{$tutorialPayment}\n\n"
            . "Saat ini layanan internet Anda sementara belum dapat digunakan secara normal karena pembayaran belum terkonfirmasi pada sistem kami.\n\n"
            . "Agar layanan internet dapat kembali aktif, mohon kesediaannya untuk segera melakukan pembayaran atau menghubungi admin kami untuk konfirmasi lebih lanjut.\n\n"
            . "Apabila pembayaran sudah dilakukan, mohon abaikan pesan ini atau silakan kirimkan bukti pembayaran agar dapat kami bantu proses verifikasi.\n\n"
            . "Terima kasih atas perhatian dan kerjasamanya 🙏.\n\n"
            . "*Hormat kami,*\n"
            . "*Hikarinet by KAILI Global*";
    }
}
