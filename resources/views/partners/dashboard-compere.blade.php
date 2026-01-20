@extends('adminlte::page')

@section('title', 'Perbandingan Tahun - ' . $partner->name)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-balance-scale"></i> Perbandingan Tahun: {{ $partner->name }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('partner.index') }}">Mitra</a></li>
                <li class="breadcrumb-item"><a href="{{ route('partner.show', $partner) }}">{{ $partner->name }}</a></li>
                <li class="breadcrumb-item active">Perbandingan</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Year Selection -->
    <div class="card">
        <div class="card-body">
            <form method="GET">
                <input type="hidden" name="mode" value="compare">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tahun Pertama</label>
                            <select name="year" class="form-control">
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ $year1 == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Bandingkan dengan Tahun</label>
                            <select name="compare_year" class="form-control">
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ $year2 == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-sync"></i> Bandingkan
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <a href="{{ route('partner.dashboard', $partner) }}" class="btn btn-default btn-block">
                                <i class="fas fa-chart-line"></i> Tampilan Tunggal
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(!$target1 || !$target2)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Satu atau kedua tahun tidak memiliki target. Silakan pilih tahun yang memiliki target.
        </div>
    @else
        <!-- Comparison Summary -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar"></i> Perbandingan Kinerja: {{ $year1 }} vs {{ $year2 }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Parameter</th>
                                        <th class="text-center" colspan="2">{{ $year1 }}</th>
                                        <th class="text-center" colspan="2">{{ $year2 }}</th>
                                        <th class="text-center" colspan="2">Growth</th>
                                    </tr>
                                    <tr class="bg-gray-light">
                                        <th></th>
                                        <th class="text-right">Target</th>
                                        <th class="text-right">Pencapaian</th>
                                        <th class="text-right">Target</th>
                                        <th class="text-right">Pencapaian</th>
                                        <th class="text-right">Target %</th>
                                        <th class="text-right">Pencapaian %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($comparisonData as $paramName => $data)
                                        <tr>
                                            <td><strong>{{ $paramName }}</strong></td>
                                            
                                            <!-- Year 1 Data -->
                                            <td class="text-right">
                                                {{ number_format($data['year1']['target'], 0) }} {{ $data['unit'] }}
                                            </td>
                                            <td class="text-right">
                                                {{ number_format($data['year1']['achievement'], 0) }} {{ $data['unit'] }}
                                                <br>
                                                <small class="badge {{ $data['year1']['percentage'] >= 100 ? 'badge-success' : 'badge-info' }}">
                                                    {{ number_format($data['year1']['percentage'], 1) }}%
                                                </small>
                                            </td>
                                            
                                            <!-- Year 2 Data -->
                                            <td class="text-right">
                                                {{ number_format($data['year2']['target'], 0) }} {{ $data['unit'] }}
                                            </td>
                                            <td class="text-right">
                                                {{ number_format($data['year2']['achievement'], 0) }} {{ $data['unit'] }}
                                                <br>
                                                <small class="badge {{ $data['year2']['percentage'] >= 100 ? 'badge-success' : 'badge-info' }}">
                                                    {{ number_format($data['year2']['percentage'], 1) }}%
                                                </small>
                                            </td>
                                            
                                            <!-- Pertumbuhan -->
                                            <td class="text-right">
                                                @if($data['growth']['target'] > 0)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-arrow-up"></i> {{ number_format($data['growth']['target'], 1) }}%
                                                    </span>
                                                @elseif($data['growth']['target'] < 0)
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-arrow-down"></i> {{ number_format(abs($data['growth']['target']), 1) }}%
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">0%</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($data['growth']['achievement'] > 0)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-arrow-up"></i> {{ number_format($data['growth']['achievement'], 1) }}%
                                                    </span>
                                                @elseif($data['growth']['achievement'] < 0)
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-arrow-down"></i> {{ number_format(abs($data['growth']['achievement']), 1) }}%
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">0%</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Side-by-Side Charts -->
        @foreach($comparisonData as $paramName => $data)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Perbandingan {{ $paramName }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Year 1 Chart -->
                        <div class="col-md-6">
                            <h5 class="text-center">{{ $year1 }}</h5>
                            <div style="height: 300px;">
                                <canvas id="chart-{{ Str::slug($paramName) }}-year1"></canvas>
                            </div>
                            <div class="text-center mt-2">
                                <span class="badge badge-info">
                                    Target: {{ number_format($data['year1']['target'], 0) }} {{ $data['unit'] }}
                                </span>
                                <span class="badge {{ $data['year1']['percentage'] >= 100 ? 'badge-success' : 'badge-warning' }}">
                                    Pencapaian: {{ number_format($data['year1']['percentage'], 1) }}%
                                </span>
                            </div>
                        </div>

                        <!-- Year 2 Chart -->
                        <div class="col-md-6">
                            <h5 class="text-center">{{ $year2 }}</h5>
                            <div style="height: 300px;">
                                <canvas id="chart-{{ Str::slug($paramName) }}-year2"></canvas>
                            </div>
                            <div class="text-center mt-2">
                                <span class="badge badge-info">
                                    Target: {{ number_format($data['year2']['target'], 0) }} {{ $data['unit'] }}
                                </span>
                                <span class="badge {{ $data['year2']['percentage'] >= 100 ? 'badge-success' : 'badge-warning' }}">
                                    Pencapaian: {{ number_format($data['year2']['percentage'], 1) }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Growth Summary -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-{{ $data['growth']['achievement'] >= 0 ? 'success' : 'warning' }}">
                                <strong>Analisis Pertumbuhan:</strong>
                                @if($data['growth']['achievement'] > 0)
                                    <i class="fas fa-arrow-up"></i> Pencapaian meningkat {{ number_format($data['growth']['achievement'], 1) }}% dari {{ $year1 }} ke {{ $year2 }}
                                @elseif($data['growth']['achievement'] < 0)
                                    <i class="fas fa-arrow-down"></i> Pencapaian menurun {{ number_format(abs($data['growth']['achievement']), 1) }}% dari {{ $year1 }} ke {{ $year2 }}
                                @else
                                    Pencapaian tetap sama antara {{ $year1 }} dan {{ $year2 }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
@if($target1 && $target2 && $chartData1 && $chartData2)
    const chartData1 = @json($chartData1);
    const chartData2 = @json($chartData2);

    function createComparisonChart(canvasId, chartData, year, paramName) {
        const data = chartData.parameters[paramName];
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Bulanan',
                        data: data.monthly,
                        backgroundColor: 'rgba(13, 110, 253, 0.8)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Kumulatif',
                        data: data.cumulative,
                        type: 'line',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Target',
                        data: Array(12).fill(data.target),
                        type: 'line',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                label += new Intl.NumberFormat().format(context.parsed.y);
                                label += ' ' + data.unit;
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('en', {notation: 'compact'}).format(value);
                            }
                        }
                    }
                }
            }
        });
    }

    // Create charts for each parameter
    Object.keys(chartData1.parameters).forEach(paramName => {
        const slug = paramName.toLowerCase().replace(/\s+/g, '-');
        createComparisonChart('chart-' + slug + '-year1', chartData1, {{ $year1 }}, paramName);
        
        if (chartData2.parameters[paramName]) {
            createComparisonChart('chart-' + slug + '-year2', chartData2, {{ $year2 }}, paramName);
        }
    });
@endif
</script>
@stop