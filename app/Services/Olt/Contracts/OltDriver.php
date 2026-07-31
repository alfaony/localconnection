<?php

namespace App\Services\Olt\Contracts;

interface OltDriver
{
    /**
     * @return array{system_description:?string, system_name:?string, uptime_seconds:?int}
     */
    public function health(): array;

    public function supportsOnuSync(): bool;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function onus(): array;
}
