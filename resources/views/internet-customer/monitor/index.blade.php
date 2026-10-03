@extends('adminlte::page')

@section('title', 'Monitor Koneksi - ' . $customer->name)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Monitor Koneksi</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('internet-customer.show', $customer->id) }}">{{ $customer->name }}</a></li>
                <li class="breadcrumb-item active">Monitor</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div id="monitor-app"
     data-status-url="{{ route('internet-customer.monitor.status', $customer->id) }}"
     data-traffic-url="{{ route('internet-customer.monitor.traffic', $customer->id) }}"
     data-latency-url="{{ route('internet-customer.monitor.latency', $customer->id) }}"
     data-restart-url="{{ route('internet-customer.monitor.restart', $customer->id) }}">

    <div class="row mb-2">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">{{ $customer->name }} <small class="text-muted">({{ $customer->code }})</small></h4>
                        <span class="text-muted">Username PPPoE: {{ $customer->username ?: '-' }}</span>
                    </div>
                    <div class="mt-2 mt-sm-0">
                        <span id="global-status-badge" class="badge badge-secondary p-2">Memuat...</span>
                        <button type="button" id="btn-restart" class="btn btn-danger ml-2">
                            <i class="fas fa-power-off mr-1"></i> Restart Koneksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="offline-alert" class="alert alert-warning d-none" role="alert">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        Router tidak dapat dihubungi. Data di bawah mungkin tidak terbaru.
    </div>

    <div class="row">
        {{-- Status Card --}}
        <div class="col-md-6 col-lg-3">
            <div class="card card-outline card-primary h-100">
                <div class="card-header"><h3 class="card-title">Status PPPoE</h3></div>
                <div class="card-body" id="card-status">
                    <div class="skeleton-line w-75"></div>
                    <div class="skeleton-line w-50"></div>
                    <div class="skeleton-line w-100"></div>
                </div>
            </div>
        </div>

        {{-- Connection Info Card --}}
        <div class="col-md-6 col-lg-3">
            <div class="card card-outline card-info h-100">
                <div class="card-header"><h3 class="card-title">Informasi Koneksi</h3></div>
                <div class="card-body" id="card-connection">
                    <div class="skeleton-line w-75"></div>
                    <div class="skeleton-line w-50"></div>
                    <div class="skeleton-line w-100"></div>
                </div>
            </div>
        </div>

        {{-- Traffic Card --}}
        <div class="col-md-6 col-lg-3">
            <div class="card card-outline card-success h-100">
                <div class="card-header"><h3 class="card-title">Traffic</h3></div>
                <div class="card-body" id="card-traffic">
                    <div class="skeleton-line w-75"></div>
                    <div class="skeleton-line w-50"></div>
                    <div class="skeleton-line w-100"></div>
                </div>
            </div>
        </div>

        {{-- Ping to Router Card --}}
        <div class="col-md-6 col-lg-3">
            <div class="card card-outline card-warning h-100">
                <div class="card-header"><h3 class="card-title">Ping Server</h3></div>
                <div class="card-body" id="card-ping">
                    <div class="skeleton-line w-75"></div>
                    <div class="skeleton-line w-50"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Latency ke Layanan Populer</h3>
                </div>
                <div class="card-body">
                    <div class="row" id="latency-grid">
                        @foreach (['Google', 'Facebook', 'YouTube', 'WhatsApp', 'Instagram'] as $service)
                            <div class="col-6 col-md-4 col-lg mb-3">
                                <div class="text-center p-2 border rounded latency-item" data-service="{{ $service }}">
                                    <div class="font-weight-bold">{{ $service }}</div>
                                    <div class="latency-value text-muted">-- ms</div>
                                    <span class="badge badge-secondary latency-badge">Memuat</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .skeleton-line {
        height: 14px;
        border-radius: 4px;
        background: linear-gradient(90deg, #eee 25%, #f5f5f5 37%, #eee 63%);
        background-size: 400% 100%;
        animation: skeleton-loading 1.4s ease infinite;
        margin-bottom: 8px;
    }
    .skeleton-line.w-75 { width: 75%; }
    .skeleton-line.w-50 { width: 50%; }
    .skeleton-line.w-100 { width: 100%; }
    @keyframes skeleton-loading {
        0% { background-position: 100% 50%; }
        100% { background-position: 0 50%; }
    }
    .latency-item.status-online { border-color: #10B981 !important; }
    .latency-item.status-offline { border-color: #EF4444 !important; }
    .badge-online { background-color: #10B981; color: #fff; }
    .badge-offline { background-color: #EF4444; color: #fff; }
    .badge-degraded { background-color: #F59E0B; color: #fff; }
</style>
@stop

@section('js')
<script>
(function () {
    var app = document.getElementById('monitor-app');
    var statusUrl = app.dataset.statusUrl;
    var trafficUrl = app.dataset.trafficUrl;
    var latencyUrl = app.dataset.latencyUrl;
    var restartUrl = app.dataset.restartUrl;
    var POLL_MS = 5000;
    var pollTimer = null;

    function toast(message, type) {
        type = type || 'info';
        var container = document.getElementById('monitor-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'monitor-toast-container';
            container.style.position = 'fixed';
            container.style.top = '15px';
            container.style.right = '15px';
            container.style.zIndex = 9999;
            document.body.appendChild(container);
        }
        var el = document.createElement('div');
        el.className = 'alert alert-' + type + ' shadow';
        el.style.minWidth = '250px';
        el.textContent = message;
        container.appendChild(el);
        setTimeout(function () { el.remove(); }, 4000);
    }

    function formatBits(bps) {
        bps = Number(bps) || 0;
        if (bps >= 1000000) return (bps / 1000000).toFixed(2) + ' Mbps';
        if (bps >= 1000) return (bps / 1000).toFixed(2) + ' Kbps';
        return bps + ' bps';
    }

    function setOffline(isOffline) {
        var alertEl = document.getElementById('offline-alert');
        alertEl.classList.toggle('d-none', !isOffline);
    }

    function setGlobalBadge(text, cls) {
        var badge = document.getElementById('global-status-badge');
        badge.className = 'badge p-2 ' + cls;
        badge.textContent = text;
    }

    function fetchJson(url) {
        return fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin',
        }).then(function (res) {
            return res.json().then(function (body) {
                return { ok: res.ok, status: res.status, body: body };
            });
        });
    }

    function renderStatus(resp) {
        var el = document.getElementById('card-status');
        var connEl = document.getElementById('card-connection');

        if (!resp.ok || !resp.body.success) {
            setOffline(true);
            setGlobalBadge('Tidak Terhubung', 'badge-offline');
            el.innerHTML = '<p class="text-danger mb-0"><i class="fas fa-times-circle"></i> ' + (resp.body.error || 'Gagal memuat status') + '</p>';
            connEl.innerHTML = '<p class="text-muted mb-0">Data tidak tersedia</p>';
            return;
        }

        setOffline(false);
        var d = resp.body.data;
        var connected = d.pppoe_status === 'connected';

        setGlobalBadge(connected ? 'Terhubung' : 'Terputus', connected ? 'badge-online' : 'badge-offline');

        el.innerHTML =
            '<p class="mb-1"><span class="badge ' + (connected ? 'badge-online' : 'badge-offline') + '">' +
            (connected ? 'Connected' : 'Disconnected') + '</span></p>' +
            '<p class="mb-1">Server: <strong>' + (d.server_status === 'online' ? 'Online' : 'Offline') + '</strong></p>' +
            '<p class="mb-0">Ping Router: <strong>' + (d.latency_ms !== null && d.latency_ms !== undefined ? d.latency_ms + ' ms' : '-') + '</strong></p>';

        connEl.innerHTML =
            '<p class="mb-1">IP Address: <strong>' + (d.ip_address || '-') + '</strong></p>' +
            '<p class="mb-1">Uptime: <strong>' + (d.uptime || '-') + '</strong></p>' +
            '<p class="mb-0">Caller ID: <strong>' + (d.caller_id || '-') + '</strong></p>';
    }

    function renderPing(resp) {
        var el = document.getElementById('card-ping');
        if (!resp.ok || !resp.body.success) {
            el.innerHTML = '<p class="text-danger mb-0">Tidak tersedia</p>';
            return;
        }
        var d = resp.body.data;
        el.innerHTML = '<p class="mb-1">Latency: <strong>' + (d.latency_ms !== null && d.latency_ms !== undefined ? d.latency_ms + ' ms' : '-') + '</strong></p>' +
            '<p class="mb-0">Server: <strong>' + (d.server_status === 'online' ? 'Online' : 'Offline') + '</strong></p>';
    }

    function renderTraffic(resp) {
        var el = document.getElementById('card-traffic');
        if (!resp.ok || !resp.body.success) {
            el.innerHTML = '<p class="text-danger mb-0">' + (resp.body.error || 'Gagal memuat traffic') + '</p>';
            return;
        }
        var d = resp.body.data;
        el.innerHTML =
            '<p class="mb-1"><i class="fas fa-arrow-down text-success"></i> Download: <strong>' + formatBits(d.rx_rate) + '</strong></p>' +
            '<p class="mb-0"><i class="fas fa-arrow-up text-primary"></i> Upload: <strong>' + formatBits(d.tx_rate) + '</strong></p>';
    }

    function renderLatency(resp) {
        if (!resp.ok || !resp.body.success) {
            return;
        }
        var targets = resp.body.data.targets || [];
        targets.forEach(function (t) {
            var item = document.querySelector('.latency-item[data-service="' + t.name + '"]');
            if (!item) return;
            var valueEl = item.querySelector('.latency-value');
            var badgeEl = item.querySelector('.latency-badge');
            var online = t.status === 'online';

            item.classList.toggle('status-online', online);
            item.classList.toggle('status-offline', !online);
            valueEl.textContent = online && t.latency_ms !== null ? t.latency_ms + ' ms' : '--';
            badgeEl.className = 'badge latency-badge ' + (online ? 'badge-online' : 'badge-offline');
            badgeEl.textContent = online ? 'Online' : 'Offline';
        });
    }

    function poll() {
        fetchJson(statusUrl).then(function (r) { renderStatus(r); renderPing(r); }).catch(function () {
            setOffline(true);
        });
        fetchJson(trafficUrl).then(renderTraffic).catch(function () {});
        fetchJson(latencyUrl).then(renderLatency).catch(function () {});
    }

    function startPolling() {
        poll();
        pollTimer = setInterval(poll, POLL_MS);
    }

    document.getElementById('btn-restart').addEventListener('click', function () {
        if (!confirm('Yakin ingin merestart koneksi pelanggan ini? Perangkat akan terputus sementara.')) {
            return;
        }
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';

        fetch(restartUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            credentials: 'same-origin',
        }).then(function (res) {
            return res.json().then(function (body) { return { ok: res.ok, body: body }; });
        }).then(function (resp) {
            if (resp.ok && resp.body.success) {
                toast(resp.body.data.message || 'Koneksi berhasil direstart', 'success');
            } else {
                toast(resp.body.error || 'Gagal merestart koneksi', 'danger');
            }
        }).catch(function () {
            toast('Gagal menghubungi server', 'danger');
        }).finally(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-power-off mr-1"></i> Restart Koneksi';
        });
    });

    startPolling();

    window.addEventListener('beforeunload', function () {
        if (pollTimer) clearInterval(pollTimer);
    });
})();
</script>
@stop
