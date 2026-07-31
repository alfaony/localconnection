<?php

namespace App\Services\Olt;

use App\Models\Olt;
use App\Services\Olt\Contracts\OltDriver;
use App\Services\Olt\Contracts\SnmpClient;
use App\Services\Olt\Drivers\GenericSnmpDriver;
use App\Services\Olt\Drivers\HiosoSnmpDriver;

class OltDriverFactory
{
    public function __construct(private SnmpClient $snmp)
    {
    }

    public function make(Olt $olt): OltDriver
    {
        return match (strtolower($olt->vendor)) {
            'hioso' => new HiosoSnmpDriver($olt, $this->snmp),
            default => new GenericSnmpDriver($olt, $this->snmp),
        };
    }
}
