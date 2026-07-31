<?php

namespace App\Services\Olt;

use App\Models\Olt;
use App\Models\OltOnu;
use App\Models\OltOnuMetric;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OltMonitoringService
{
    public function __construct(private OltDriverFactory $drivers)
    {
    }

    /**
     * Test credentials without mutating or persisting an OLT.
     */
    public function testConnection(Olt $olt): array
    {
        return $this->drivers->make($olt)->health();
    }

    /**
     * Poll device health and synchronize its ONU inventory when an OID map is
     * configured for the selected vendor/model.
     */
    public function poll(Olt $olt): array
    {
        try {
            $driver = $this->drivers->make($olt);
            $health = $driver->health();
            $onuRows = $driver->supportsOnuSync() ? $driver->onus() : null;

            $result = DB::transaction(function () use ($olt, $health, $onuRows) {
                $olt->update([
                    'status' => Olt::STATUS_UP,
                    'system_description' => $health['system_description'],
                    'uptime_seconds' => $health['uptime_seconds'],
                    'last_polled_at' => now(),
                    'last_success_at' => now(),
                    'last_error' => null,
                ]);

                $synced = $onuRows === null ? null : $this->synchronizeOnus($olt, $onuRows);

                return [
                    'status' => Olt::STATUS_UP,
                    'system_name' => $health['system_name'],
                    'onus_synced' => $synced,
                ];
            });

            return array_merge(['success' => true], $result);
        } catch (Throwable $exception) {
            $message = mb_substr($exception->getMessage(), 0, 1000);

            $olt->forceFill([
                'status' => Olt::STATUS_ERROR,
                'last_polled_at' => now(),
                'last_error' => $message,
            ])->save();

            Log::warning('[OLT] Polling failed', [
                'olt_id' => $olt->id,
                'host' => $olt->host,
                'error' => $message,
            ]);

            return [
                'success' => false,
                'status' => Olt::STATUS_ERROR,
                'message' => $message,
            ];
        }
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function synchronizeOnus(Olt $olt, array $rows): int
    {
        $syncedIds = [];
        $polledAt = now();

        foreach ($rows as $row) {
            $onu = OltOnu::firstOrNew([
                'olt_id' => $olt->id,
                'pon_port' => (string) $row['pon_port'],
                'onu_index' => (string) $row['onu_index'],
            ]);

            $shouldRecordMetric = !$onu->exists
                || $onu->status !== $row['status']
                || $this->numberChanged($onu->rx_power, $row['rx_power'])
                || $this->numberChanged($onu->tx_power, $row['tx_power'])
                || $this->numberChanged($onu->distance_m, $row['distance_m'])
                || !$onu->metrics()->where(
                    'recorded_at',
                    '>=',
                    now()->subMinutes((int) config('olt.history_interval_minutes', 15))
                )->exists();

            $onu->fill([
                'serial_number' => $row['serial_number'],
                'name' => $row['name'],
                'status' => $row['status'],
                'rx_power' => $row['rx_power'],
                'tx_power' => $row['tx_power'],
                'distance_m' => $row['distance_m'],
                'last_down_reason' => $row['last_down_reason'],
                'last_polled_at' => $polledAt,
                'raw_data' => $row['raw_data'],
            ]);

            if ($row['status'] === OltOnu::STATUS_ONLINE) {
                $onu->last_seen_at = $polledAt;
            }

            $onu->save();
            $syncedIds[] = $onu->id;

            if ($shouldRecordMetric) {
                OltOnuMetric::create([
                    'olt_onu_id' => $onu->id,
                    'status' => $onu->status,
                    'rx_power' => $onu->rx_power,
                    'tx_power' => $onu->tx_power,
                    'distance_m' => $onu->distance_m,
                    'recorded_at' => $polledAt,
                ]);
            }
        }

        $missingQuery = $olt->onus();
        if ($syncedIds) {
            $missingQuery->whereNotIn('id', $syncedIds);
        }

        $missingQuery->get()->each(function (OltOnu $onu) use ($polledAt) {
            $wasOffline = $onu->status === OltOnu::STATUS_OFFLINE;
            $onu->update([
                'status' => OltOnu::STATUS_OFFLINE,
                'last_polled_at' => $polledAt,
            ]);

            if (!$wasOffline) {
                OltOnuMetric::create([
                    'olt_onu_id' => $onu->id,
                    'status' => OltOnu::STATUS_OFFLINE,
                    'rx_power' => $onu->rx_power,
                    'tx_power' => $onu->tx_power,
                    'distance_m' => $onu->distance_m,
                    'recorded_at' => $polledAt,
                ]);
            }
        });

        return count($syncedIds);
    }

    private function numberChanged($old, $new): bool
    {
        if ($old === null || $new === null) {
            return $old !== $new;
        }

        return abs((float) $old - (float) $new) >= 0.01;
    }
}
