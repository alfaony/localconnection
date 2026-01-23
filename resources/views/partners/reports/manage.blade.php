@extends('adminlte::page')

@section('title', 'Kelola Laporan Bulanan')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-calendar-alt"></i> Kelola Laporan Bulanan</h1>
            <p class="text-muted">{{ $partner->name }} - Tahun {{ $target->year }}</p>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('partner.index') }}">Mitra</a></li>
                <li class="breadcrumb-item"><a href="{{ route('partner.show', $partner) }}">{{ $partner->name }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('partner.dashboard', ['partner' => $partner, 'year' => $target->year]) }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Kelola Laporan</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Status Laporan Bulanan - {{ $target->year }}</h3>
            <div class="card-tools">
                <a href="{{ route('partner.dashboard', ['partner' => $partner, 'year' => $target->year]) }}" class="btn btn-default btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th width="15%">Bulan</th>
                            @foreach($target->targetValues as $targetValue)
                                <th class="text-center">
                                    {{ $targetValue->parameterType->name }}
                                    <br>
                                    <small class="text-muted">Target: {{ number_format($targetValue->target_value, 0) }}</small>
                                </th>
                            @endforeach
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(config('partners.months') as $monthNum => $monthName)
                            <tr>
                                <td>
                                    <strong>{{ $monthName }}</strong>
                                </td>
                                
                                @php
                                    $hasReport = false;
                                    $reportCount = 0;
                                @endphp
                                
                                @foreach($target->targetValues as $targetValue)
                                    @php
                                        $report = $targetValue->getMonthlyReport($monthNum, $target->year);
                                        if ($report) {
                                            $hasReport = true;
                                            $reportCount++;
                                        }
                                        $percentage = $report && $targetValue->target_value > 0 
                                            ? ($report->achievement_value / $targetValue->target_value) * 100 
                                            : 0;
                                    @endphp
                                    <td class="text-center">
                                        @if($report)
                                            <div>
                                                <strong>{{ number_format($report->achievement_value, 0) }}</strong>
                                                <br>
                                                <span class="badge {{ $percentage >= 100 ? 'badge-success' : 'badge-primary' }}">
                                                    {{ number_format($percentage, 1) }}%
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                
                                <td class="text-center">
                                    @if($hasReport)
                                        <a href="{{ route('partner.reports.edit', ['partner' => $partner, 'target' => $target, 'month' => $monthNum]) }}" 
                                           class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Ubah
                                        </a>
                                    @else
                                        <a href="{{ route('partner.reports.create', ['partner' => $partner, 'target' => $target]) }}?month={{ $monthNum }}" 
                                           class="btn btn-success btn-sm">
                                            <i class="fas fa-plus"></i> Tambah
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Bulan Dilaporkan</span>
                            <span class="info-box-number">
                                {{ $reportedMonths = $target->targetValues->first() ? $target->targetValues->first()->monthlyReports()->distinct('month')->count() : 0 }}
                                / 12
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Parameter</span>
                            <span class="info-box-number">{{ $target->targetValues->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Laporan Belum Ada</span>
                            <span class="info-box-number">{{ 12 - $reportedMonths }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-bolt"></i> Aksi Cepat</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Perlu menambahkan laporan untuk beberapa bulan?</p>
                    <a href="{{ route('partner.reports.create', ['partner' => $partner, 'target' => $target]) }}" 
                       class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Tambah Laporan Bulanan Baru
                    </a>
                    <a href="{{ route('partner.dashboard', ['partner' => $partner, 'year' => $target->year]) }}" 
                       class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> Lihat Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .table td {
        vertical-align: middle;
    }
    .info-box {
        min-height: 90px;
    }
</style>
@stop