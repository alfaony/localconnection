<?php

namespace App\Services\Mikrotik;

use App\Models\InternetPackage;
use App\Models\PackageRouterProfile;

class PolicyResolver
{
    public function resolve(int $routerId, InternetPackage $pkg): array
    {
        $map = PackageRouterProfile::where('router_id', $routerId)
            ->where('package_id', $pkg->id)->first();

        $profile   = $map->ros_profile ?? ('PKG_'.$pkg->id);
        $fup       = data_get($map, 'meta.ros_profile_fup') ?? ($profile.'_FUP');
        $down      = $pkg->rate_down_mbps ?? $pkg->bandwidth;
        $up        = $pkg->rate_up_mbps   ?? max(1, (int)ceil(($pkg->bandwidth ?? 1)*0.2));
        $rate      = "{$down}M/{$up}M";
        $rateFup   = "{$pkg->fup_rate_down_mbps}M/{$pkg->fup_rate_up_mbps}M";
        $addrList  = data_get($map, 'meta.address_list');
        $addrListF = data_get($map, 'meta.fup_address_list');

        return compact('profile','fup','rate','rateFup','addrList','addrListF');
    }
}