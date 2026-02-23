@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Dashboard</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['total_revenue_today'], 0, ',', '.') }}</h3>
                    <p>Revenue Hari Ini</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['total_revenue_month'], 0, ',', '.') }}</h3>
                    <p>Revenue Bulan Ini</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['total_active_subscriptions'] }}</h3>
                    <p>Subscription Aktif</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['total_customers'] }}</h3>
                    <p>Total Customers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-friends"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Revenue Chart --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-1"></i>
                        Revenue Trend (30 Hari Terakhir)
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Top Software --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy mr-1"></i>
                        Top Software
                    </h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($topSoftware as $software)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{{ $software->nama }} - {{ $software->tipe_paket }}</span>
                                <span class="badge badge-primary badge-pill">{{ $software->subscriptions_count }}</span>
                            </div>
                        </li>
                        @empty
                        <li class="list-group-item text-center text-muted">
                            Belum ada data
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Subscription Trend --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-area mr-1"></i>
                        Subscription Trend (6 Bulan)
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="subscriptionTrendChart" style="height: 250px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Slot Usage --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-database mr-1"></i>
                        Slot Usage Overview
                    </h3>
                </div>
                <div class="card-body">
                    @forelse($slotUsage as $slot)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">{{ $slot['software'] }}</span>
                            <span class="small">{{ $slot['used_slots'] }}/{{ $slot['total_slots'] }} ({{ $slot['usage_percentage'] }}%)</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar {{ $slot['usage_percentage'] > 80 ? 'bg-danger' : ($slot['usage_percentage'] > 60 ? 'bg-warning' : 'bg-success') }}" 
                                 role="progressbar" 
                                 style="width: {{ $slot['usage_percentage'] }}%">
                                {{ $slot['available_slots'] }} tersedia
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-muted">Belum ada data slot</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Recent Payments --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-credit-card mr-1"></i>
                        Pembayaran Terbaru
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Software</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $payment)
                                <tr>
                                    <td>{{ $payment->subscription->user->name }}</td>
                                    <td>{{ $payment->subscription->masterAccount->software->nama }}</td>
                                    <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                    <td>{{ $payment->paid_at->format('d M Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada pembayaran</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Expiring Soon --}}
        <div class="col-md-5">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Akan Expired (7 Hari)
                    </h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($expiringSoon as $subscription)
                        <li class="list-group-item">
                            <small class="text-muted">{{ $subscription->user->name }}</small>
                            <br>
                            <strong>{{ $subscription->masterAccount->software->nama }}</strong>
                            <br>
                            <small class="text-danger">
                                <i class="far fa-clock"></i> 
                                Expired: {{ carbon\carbon::parse($subscription->tanggal_expired)->format('d M Y') }}
                                ({{ $subscription->days_until_expiry }} hari lagi)
                            </small>
                        </li>
                        @empty
                        <li class="list-group-item text-center text-muted">
                            Tidak ada subscription yang akan expired
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: @json($revenueChart['labels']),
            datasets: [{
                label: 'Revenue (Rp)',
                data: @json($revenueChart['values']),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });

    // Subscription Trend Chart
    const trendCtx = document.getElementById('subscriptionTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'bar',
        data: {
            labels: @json($subscriptionTrendChart['labels']),
            datasets: [{
                label: 'Subscriptions',
                data: @json($subscriptionTrendChart['values']),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@stop
