@extends('adminlte::page')

@section('title', 'Dashboard - ' . $partner->name)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-chart-line"></i> Dashboard: {{ $partner->name }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('partner.index') }}">Partners</a></li>
                <li class="breadcrumb-item"><a href="{{ route('partner.show', $partner) }}">{{ $partner->name }}</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Mode Toggle & Year Selection -->
    <div class="card">
        <div class="card-body">
            <form method="GET" id="dashboardForm">
                <div class="row">
                    <!-- Mode Selection -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>View Mode</label>
                            <select name="mode" class="form-control" id="modeSelect" onchange="toggleCompareMode()">
                                <option value="single" {{ request('mode', 'single') == 'single' ? 'selected' : '' }}>
                                    Single Year
                                </option>
                                <option value="compare" {{ request('mode') == 'compare' ? 'selected' : '' }}>
                                    Compare Years
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Primary Year -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label id="yearLabel">Year</label>
                            <select name="year" class="form-control">
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Compare Year (Hidden by default) -->
                    <div class="col-md-3" id="compareYearDiv" style="display: {{ request('mode') == 'compare' ? 'block' : 'none' }};">
                        <div class="form-group">
                            <label>Compare with Year</label>
                            <select name="compare_year" class="form-control">
                                <option value="">-- Select Year --</option>
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ request('compare_year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-sync"></i> Update View
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(!$target)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            No target found for year {{ $selectedYear }}. 
            <a href="{{ route('partner.targets.create', $partner) }}" class="alert-link">Create target first</a>.
        </div>
    @else
        <!-- Summary Cards -->
        <div class="row">
            @foreach($chartData['parameters'] as $paramName => $data)
                <div class="col-md-4 col-md-6">
                    <div class="small-box {{ $data['achievement_percentage'] >= 100 ? 'bg-success' : 'bg-info' }}">
                        <div class="inner">
                            <h3>{{ number_format($data['total_achievement'], 0) }}</h3>
                            <p>{{ $paramName }}</p>
                            <p class="mb-0">
                                <small>
                                    Target: {{ number_format($data['target'], 0) }} {{ $data['unit'] }}
                                </small>
                            </p>
                        </div>
                        <div class="icon">
                            <i class="fas {{ $data['achievement_percentage'] >= 100 ? 'fa-check-circle' : 'fa-chart-bar' }}"></i>
                        </div>
                        <div class="small-box-footer">
                            {{ number_format($data['achievement_percentage'], 1) }}% achieved
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Charts -->
        @foreach($chartData['parameters'] as $paramName => $data)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> {{ $paramName }} - {{ $selectedYear }}
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">Target: {{ number_format($data['target'], 0) }} {{ $data['unit'] }}</span>
                        <span class="badge {{ $data['achievement_percentage'] >= 100 ? 'badge-success' : 'badge-warning' }}">
                            Achievement: {{ number_format($data['achievement_percentage'], 1) }}%
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 400px;">
                        <canvas id="chart-{{ Str::slug($paramName) }}"></canvas>
                    </div>
                    
                    <!-- Monthly Data Table -->
                    <div class="table-responsive mt-4">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th class="text-right">Monthly</th>
                                    <th class="text-right">Cumulative</th>
                                    <th class="text-right">% of Target</th>
                                    <th width="150">Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chartData['labels'] as $index => $month)
                                    <tr>
                                        <td><strong>{{ $month }}</strong></td>
                                        <td class="text-right">{{ number_format($data['monthly'][$index], 0) }} {{ $data['unit'] }}</td>
                                        <td class="text-right">{{ number_format($data['cumulative'][$index], 0) }} {{ $data['unit'] }}</td>
                                        <td class="text-right">
                                            @php
                                                $percentage = $data['target'] > 0 ? ($data['cumulative'][$index] / $data['target']) * 100 : 0;
                                            @endphp
                                            {{ number_format($percentage, 1) }}%
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar {{ $percentage >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                                     style="width: {{ min($percentage, 100) }}%">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <th>Total</th>
                                    <th class="text-right">{{ number_format($data['total_achievement'], 0) }} {{ $data['unit'] }}</th>
                                    <th class="text-right">-</th>
                                    <th class="text-right">{{ number_format($data['achievement_percentage'], 1) }}%</th>
                                    <th>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $data['achievement_percentage'] >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                                 style="width: {{ min($data['achievement_percentage'], 100) }}%">
                                                {{ number_format($data['achievement_percentage'], 1) }}%
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body text-center">
                <a href="{{ route('partner.reports.manage', ['partner' => $partner, 'target' => $target]) }}" 
                   class="btn btn-success btn-md">
                    <i class="fas fa-calendar-check"></i> Manage All Reports
                </a>
                <a href="{{ route('partner.reports.create', ['partner' => $partner, 'target' => $target]) }}" 
                   class="btn btn-primary btn-md">
                    <i class="fas fa-plus-circle"></i> Add Monthly Report
                </a>
                <a href="{{ route('partner.targets.edit', ['partner' => $partner, 'target' => $target]) }}" 
                   class="btn btn-warning btn-md">
                    <i class="fas fa-edit"></i> Edit Target
                </a>
                <a href="{{ route('partner.show', $partner) }}" 
                   class="btn btn-default btn-md">
                    <i class="fas fa-arrow-left"></i> Back to Partner
                </a>
            </div>
        </div>
    @endif
@stop

@section('css')
<style>
    .small-box {
        border-radius: 5px;
    }
    .progress {
        background-color: #e9ecef;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
function toggleCompareMode() {
    const mode = document.getElementById('modeSelect').value;
    const compareDiv = document.getElementById('compareYearDiv');
    const yearLabel = document.getElementById('yearLabel');
    
    if (mode === 'compare') {
        compareDiv.style.display = 'block';
        yearLabel.textContent = 'First Year';
    } else {
        compareDiv.style.display = 'none';
        yearLabel.textContent = 'Year';
    }
}

@if($target && $chartData)
    const chartData = @json($chartData);
    const colors = [
        'rgba(13, 110, 253, 0.8)',
        'rgba(25, 135, 84, 0.8)',
        'rgba(220, 53, 69, 0.8)',
        'rgba(255, 193, 7, 0.8)',
        'rgba(13, 202, 240, 0.8)',
        'rgba(111, 66, 193, 0.8)',
    ];

    let colorIndex = 0;

    Object.keys(chartData.parameters).forEach((paramName, index) => {
        const data = chartData.parameters[paramName];
        const color = colors[colorIndex % colors.length];
        colorIndex++;

        const ctx = document.getElementById('chart-' + paramName.toLowerCase().replace(/\s+/g, '-')).getContext('2d');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Monthly Achievement',
                        data: data.monthly,
                        backgroundColor: color,
                        borderColor: color.replace('0.8', '1'),
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Cumulative Achievement',
                        data: data.cumulative,
                        type: 'line',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Target',
                        data: Array(12).fill(data.target),
                        type: 'line',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += new Intl.NumberFormat().format(context.parsed.y);
                                label += ' ' + data.unit;
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat().format(value);
                            }
                        }
                    }
                }
            }
        });
    });
@endif
</script>
@stop