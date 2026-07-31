<?php

namespace App\Services\Olt\Contracts;

use App\Models\Olt;

interface SnmpClient
{
    public function get(Olt $olt, string $oid): ?string;

    /**
     * Return values keyed by the numeric suffix below the requested OID.
     *
     * @return array<string, string>
     */
    public function walk(Olt $olt, string $oid): array;
}
