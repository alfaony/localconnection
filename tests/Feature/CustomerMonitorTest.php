<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Company;
use App\Models\District;
use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Pop;
use App\Models\Province;
use App\Models\Role;
use App\Models\Router;
use App\Models\Subdistrict;
use App\Models\User;
use App\Schemas\RoleSchema;
use App\Services\RouterOSService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use RouterOS\Exceptions\ConnectException;
use Tests\TestCase;

class CustomerMonitorTest extends TestCase
{
    use DatabaseTransactions;

    private const MONITOR_METHODS = ['monitor', 'status', 'traffic', 'latency', 'restart'];

    private function makeRole(): Role
    {
        $role = new Role();
        $role->name = 'Monitor Test Role ' . Str::random(6);
        $role->guard_name = 'web';
        $role->save();

        foreach (self::MONITOR_METHODS as $method) {
            $permission = new Permission();
            $permission->name = ucwords($method) . ' Internet Customer Test ' . Str::random(6);
            $permission->method = $method;
            $permission->table = 'internet_customers';
            $permission->model = 'InternetCustomer';
            $permission->guard_name = 'web';
            $permission->save();

            $pivot = new PermissionRole();
            $pivot->role_id = $role->id;
            $pivot->permission_id = $permission->id;
            $pivot->save();
        }

        return $role;
    }

    private function makeCompany(): Company
    {
        return InternetPackage::firstOrFail()->company;
    }

    private function makeUser(Role $role, Company $company): User
    {
        return User::create([
            'name'     => 'Monitor Tester',
            'username' => 'monitor_tester_' . Str::random(8),
            'email'    => 'monitor_tester_' . Str::random(8) . '@example.test',
            'password' => bcrypt('secret'),
            'role_id'  => $role->id,
            'company_id' => $company->id,
        ]);
    }

    private function makeRouter(Company $company, User $user): Router
    {
        $pop = Pop::firstOrFail();

        return Router::create([
            'pop_id'     => $pop->id,
            'company_id' => $company->id,
            'user_id'    => $user->id,
            'name'       => 'RT-TEST-' . Str::random(6),
            'host'       => '10.10.10.1',
            'port'       => '8728',
            'username'   => 'admin',
            'password'   => 'secret',
            'ssl'        => false,
        ]);
    }

    private function makeCustomer(Company $company, ?Router $router = null): InternetCustomer
    {
        $province = Province::firstOrFail();
        $city = City::firstOrFail();
        $district = District::firstOrFail();
        $subdistrict = Subdistrict::firstOrFail();
        $package = InternetPackage::firstOrFail();

        return InternetCustomer::create([
            'company_id'           => $company->id,
            'province_id'          => $province->id,
            'city_id'              => $city->id,
            'district_id'          => $district->id,
            'subdistrict_id'       => $subdistrict->id,
            'internet_package_id'  => $package->id,
            'name'                 => 'Monitor Test Customer',
            'address'              => 'Jl. Testing No. 1',
            'status'               => 'customer_existing',
            'access_type'          => 'pppoe',
            'username'             => 'pppoe_test_' . Str::random(8),
            'router_id'            => $router?->id,
        ]);
    }

    /** @return array{0: User, 1: InternetCustomer, 2: Router} */
    private function setupAuthorizedFixtures(): array
    {
        $role = $this->makeRole();
        $company = $this->makeCompany();
        $user = $this->makeUser($role, $company);
        $router = $this->makeRouter($company, $user);
        $customer = $this->makeCustomer($company, $router);

        return [$user, $customer, $router];
    }

    /** @test */
    public function test_monitor_index_requires_auth(): void
    {
        [, $customer] = $this->setupAuthorizedFixtures();

        $response = $this->get(route('internet-customer.monitor', $customer->id));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_monitor_index_shows_customer_monitoring_page(): void
    {
        [$user, $customer] = $this->setupAuthorizedFixtures();

        $response = $this->actingAs($user)->get(route('internet-customer.monitor', $customer->id));

        $response->assertOk();
        $response->assertViewIs('internet-customer.monitor.index');
        $response->assertSee($customer->name);
    }

    /** @test */
    public function test_status_endpoint_returns_json(): void
    {
        [$user, $customer] = $this->setupAuthorizedFixtures();

        $this->mock(RouterOSService::class, function ($mock) {
            $mock->shouldReceive('getPppoeStatus')->once()->andReturn([
                'connected' => true,
                'ip'        => '192.168.100.10',
                'uptime'    => '1h2m3s',
                'caller_id' => 'AA:BB:CC:DD:EE:FF',
            ]);
            $mock->shouldReceive('pingRouter')->once()->andReturn([
                'latency_ms' => 12.5,
                'status'     => 'online',
            ]);
        });

        $response = $this->actingAs($user)->getJson(route('internet-customer.monitor.status', $customer->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'server_status' => 'online',
                'pppoe_status'  => 'connected',
                'ip_address'    => '192.168.100.10',
                'uptime'        => '1h2m3s',
                'caller_id'     => 'AA:BB:CC:DD:EE:FF',
                'latency_ms'    => 12.5,
            ],
        ]);
        $response->assertJsonStructure(['success', 'data', 'timestamp']);
    }

    /** @test */
    public function test_traffic_endpoint_returns_json(): void
    {
        [$user, $customer] = $this->setupAuthorizedFixtures();

        $this->mock(RouterOSService::class, function ($mock) {
            $mock->shouldReceive('getPppoeTraffic')->once()->andReturn([
                'rx_rate'  => 1024000,
                'tx_rate'  => 512000,
                'rx_bytes' => 999999,
                'tx_bytes' => 888888,
            ]);
        });

        $response = $this->actingAs($user)->getJson(route('internet-customer.monitor.traffic', $customer->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'rx_rate'  => 1024000,
                'tx_rate'  => 512000,
                'rx_bytes' => 999999,
                'tx_bytes' => 888888,
            ],
        ]);
    }

    /** @test */
    public function test_latency_endpoint_returns_json(): void
    {
        [$user, $customer] = $this->setupAuthorizedFixtures();

        $this->mock(RouterOSService::class, function ($mock) {
            $mock->shouldReceive('pingHosts')->once()->andReturn([
                ['host' => 'google.com', 'avg_rtt' => 10.0, 'loss' => 0.0],
                ['host' => 'facebook.com', 'avg_rtt' => 15.0, 'loss' => 0.0],
                ['host' => 'youtube.com', 'avg_rtt' => 20.0, 'loss' => 0.0],
                ['host' => 'whatsapp.com', 'avg_rtt' => 25.0, 'loss' => 0.0],
                ['host' => 'instagram.com', 'avg_rtt' => 30.0, 'loss' => 0.0],
            ]);
        });

        $response = $this->actingAs($user)->getJson(route('internet-customer.monitor.latency', $customer->id));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => ['targets'],
            'timestamp',
        ]);
        $response->assertJsonCount(5, 'data.targets');
    }

    /** @test */
    public function test_restart_requires_post_method(): void
    {
        [$user, $customer] = $this->setupAuthorizedFixtures();

        $response = $this->actingAs($user)->get(route('internet-customer.monitor.restart', $customer->id));

        $response->assertStatus(405);
    }

    /** @test */
    public function test_restart_disconnects_pppoe_session(): void
    {
        [$user, $customer] = $this->setupAuthorizedFixtures();

        $this->mock(RouterOSService::class, function ($mock) {
            $mock->shouldReceive('disconnectPppoe')->once()->andReturn(true);
        });

        $response = $this->actingAs($user)->postJson(route('internet-customer.monitor.restart', $customer->id));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.message', 'Sesi PPPoE berhasil diputus, perangkat akan reconnect otomatis.');
    }

    /** @test */
    public function test_monitor_handles_router_offline_gracefully(): void
    {
        [$user, $customer] = $this->setupAuthorizedFixtures();

        $this->mock(RouterOSService::class, function ($mock) {
            $mock->shouldReceive('getPppoeStatus')->once()->andThrow(new ConnectException('Unable to connect to router'));
        });

        $response = $this->actingAs($user)->getJson(route('internet-customer.monitor.status', $customer->id));

        $response->assertStatus(503);
        $response->assertJson([
            'success' => false,
            'offline' => true,
        ]);
    }

    /** @test */
    public function test_monitor_returns_403_if_customer_has_no_router(): void
    {
        $role = $this->makeRole();
        $company = $this->makeCompany();
        $user = $this->makeUser($role, $company);
        $customer = $this->makeCustomer($company, null);

        $response = $this->actingAs($user)->getJson(route('internet-customer.monitor.status', $customer->id));

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }
}
