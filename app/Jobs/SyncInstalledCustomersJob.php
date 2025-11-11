<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\Router;
use App\Schemas\ParamSchema;
use App\Services\RouterOSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RouterOS\Query;
use Illuminate\Support\Str;
use App\Models\PppoeServer;

class SyncInstalledCustomersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function __construct(public ?array $customerIds = null) {} // opsional: limit ke ID tertentu

    public function handle(RouterOSService $ros): void
    {
        // Ambil customer berstatus INSTALLED dan punya router + username
        $query = InternetCustomer::query()
            ->with('router')
            ->whereIn('status', [ParamSchema::INSTALLED, ParamSchema::REACTIVATED])
            ->whereNotNull('router_id')
            ->whereNotNull('username');

        if ($this->customerIds) {
            $query->whereIn('id', $this->customerIds);
        }

        $totalChecked = 0;
        $totalActivated = 0;

        // Proses per-chunk untuk hemat memori
        $query->chunkById(200, function (Collection $customers) use ($ros, &$totalChecked, &$totalActivated) {
            // Kelompokkan per router agar 1 router → 1 koneksi
            /** @var \Illuminate\Support\Collection $byRouter */
            $byRouter = $customers->groupBy('router_id');

            foreach ($customers as $cust) 
            {
                /** @var Router $router */
                $router = $cust->router;
                if (!$router) {
                    Log::warning('Router missing for customers', ['router_id' => $cust->router_id]);
                    continue;
                }

                try {
                    $client = $ros->client($router);
                } catch (\Throwable $e) 
                {
                    Log::error('Failed connect to router', [
                        'router_id' => $cust->router_id,
                        'name' => $router->name ?? null,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }

                try {
                    $isUp = $ros->isUserActive($client, $cust->username);
                    if ($isUp) 
                    {
                        // 1) Ambil snapshot sesi aktif + secret
                        $activeRow = $client->query(
                            (new Query('/ppp/active/print'))->where('name', $cust->username)
                        )->read()[0] ?? [];
                        
                        $secretRow = $client->query(
                            (new Query('/ppp/secret/print'))->where('name', $cust->username)
                        )->read()[0] ?? [];

                        // 2) Isi IP & MAC dari sesi aktif
                        $ip  = $activeRow['address']    ?? $cust->ip_address;
                        $mac = $activeRow['caller-id']  ?? $cust->mac_address;

                        // 3) Tentukan VLAN (best-effort): ambil dari PPPoE server pertama yang punya interface VLAN
                        $vlanId = $cust->vlan_id;
                        if (is_null($vlanId)) {
                            $srv = PppoeServer::with('interface')
                                ->where('router_id', $cust->router_id)
                                ->whereNotNull('interface_id')
                                ->first();
                            $vlanId = $srv?->interface?->vlan_id ?? $vlanId;
                        }

                        // 4) Pastikan ros_comment_uuid ada & tempelkan ke secret.comment (anchor rekonsiliasi)
                        $uuid = $cust->ros_comment_uuid ?: (string) Str::uuid();
                        if (!empty($secretRow['.id'])) {
                            $commentShould = 'cust:' . $uuid;
                            if (($secretRow['comment'] ?? '') !== $commentShould) {
                                $client->query(
                                    (new Query('/ppp/secret/set'))
                                        ->equal('.id', $secretRow['.id'])
                                        ->equal('comment', $commentShould)
                                )->read();
                                $secretRow['comment'] = $commentShould;
                            }
                        }

                        // 5) Susun meta (ros_active + ros_secret) untuk audit
                        $meta = (array) $cust->meta;
                        $meta['ros_active'] = [
                            'id'         => $activeRow['.id']        ?? null,
                            'address'    => $activeRow['address']    ?? null,
                            'caller_id'  => $activeRow['caller-id']  ?? null,
                            'uptime'     => $activeRow['uptime']     ?? null,
                            'encoding'   => $activeRow['encoding']   ?? null,
                            'service'    => $activeRow['service']    ?? null,
                            'last_seen'  => now()->toIso8601String(),
                        ];
                        $meta['ros_secret'] = [
                            'id'       => $secretRow['.id']      ?? null,
                            'disabled' => $secretRow['disabled'] ?? null,
                            'profile'  => $secretRow['profile']  ?? null,
                            'comment'  => $secretRow['comment']  ?? null,
                        ];

                        // 6) Update customer
                        $cust->fill([
                            'status'           => \App\Schemas\ParamSchema::ACTIVE,
                            'ip_address'       => $ip,
                            'mac_address'      => $mac,
                            'vlan_id'          => $vlanId,
                            'ros_comment_uuid' => $uuid,
                            // 'expires_at'     => JANGAN diubah di sini; itu urusan billing/renewal
                        ]);
                        $cust->meta = $meta;
                        $cust->save();
                        
                        $totalActivated++;
                        Log::info('Customer activated by sync', [
                            'customer_id' => $cust->id,
                            'username'    => $cust->username,
                            'router_id'   => $cust->router_id,
                            'ip'          => $ip,
                            'mac'         => $mac,
                            'vlan_id'     => $vlanId,
                        ]);
                    }
                } catch (\Throwable $e) {
                    // dd($e);
                    Log::error('Check active failed', [
                        'customer_id' => $cust->id,
                        'username'    => $cust->username,
                        'router_id'   => $cust->router_id,
                        'error'       => $e->getMessage()
                    ]);
                }

            }
        });

        Log::info('SyncInstalledCustomersJob done', [
            'checked'   => $totalChecked,
            'activated' => $totalActivated
        ]);
    }
}