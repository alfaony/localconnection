<?php

namespace App\Http\Livewire\Router;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Models\{Router, InternetPackage, PackageRouterProfile, AddressPool};
use App\Services\RouterOSService;

class PackageProfileMapping extends Component
{
    public int $routerId;
    public array $rows = [];
    public array $availablePools = [];
    public array $pushResults = [];

    public function mount(int $routerId)
    {
        $this->routerId = $routerId;
        $this->loadPools();
        $this->loadRows();
    }

    public function render()
    {
        $router = Router::findOrFail($this->routerId);
        return view('livewire.router.package-profile-mapping', [
            'router'         => $router,
            'rows'           => $this->rows,
            'availablePools' => $this->availablePools,
            'pushResults'    => $this->pushResults,
        ])->extends('adminlte::page');
    }

    public function loadPools()
    {
        $this->availablePools = AddressPool::where('router_id', $this->routerId)
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id'    => $p->id,
                'label' => $p->name . ($p->cidr ? ' (' . $p->cidr . ')' : ''),
            ])
            ->toArray();
    }

    public function loadRows()
    {
        $pkgs = InternetPackage::where('is_active', true)->orderBy('name')->get();
        $maps = PackageRouterProfile::where('router_id', $this->routerId)
            ->get()
            ->keyBy('package_id');

        $this->rows = [];
        foreach ($pkgs as $p) {
            $map = $maps->get($p->id);
            $this->rows[$p->id] = [
                'package_name'    => $p->name,
                'ros_profile'     => $map->ros_profile     ?? '',
                'address_pool_id' => $map->address_pool_id ?? '',
                'local_address'   => $map->local_address   ?? '',
            ];
        }
    }

    public function save()
    {
        foreach ($this->rows as $packageId => $row) {
            if (!trim($row['ros_profile'])) continue;

            PackageRouterProfile::updateOrCreate(
                ['router_id' => $this->routerId, 'package_id' => $packageId],
                [
                    'ros_profile'     => trim($row['ros_profile']),
                    'address_pool_id' => $row['address_pool_id'] ?: null,
                    'local_address'   => trim($row['local_address']) ?: null,
                ]
            );
        }

        $this->dispatchBrowserEvent('toast', [
            'type'    => 'success',
            'message' => 'Mapping berhasil disimpan.',
        ]);

        $this->loadRows();
    }

    /**
     * Push semua profile mapping ke MikroTik dengan forceOverwrite = true.
     */
    public function pushToRouter()
    {
        $router  = Router::findOrFail($this->routerId);
        $ros     = app(RouterOSService::class);
        $results = [];

        try {
            $client = $ros->client($router);
        } catch (\Throwable $e) {
            $this->dispatchBrowserEvent('toast', [
                'type'    => 'error',
                'message' => 'Gagal konek ke router: ' . $e->getMessage(),
            ]);
            return;
        }

        $maps = PackageRouterProfile::where('router_id', $this->routerId)
            ->with(['package', 'addressPool'])
            ->get();

        foreach ($maps as $map) {
            if (!$map->ros_profile || !$map->package) continue;

            $pkg         = $map->package;
            $profileName = $map->ros_profile;
            $pool        = $map->addressPool?->name;
            $gateway     = $map->local_address ?: $map->addressPool?->gateway;

            try {
                $ros->ensurePppProfile(
                    $client,
                    $pkg,
                    $profileName,
                    null,           // fupProfileName
                    $router->id,    // routerIdForHints (fallback PPPoE Server jika pool null)
                    $pool,
                    $gateway,
                    true            // forceOverwrite ← update nilai yang sudah ada
                );

                $results[] = ['profile' => $profileName, 'status' => 'ok'];

                Log::info('[PackageProfileMapping] Profile pushed', [
                    'router'  => $router->name,
                    'profile' => $profileName,
                    'pool'    => $pool,
                    'gateway' => $gateway,
                ]);
            } catch (\Throwable $e) {
                $results[] = ['profile' => $profileName, 'status' => 'error', 'message' => $e->getMessage()];

                Log::warning('[PackageProfileMapping] Push failed', [
                    'profile' => $profileName,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        $this->pushResults = $results;

        $failed  = collect($results)->where('status', 'error')->count();
        $success = collect($results)->where('status', 'ok')->count();

        $this->dispatchBrowserEvent('toast', [
            'type'    => $failed > 0 ? 'warning' : 'success',
            'message' => "{$success} profile berhasil di-push" . ($failed > 0 ? ", {$failed} gagal." : '.'),
        ]);
    }
}
