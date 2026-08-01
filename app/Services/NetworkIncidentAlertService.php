<?php

namespace App\Services;

use App\Models\NetworkIncident;
use App\Models\Router;
use App\Models\SettingCompany;
use App\Services\WhatsApp\WahaClient;
use App\Services\WhatsApp\WhatsAppSender;
use App\Services\WhatsApp\WhatsAppSendException;
use Illuminate\Support\Facades\Log;

/**
 * Deteksi customer yang tiba-tiba putus (bukan karena suspend/isolir
 * billing) dan langsung kirim alert WA — ke NOC/teknisi (selalu) dan ke
 * customer (selalu, pesan lebih halus).
 *
 * Grouping otomatis: kalau banyak customer di router yang sama putus
 * bareng dalam 1x sync run, dikirim 1 pesan rangkuman ke NOC (bukan
 * spam per-customer) — tapi tetap dikirim SAAT ITU JUGA, tidak ditunda.
 * Threshold ini murni soal "1 pesan vs N pesan", bukan soal delay waktu.
 */
class NetworkIncidentAlertService
{
    /**
     * Di atas angka ini, NOC dapat 1 pesan rangkuman. Di bawah/sama,
     * tiap customer dapat pesan sendiri (lebih detail per-customer).
     */
    protected const MASS_OUTAGE_THRESHOLD = 3;

    /**
     * @param array $downCustomers Array of stdClass/object customer (dari
     *   DB::table query), minimal punya: id, router_id, name, code,
     *   username, phone (kalau ada), company_id
     */
    public function reportDown(array $downCustomers): void
    {
        if (empty($downCustomers)) {
            return;
        }

        $byRouter = collect($downCustomers)->groupBy(fn ($c) => $c->router_id ?? 0);

        foreach ($byRouter as $routerId => $customers) {
            $router = $routerId ? Router::find($routerId) : null;
            $isMassOutage = $customers->count() > self::MASS_OUTAGE_THRESHOLD;

            $incidents = $customers->map(function ($customer) use ($router, $isMassOutage, $customers) {
                return NetworkIncident::create([
                    'company_id' => $customer->company_id,
                    'internet_customer_id' => $customer->id,
                    'router_id' => $router?->id,
                    'type' => $isMassOutage ? NetworkIncident::TYPE_MASS_OUTAGE : NetworkIncident::TYPE_SINGLE,
                    'affected_count' => $customers->count(),
                    'detected_at' => now(),
                ]);
            });

            try {
                if ($isMassOutage) {
                    $this->notifyNocMassOutage($router, $customers, $incidents);
                } else {
                    foreach ($customers as $customer) {
                        $this->notifyNocSingle($router, $customer, $incidents->firstWhere('internet_customer_id', $customer->id));
                    }
                }

                foreach ($customers as $customer) {
                    $this->notifyCustomer($customer, $incidents->firstWhere('internet_customer_id', $customer->id));
                }
            } catch (\Throwable $e) {
                Log::error('[NetworkIncidentAlert] Gagal kirim notifikasi', [
                    'router_id' => $routerId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Tandai insiden selesai kalau customer kedeteksi online lagi.
     * Dipanggil dari SyncInstalledCustomersJob saat customer yang
     * sebelumnya down sekarang ketemu lagi.
     */
    public function reportRecovered(array $recoveredCustomerIds): void
    {
        if (empty($recoveredCustomerIds)) {
            return;
        }

        $openIncidents = NetworkIncident::open()
            ->whereIn('internet_customer_id', $recoveredCustomerIds)
            ->get();

        foreach ($openIncidents as $incident) {
            $incident->update(['resolved_at' => now()]);
        }

        Log::info('[NetworkIncidentAlert] Insiden resolved', [
            'count' => $openIncidents->count(),
        ]);
    }

    protected function notifyNocMassOutage(?Router $router, $customers, $incidents): void
    {
        $wa = $this->whatsappFor($customers->first()->company_id);
        $nocNumbers = $this->nocNumbers($customers->first()->company_id);

        if (!$wa || empty($nocNumbers)) {
            Log::warning('[NetworkIncidentAlert] NOC WA tidak dikonfigurasi, skip notif NOC', [
                'router_id' => $router?->id,
            ]);
            return;
        }

        $routerName = $router?->name ?? 'Router tidak diketahui';
        $list = $customers->take(15)->map(fn ($c) => "- {$c->name} ({$c->code})")->implode("\n");
        $more = $customers->count() > 15 ? "\n...dan " . ($customers->count() - 15) . " customer lainnya" : '';

        $message = "🔴 *GANGGUAN MASSAL TERDETEKSI*\n\n"
            . "Router: *{$routerName}*\n"
            . "Total customer terdampak: *{$customers->count()}*\n\n"
            . "Daftar customer:\n{$list}{$more}\n\n"
            . "Kemungkinan: router down, fiber putus, atau listrik padam di lokasi. Mohon segera dicek.";

        $responses = [];
        foreach ($nocNumbers as $number) {
            try {
                $responses[$number] = $wa->sendText($number, $message);
            } catch (WhatsAppSendException $e) {
                Log::error('[NetworkIncidentAlert] Gagal kirim WA ke NOC (mass outage)', [
                    'number' => $number,
                    'error' => $e->getMessage(),
                ]);
                $responses[$number] = ['error' => $e->getMessage()];
            }
        }

        foreach ($incidents as $incident) {
            $incident->update([
                'noc_notified_at' => now(),
                'noc_wa_response' => json_encode($responses),
            ]);
        }
    }

    protected function notifyNocSingle(?Router $router, $customer, ?NetworkIncident $incident): void
    {
        $wa = $this->whatsappFor($customer->company_id);
        $nocNumbers = $this->nocNumbers($customer->company_id);

        if (!$wa || empty($nocNumbers)) {
            Log::warning('[NetworkIncidentAlert] NOC WA tidak dikonfigurasi, skip notif NOC', [
                'customer_id' => $customer->id,
            ]);
            return;
        }

        $routerName = $router?->name ?? 'Router tidak diketahui';
        $message = "🟠 *Customer Terputus*\n\n"
            . "Nama: *{$customer->name}* ({$customer->code})\n"
            . "Router: {$routerName}\n"
            . "Username: {$customer->username}\n\n"
            . "Mohon dicek kondisi koneksinya.";

        $responses = [];
        foreach ($nocNumbers as $number) {
            try {
                $responses[$number] = $wa->sendText($number, $message);
            } catch (WhatsAppSendException $e) {
                Log::error('[NetworkIncidentAlert] Gagal kirim WA ke NOC (single)', [
                    'number' => $number,
                    'error' => $e->getMessage(),
                ]);
                $responses[$number] = ['error' => $e->getMessage()];
            }
        }

        $incident?->update([
            'noc_notified_at' => now(),
            'noc_wa_response' => json_encode($responses),
        ]);
    }

    protected function notifyCustomer($customer, ?NetworkIncident $incident): void
    {
        if (empty($customer->phone)) {
            return; // tidak ada nomor, skip diam-diam (bukan error)
        }

        $wa = $this->whatsappFor($customer->company_id);
        if (!$wa) {
            return;
        }

        $message = "Halo {$customer->name},\n\n"
            . "Mohon maaf, sistem kami mendeteksi gangguan pada koneksi internet Anda. "
            . "Tim teknis kami sedang memeriksa dan akan segera menangani gangguan ini.\n\n"
            . "Terima kasih atas kesabarannya 🙏";

        try {
            $response = $wa->sendText($customer->phone, $message);
        } catch (WhatsAppSendException $e) {
            Log::error('[NetworkIncidentAlert] Gagal kirim WA ke customer', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        $incident?->update([
            'customer_notified_at' => now(),
            'customer_wa_response' => json_encode($response),
        ]);
    }

    protected function whatsappFor(string $companyId): ?WhatsAppSender
    {
        $settingCompany = SettingCompany::byCompany($companyId)->get()->pluck('field_value', 'field_title');

        if (empty($settingCompany['waha_base_url'])) {
            return null;
        }

        return new WahaClient(
            $settingCompany['waha_base_url'],
            $settingCompany['waha_session'] ?? 'default',
            $settingCompany['waha_api_key'] ?? null,
        );
    }

    /**
     * Nomor WA NOC/teknisi internal, dari setting 'noc_wa_number' (bisa
     * lebih dari 1 nomor, dipisah koma). WAJIB ditambahkan manual dulu ke
     * SettingCompany sebelum fitur ini bisa notif NOC — belum ada UI form
     * khusus buat ini, lihat NETWORK_INCIDENT_SETUP.md.
     */
    protected function nocNumbers(string $companyId): array
    {
        $settingCompany = SettingCompany::byCompany($companyId)->get()->pluck('field_value', 'field_title');
        $raw = $settingCompany['noc_wa_number'] ?? null;

        if (!$raw) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $raw)));
    }
}
