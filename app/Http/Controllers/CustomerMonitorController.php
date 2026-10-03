<?php

namespace App\Http\Controllers;

use App\Models\InternetCustomer;
use App\Services\RouterOSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RouterOS\Exceptions\ConnectException;

/**
 * Customer Monitoring Portal — live PPPoE connection status for a single
 * InternetCustomer (status, traffic, latency, restart), backed by
 * RouterOSService. PPPoE only for now.
 */
class CustomerMonitorController extends Controller
{
    private const PING_TARGETS = [
        ['name' => 'Google', 'host' => 'google.com'],
        ['name' => 'Facebook', 'host' => 'facebook.com'],
        ['name' => 'YouTube', 'host' => 'youtube.com'],
        ['name' => 'WhatsApp', 'host' => 'whatsapp.com'],
        ['name' => 'Instagram', 'host' => 'instagram.com'],
    ];

    public function __construct(private RouterOSService $routerOS)
    {
    }

    /**
     * Show the monitoring page for a customer.
     */
    public function index(string $customerId): View|RedirectResponse
    {
        $customer = $this->findCustomer($customerId);

        if (!$customer->router_id || !$customer->router) {
            return redirect()
                ->route('internet-customer.show', $customer->id)
                ->with('error', 'Pelanggan belum terpasang di router manapun, monitoring tidak tersedia.');
        }

        return view('internet-customer.monitor.index', compact('customer'));
    }

    /**
     * Server ping + PPPoE active status. Polled every 5s from the page.
     */
    public function status(string $customerId): JsonResponse
    {
        $customer = $this->findCustomer($customerId);
        if ($guard = $this->guardHasRouter($customer)) {
            return $guard;
        }

        try {
            $pppoe = $this->routerOS->getPppoeStatus($customer->router, $customer->username);
            $ping  = $this->routerOS->pingRouter($customer->router);

            Log::info('[CustomerMonitor] status checked', [
                'customer_id' => $customer->id,
                'user_id'     => Auth::id(),
            ]);

            return $this->success([
                'server_status' => $ping['status'],
                'pppoe_status'  => $pppoe['connected'] ? 'connected' : 'disconnected',
                'ip_address'    => $pppoe['ip'],
                'uptime'        => $pppoe['uptime'],
                'caller_id'     => $pppoe['caller_id'],
                'latency_ms'    => $ping['latency_ms'],
            ]);
        } catch (ConnectException $e) {
            return $this->routerUnreachable($customer, $e);
        } catch (\Throwable $e) {
            return $this->unexpectedError($customer, $e);
        }
    }

    /**
     * Realtime PPPoE upload/download rate + byte counters.
     */
    public function traffic(string $customerId): JsonResponse
    {
        $customer = $this->findCustomer($customerId);
        if ($guard = $this->guardHasRouter($customer)) {
            return $guard;
        }

        try {
            $traffic = $this->routerOS->getPppoeTraffic($customer->router, $customer->username);

            return $this->success($traffic);
        } catch (ConnectException $e) {
            return $this->routerUnreachable($customer, $e);
        } catch (\Throwable $e) {
            return $this->unexpectedError($customer, $e);
        }
    }

    /**
     * Latency from the router to well-known internet hosts.
     */
    public function latency(string $customerId): JsonResponse
    {
        $customer = $this->findCustomer($customerId);
        if ($guard = $this->guardHasRouter($customer)) {
            return $guard;
        }

        try {
            $hosts      = array_column(self::PING_TARGETS, 'host');
            $pingByHost = collect($this->routerOS->pingHosts($customer->router, $hosts))->keyBy('host');

            $targets = array_map(function (array $target) use ($pingByHost) {
                $ping = $pingByHost->get($target['host']);

                return [
                    'name'       => $target['name'],
                    'host'       => $target['host'],
                    'latency_ms' => $ping['avg_rtt'] ?? null,
                    'status'     => ($ping && $ping['avg_rtt'] !== null) ? 'online' : 'offline',
                ];
            }, self::PING_TARGETS);

            return $this->success(['targets' => $targets]);
        } catch (ConnectException $e) {
            return $this->routerUnreachable($customer, $e);
        } catch (\Throwable $e) {
            return $this->unexpectedError($customer, $e);
        }
    }

    /**
     * Disconnect the customer's active PPPoE session so it reconnects fresh.
     */
    public function restart(string $customerId): JsonResponse
    {
        $customer = $this->findCustomer($customerId);
        if ($guard = $this->guardHasRouter($customer)) {
            return $guard;
        }

        try {
            $disconnected = $this->routerOS->disconnectPppoe($customer->router, $customer->username);

            Log::info('[CustomerMonitor] PPPoE session restarted', [
                'customer_id' => $customer->id,
                'username'    => $customer->username,
                'by_user'     => Auth::id(),
            ]);

            return $this->success([
                'message' => $disconnected
                    ? 'Sesi PPPoE berhasil diputus, perangkat akan reconnect otomatis.'
                    : 'Tidak ada sesi PPPoE aktif untuk diputus.',
            ]);
        } catch (ConnectException $e) {
            return $this->routerUnreachable($customer, $e);
        } catch (\Throwable $e) {
            return $this->unexpectedError($customer, $e);
        }
    }

    private function findCustomer(string $customerId): InternetCustomer
    {
        return InternetCustomer::with('router')
            ->byCompany(Auth::user()->company_id)
            ->findOrFail($customerId);
    }

    private function guardHasRouter(InternetCustomer $customer): ?JsonResponse
    {
        if (!$customer->router_id || !$customer->router) {
            return response()->json([
                'success'   => false,
                'error'     => 'Pelanggan tidak memiliki router.',
                'timestamp' => now()->toIso8601String(),
            ], 403);
        }

        return null;
    }

    private function success(array $data): JsonResponse
    {
        return response()->json([
            'success'   => true,
            'data'      => $data,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    private function routerUnreachable(InternetCustomer $customer, \Throwable $e): JsonResponse
    {
        Log::error('[CustomerMonitor] Router unreachable', [
            'customer_id' => $customer->id,
            'error'       => $e->getMessage(),
        ]);

        return response()->json([
            'success'   => false,
            'error'     => 'Router tidak dapat dihubungi',
            'offline'   => true,
            'timestamp' => now()->toIso8601String(),
        ], 503);
    }

    private function unexpectedError(InternetCustomer $customer, \Throwable $e): JsonResponse
    {
        $isTimeout = str_contains(strtolower($e->getMessage()), 'timeout')
            || str_contains(strtolower($e->getMessage()), 'timed out');

        Log::error('[CustomerMonitor] Error', [
            'customer_id' => $customer->id,
            'error'       => $e->getMessage(),
        ]);

        return response()->json([
            'success'   => false,
            'error'     => $isTimeout ? 'Koneksi timeout' : 'Terjadi kesalahan',
            'timeout'   => $isTimeout,
            'timestamp' => now()->toIso8601String(),
        ], $isTimeout ? 408 : 500);
    }
}
