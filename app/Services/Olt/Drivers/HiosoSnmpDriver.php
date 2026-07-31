<?php

namespace App\Services\Olt\Drivers;

use App\Models\OltOnu;
use App\Services\Olt\Exceptions\SnmpException;

class HiosoSnmpDriver extends GenericSnmpDriver
{
    private const SUPPORTED_FIELDS = [
        'pon_port',
        'onu_index',
        'serial_number',
        'name',
        'status',
        'rx_power',
        'tx_power',
        'distance_m',
        'last_down_reason',
    ];

    public function supportsOnuSync(): bool
    {
        return $this->oid('status') !== null;
    }

    public function onus(): array
    {
        if (!$this->supportsOnuSync()) {
            throw new SnmpException(
                'OID ONU HiOSO belum dikonfigurasi. Masukkan MIB/OID sesuai model OLT.'
            );
        }

        $walks = [];
        foreach (self::SUPPORTED_FIELDS as $field) {
            if ($oid = $this->oid($field)) {
                $walks[$field] = $this->snmp->walk($this->olt, $oid);
            }
        }

        $keys = [];
        foreach ($walks as $values) {
            $keys = array_merge($keys, array_keys($values));
        }
        $keys = array_unique($keys);
        $rows = [];

        foreach ($keys as $suffix) {
            $raw = array_fill_keys(self::SUPPORTED_FIELDS, null);
            foreach ($walks as $field => $values) {
                $raw[$field] = $values[$suffix] ?? null;
            }

            $position = $this->position($suffix, $raw);
            $rows[] = [
                'pon_port' => $position['pon_port'],
                'onu_index' => $position['onu_index'],
                'serial_number' => $raw['serial_number'] ?: null,
                'name' => $raw['name'] ?: null,
                'status' => $this->normalizeStatus($raw['status']),
                'rx_power' => $this->scaledNumber('rx_power', $raw['rx_power']),
                'tx_power' => $this->scaledNumber('tx_power', $raw['tx_power']),
                'distance_m' => $this->scaledInteger('distance_m', $raw['distance_m']),
                'last_down_reason' => $raw['last_down_reason'] ?: null,
                'raw_data' => array_filter($raw, static fn ($value) => $value !== null),
            ];
        }

        return $rows;
    }

    private function oid(string $field): ?string
    {
        $oid = data_get($this->olt->oid_map, "onu.{$field}");

        return is_string($oid) && $oid !== '' ? $oid : null;
    }

    private function position(string $suffix, array $raw): array
    {
        if (!empty($raw['pon_port']) && !empty($raw['onu_index'])) {
            return [
                'pon_port' => (string) $raw['pon_port'],
                'onu_index' => (string) $raw['onu_index'],
            ];
        }

        $segments = explode('.', $suffix);

        return [
            'pon_port' => (string) ($raw['pon_port'] ?: ($segments[count($segments) - 2] ?? '0')),
            'onu_index' => (string) ($raw['onu_index'] ?: end($segments)),
        ];
    }

    private function normalizeStatus(?string $value): string
    {
        if ($value === null || $value === '') {
            return OltOnu::STATUS_UNKNOWN;
        }

        $statusValues = (array) data_get($this->olt->oid_map, 'status_values', []);
        $mapped = $statusValues[(string) $value] ?? null;
        $status = strtoupper((string) ($mapped ?: $value));

        return match (true) {
            in_array($status, ['ONLINE', 'UP', 'ACTIVE'], true) => OltOnu::STATUS_ONLINE,
            in_array($status, ['OFFLINE', 'DOWN', 'INACTIVE'], true) => OltOnu::STATUS_OFFLINE,
            str_contains($status, 'LOS') => OltOnu::STATUS_LOS,
            default => OltOnu::STATUS_UNKNOWN,
        };
    }

    private function scaledNumber(string $field, ?string $value): ?float
    {
        if ($value === null || !is_numeric($value)) {
            return null;
        }

        return round(((float) $value) * (float) data_get($this->olt->oid_map, "scales.{$field}", 1), 2);
    }

    private function scaledInteger(string $field, ?string $value): ?int
    {
        $number = $this->scaledNumber($field, $value);

        return $number === null ? null : (int) round($number);
    }
}
