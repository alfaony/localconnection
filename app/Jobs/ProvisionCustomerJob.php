<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\Router;
use App\Models\PackageRouterProfile;
use App\Services\RouterOSService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProvisionCustomerJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $internetCustomerId, public ?string $initialPlainPassword = null) {}

    public function handle(RouterOSService $ros): void
    {
        $cust = InternetCustomer::with(['internetPackage','router'])->findOrFail($this->internetCustomerId);

        /** @var Router $router */
        $router = $cust->router;
        /** @var InternetPackage $pkg */
        $pkg = $cust->internetPackage;

        // dd($router, $pkg);
        $map = PackageRouterProfile::where('router_id',$router->id)
              ->where('package_id',$pkg->id)->first();

        $profile = $map->ros_profile ?? ('PKG_' . $pkg->id);           // fallback nama profile
        $fup     = $profile.'_FUP';

        $client = $ros->client($router);
        // pastikan profile ada
        $ros->ensurePppProfile($client, $pkg, $profile, $fup);

        // set plaintext password sekali saat create
        if ($this->initialPlainPassword) {
            $cust->pass_hash = $this->initialPlainPassword; // atau simpan terenkripsi untuk 1st push
        }

        // upsert secret + enable/disable by status
        $ros->upsertPppSecret($client, $cust, $cust->status === 'active' ? $profile : $fup);

        // jika suspended → pastikan sesi putus
        // dd($cust->username, $cust);
        if ($cust->status !== 'active') {
            $ros->disconnectIfActive($client, $cust->username);
        }
    }

    public function failed(Exception $e): void
    {
        // dd($e);
        // log ke audit_logs atau update jobs_provisioning bila kamu pakai tabel itu
        \Log::error('Provision failed: '.$e->getMessage(), ['cust'=>$this->internetCustomerId]);
    }
}