<?php

namespace App\Services\Olt\Drivers;

use App\Models\Olt;
use App\Services\Olt\Contracts\OltDriver;
use App\Services\Olt\Contracts\SnmpClient;
use App\Services\Olt\Exceptions\SnmpException;

class GenericSnmpDriver implements OltDriver
{
    protected const OID_SYSTEM_DESCRIPTION = '.1.3.6.1.2.1.1.1.0';
    protected const OID_SYSTEM_UPTIME = '.1.3.6.1.2.1.1.3.0';
    protected const OID_SYSTEM_NAME = '.1.3.6.1.2.1.1.5.0';

    public function __construct(
        protected Olt $olt,
        protected SnmpClient $snmp
    ) {
    }

    public function health(): array
    {
        $description = $this->snmp->get($this->olt, self::OID_SYSTEM_DESCRIPTION);

        if ($description === null || $description === '') {
            throw new SnmpException('sysDescr kosong; periksa akses dan konfigurasi SNMP.');
        }

        return [
            'system_description' => $description,
            'system_name' => $this->snmp->get($this->olt, self::OID_SYSTEM_NAME),
            'uptime_seconds' => $this->parseUptime(
                $this->snmp->get($this->olt, self::OID_SYSTEM_UPTIME)
            ),
        ];
    }

    public function supportsOnuSync(): bool
    {
        return false;
    }

    public function onus(): array
    {
        return [];
    }

    private function parseUptime(?string $value): ?int
    {
        if (!$value) {
            return null;
        }

        if (preg_match('/\((\d+)\)/', $value, $matches)) {
            return (int) floor(((int) $matches[1]) / 100);
        }

        if (ctype_digit($value)) {
            return (int) floor(((int) $value) / 100);
        }

        return null;
    }
}
