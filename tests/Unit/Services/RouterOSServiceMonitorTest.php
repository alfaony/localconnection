<?php

namespace Tests\Unit\Services;

use App\Models\Router;
use App\Services\RouterOSService;
use Mockery;
use PHPUnit\Framework\TestCase;
use RouterOS\Client;
use RouterOS\Exceptions\ConnectException;

class RouterOSServiceMonitorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function router(): Router
    {
        return new Router([
            'name'     => 'RT-TEST',
            'host'     => '10.10.10.1',
            'port'     => 8728,
            'username' => 'admin',
            'password' => 'secret',
            'ssl'      => false,
        ]);
    }

    /** @return array{0: RouterOSService, 1: \Mockery\MockInterface} */
    private function serviceWithMockedClient(): array
    {
        $client = Mockery::mock(Client::class);

        $service = Mockery::mock(RouterOSService::class)->makePartial();
        $service->shouldReceive('client')->andReturn($client);

        return [$service, $client];
    }

    /** @test */
    public function test_get_pppoe_status_returns_connected_when_active(): void
    {
        [$service, $client] = $this->serviceWithMockedClient();

        $client->shouldReceive('query')->once()->andReturnSelf();
        $client->shouldReceive('read')->once()->andReturn([[
            'address'   => '192.168.100.10',
            'uptime'    => '1h2m3s',
            'caller-id' => 'AA:BB:CC:DD:EE:FF',
        ]]);

        $result = $service->getPppoeStatus($this->router(), 'customer01');

        $this->assertTrue($result['connected']);
        $this->assertSame('192.168.100.10', $result['ip']);
        $this->assertSame('1h2m3s', $result['uptime']);
        $this->assertSame('AA:BB:CC:DD:EE:FF', $result['caller_id']);
    }

    /** @test */
    public function test_get_pppoe_status_returns_disconnected_when_not_found(): void
    {
        [$service, $client] = $this->serviceWithMockedClient();

        $client->shouldReceive('query')->once()->andReturnSelf();
        $client->shouldReceive('read')->once()->andReturn([]);

        $result = $service->getPppoeStatus($this->router(), 'customer01');

        $this->assertFalse($result['connected']);
        $this->assertNull($result['ip']);
        $this->assertNull($result['uptime']);
        $this->assertNull($result['caller_id']);
    }

    /** @test */
    public function test_ping_hosts_returns_latency_array(): void
    {
        [$service, $client] = $this->serviceWithMockedClient();

        $client->shouldReceive('query')->twice()->andReturnSelf();
        $client->shouldReceive('read')->twice()->andReturn(
            [['time' => '12ms'], ['time' => '14ms'], ['time' => '13ms']],
            [['time' => '20ms'], ['time' => '22ms'], ['time' => '18ms']],
        );

        $result = $service->pingHosts($this->router(), ['google.com', 'facebook.com']);

        $this->assertCount(2, $result);
        $this->assertSame('google.com', $result[0]['host']);
        $this->assertEqualsWithDelta(13.0, $result[0]['avg_rtt'], 0.01);
        $this->assertSame(0.0, $result[0]['loss']);
        $this->assertSame('facebook.com', $result[1]['host']);
        $this->assertEqualsWithDelta(20.0, $result[1]['avg_rtt'], 0.01);
    }

    /** @test */
    public function test_disconnect_pppoe_returns_true_on_success(): void
    {
        [$service, $client] = $this->serviceWithMockedClient();

        $client->shouldReceive('query')->twice()->andReturnSelf();
        $client->shouldReceive('read')->twice()->andReturn(
            [['.id' => '*1', 'name' => 'customer01']],
            [],
        );

        $result = $service->disconnectPppoe($this->router(), 'customer01');

        $this->assertTrue($result);
    }

    /** @test */
    public function test_ping_router_returns_offline_on_connection_failure(): void
    {
        $service = Mockery::mock(RouterOSService::class)->makePartial();
        $service->shouldReceive('client')->andThrow(new ConnectException('Unable to connect'));

        $result = $service->pingRouter($this->router());

        $this->assertSame('offline', $result['status']);
        $this->assertNull($result['latency_ms']);
    }
}
