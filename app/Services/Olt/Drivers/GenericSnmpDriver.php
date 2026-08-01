<?php

namespace App\Services\Olt\Drivers;

use App\Models\Olt;
use App\Models\OltOnu;
use App\Services\Olt\Contracts\OltDriver;
use App\Services\Olt\Contracts\SnmpClient;
use App\Services\Olt\Exceptions\SnmpException;

/**
 * Driver SNMP generic — berlaku untuk SEMUA vendor OLT, tanpa perlu subclass
 * baru tiap ada brand baru. Semua OID data ONU (RX/TX power, status, jarak,
 * dll) dibaca dari kolom Olt::$oid_map (JSON, diisi manual per device lewat
 * UI), BUKAN di-hardcode di kode.
 *
 * Kalau suatu saat ada vendor yang butuh parsing/behavior beda dari skema
 * generic ini (misal format index OID yang aneh, atau butuh query
 * multi-tahap), baru extend class ini dan override method yang relevan.
 * Sebelum itu, satu class ini cukup buat semua vendor.
 */
class GenericSnmpDriver implements OltDriver
{
    protected const OID_SYSTEM_DESCRIPTION = '.1.3.6.1.2.1.1.1.0';
    protected const OID_SYSTEM_UPTIME = '.1.3.6.1.2.1.1.3.0';
    protected const OID_SYSTEM_NAME = '.1.3.6.1.2.1.1.5.0';

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
        return $this->oid('status') !== null;
    }

    /**
     * Sync data ONU berdasarkan oid_map yang dikonfigurasi di device ini.
     * Kalau oid_map belum diisi, throw exception yang jelas — TIDAK silent
     * return array kosong (biar nggak disalahartikan sebagai "OLT tidak
     * punya ONU" padahal sebenarnya "OID-nya belum di-setup").
     */
    public function onus(): array
    {
        if (!$this->supportsOnuSync()) {
            $vendorLabel = strtoupper($this->olt->vendor ?: 'device ini');
            throw new SnmpException(
                "OID ONU untuk {$vendorLabel} belum dikonfigurasi. Masukkan MIB/OID sesuai model OLT di halaman edit OLT."
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

    protected function oid(string $field): ?string
    {
        $oid = data_get($this->olt->oid_map, "onu.{$field}");

        return is_string($oid) && $oid !== '' ? $oid : null;
    }

    protected function position(string $suffix, array $raw): array
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

    protected function normalizeStatus(?string $value): string
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

    protected function scaledNumber(string $field, ?string $value): ?float
    {
        if ($value === null || !is_numeric($value)) {
            return null;
        }

        return round(((float) $value) * (float) data_get($this->olt->oid_map, "scales.{$field}", 1), 2);
    }

    protected function scaledInteger(string $field, ?string $value): ?int
    {
        $number = $this->scaledNumber($field, $value);

        return $number === null ? null : (int) round($number);
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
