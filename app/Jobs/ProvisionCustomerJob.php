<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\Router;
use App\Models\PackageRouterProfile;
use App\Services\RouterOSService;
use App\Jobs\SyncInstalledCustomersJob;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Schemas\ParamSchema;
use App\Services\PoolResolver;

class ProvisionCustomerJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    public function __construct(public string $internetCustomerId) {}

    public function handle(RouterOSService $ros): void
    {
        $cust = InternetCustomer::with(['internetPackage','router'])->findOrFail($this->internetCustomerId);

        try {
            /** @var Router $router */
            $router = $cust->router;
            /** @var InternetPackage $pkg */
            $pkg = $cust->internetPackage;
    
            // dd($router, $pkg);
            $map = PackageRouterProfile::where('router_id',$router->id)
                  ->where('package_id',$pkg->id)->first();
    
            $pool = PoolResolver::forCustomer($cust);
            $poolName = $pool?->name;
            $gateway  = $pool?->gateway;

            $profile  = $map->ros_profile ?? ('PKG_'.$pkg->id);
            $fup      = $profile.'_FUP';

            // pastikan profil isi rate-limit + remote-address (pool) + DNS
            
            $client = $ros->client($router);
            if ($cust->status == ParamSchema::INSTALLED) 
            {
                // pastikan profile ada
                $ros->ensurePppProfile(
                    $client,
                    $pkg,
                    $profile,
                    $fup,
                    $cust->router_id,
                    $poolName,
                    $gateway
                );
                // $ros->ensurePppProfile($client, $pkg, $profile, $fup, $cust->router_id);

                    // upsert secret + enable/disable by status
                $ros->upsertPppSecret($client, $cust, $profile, $cust->local_address);
            }
            elseif ($cust->status == ParamSchema::SUSPENDED)
            {
                $suspendProfileName = 'SUSPENDED';
                // buat profile SUSPENDED di MikroTik jika belum ada (2M/2M, tanpa pool)
                $ros->ensureSuspendedPppProfile($client, $suspendProfileName);

                $row = $client->query(
                    (new \RouterOS\Query('/ppp/secret/print'))->where('name', $cust->username)
                )->read()[0] ?? null;

                if ($row)
                {
                    $qSet = (new \RouterOS\Query('/ppp/secret/set'))
                        ->equal('.id', $row['.id'])
                        ->equal('profile', $suspendProfileName);
                    $client->query($qSet)->read();

                    $meta = (array) $cust->meta;
                    $meta['ros_secret'] = [
                        'id'       => $row['.id'] ?? null,
                        'disabled' => "no",
                        'profile'  => $suspendProfileName,
                        'comment'  => $row['comment'] ?? null,
                    ];
                    $cust->meta = $meta;
                    $cust->save();
                }

                $ros->disconnectIfActive($client, $cust->username);
            }

            elseif ($cust->status == ParamSchema::REACTIVATED) 
            {
                $profile = $map->ros_profile ?? ('PKG_'.$pkg->id);

                $ros->ensurePppProfile($client, $pkg, $profile, null, $cust->router_id, $poolName, $gateway);
                
                // upsert secret & pastikan enable dengan profil normal
                $ros->upsertPppSecret($client, $cust, $profile, $cust->local_address);

                // opsional: update meta untuk tracking
                $row = $client->query(
                    (new \RouterOS\Query('/ppp/secret/print'))->where('name', $cust->username)
                )->read()[0] ?? null;

                if ($row) 
                {
                    $meta = (array) $cust->meta;
                    $meta['ros_secret'] = [
                        'id'       => $row['.id'] ?? null,
                        'disabled' => $row['disabled'] ?? 'no',
                        'profile'  => $row['profile'] ?? $profile,
                        'comment'  => $row['comment'] ?? null,
                    ];
                    $cust->meta = $meta;
                    $cust->save();
                }

                $ros->disconnectIfActive($client, $cust->username);
            }
            
            // ✅ Trigger sync check after 45 seconds to update status to ACTIVE
            // setelah disconnectIfActive, router butuh waktu reconnect
            if (in_array($cust->status, [ParamSchema::REACTIVATED, ParamSchema::INSTALLED])) {
                dispatch(new SyncInstalledCustomersJob([$cust->id]))->delay(now()->addMinutes(1));
            }

        } catch (\Throwable $th) {
            Log::error('ProvisionCustomerJob failed: '.$th->getMessage(), [
                'customer_id' => $this->internetCustomerId,
            ]);
            throw $th;
        }
    }

    public function failed(Exception $e): void
    {
        // dd($e->getMessage());
        // log ke audit_logs atau update jobs_provisioning bila kamu pakai tabel itu
        \Log::error('Provision failed: '.$e->getMessage(), ['cust'=>$this->internetCustomerId]);
    }
}