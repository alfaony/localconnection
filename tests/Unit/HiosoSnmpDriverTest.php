<?php

namespace Tests\Unit;

use App\Models\Olt;
use App\Services\Olt\Contracts\SnmpClient;
use App\Services\Olt\Drivers\HiosoSnmpDriver;
use PHPUnit\Framework\TestCase;

class HiosoSnmpDriverTest extends TestCase
{
    public function test_it_reads_standard_health_oids(): void
    {
        $olt = new Olt(['vendor' => 'hioso']);
        $snmp = new FakeSnmpClient([
            '.1.3.6.1.2.1.1.1.0' => 'HiOSO EPON OLT',
            '.1.3.6.1.2.1.1.5.0' => 'OLT-POP-01',
            '.1.3.6.1.2.1.1.3.0' => '(123400) 0:20:34.00',
        ]);

        $health = (new HiosoSnmpDriver($olt, $snmp))->health();

        $this->assertSame('HiOSO EPON OLT', $health['system_description']);
        $this->assertSame('OLT-POP-01', $health['system_name']);
        $this->assertSame(1234, $health['uptime_seconds']);
    }

    public function test_it_normalizes_vendor_onu_walks_using_configured_oid_map(): void
    {
        $olt = new Olt([
            'vendor' => 'hioso',
            'oid_map' => [
                'onu' => [
                    'status' => '.1.3.6.1.4.1.1000.1',
                    'serial_number' => '.1.3.6.1.4.1.1000.2',
                    'rx_power' => '.1.3.6.1.4.1.1000.3',
                ],
                'status_values' => ['1' => 'ONLINE', '2' => 'LOS'],
                'scales' => ['rx_power' => 0.01],
            ],
        ]);

        $snmp = new FakeSnmpClient([], [
            '.1.3.6.1.4.1.1000.1' => ['1.7' => '1', '1.8' => '2'],
            '.1.3.6.1.4.1.1000.2' => ['1.7' => 'SN001', '1.8' => 'SN002'],
            '.1.3.6.1.4.1.1000.3' => ['1.7' => '-2350', '1.8' => '-2810'],
        ]);

        $onus = (new HiosoSnmpDriver($olt, $snmp))->onus();

        $this->assertCount(2, $onus);
        $this->assertSame('1', $onus[0]['pon_port']);
        $this->assertSame('7', $onus[0]['onu_index']);
        $this->assertSame('ONLINE', $onus[0]['status']);
        $this->assertSame(-23.5, $onus[0]['rx_power']);
        $this->assertSame('LOS', $onus[1]['status']);
    }
}

class FakeSnmpClient implements SnmpClient
{
    public function __construct(
        private array $gets = [],
        private array $walks = []
    ) {
    }

    public function get(Olt $olt, string $oid): ?string
    {
        return $this->gets[$oid] ?? null;
    }

    public function walk(Olt $olt, string $oid): array
    {
        return $this->walks[$oid] ?? [];
    }
}
