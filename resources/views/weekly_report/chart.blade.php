@extends('adminlte::page')

@section('title', 'Chart Mingguan')

@section('content_header')
<h1 class="m-0 text-dark">📊 Grafik Laporan Mingguan</h1>
@stop

@canAccess('index','dashboard_weekly_reports')
@canAccess('data','dashboard_weekly_reports')
@section('content')
<form id="filter-form" class="form-inline mb-3">
    <select id="division-select" name="division_id" class="form-control mr-2" required>
        @foreach($divisions as $division)
            <option value="{{ $division->id }}">{{ $division->name }}</option>
        @endforeach
    </select>

    <?php
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y');
        $endOfMonth = \Carbon\Carbon::now()->endOfMonth()->format('d-m-Y');
    ?>
    <input type="text" class="form-control" placeholder="Tanggal" id="date_range" value="{{ request('start_date') && request('end_date') ? request('start_date').' - '.request('end_date') : $startOfMonth.' - '.$endOfMonth }}">
    <input type="hidden" id="start_date" name="start_date" value="{{ request('start_date') ?? $startOfMonth }}">
    <input type="hidden" id="end_date" name="end_date" value="{{ request('end_date') ?? $endOfMonth }}">

    <button type="submit" class="btn btn-primary ml-2">
        <i class="fas fa-sync-alt mr-1"></i>Terapkan Filter
    </button>
</form>

<div id="chart-loading" class="text-center my-4">
    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i><br>
    <small>Memuat grafik...</small>
</div>

<div id="charts-container" style="display:none">
    <div class="row">
        @foreach([
            'number_of_customers' => 'Pelanggan Aktif',
            'number_of_users' => 'User Sistem',
            'number_of_products' => 'Produk',
            'number_of_projects' => 'Proyek',
            'number_of_homepasses' => 'Homepass',
            'number_of_leads' => 'Leads',
            'number_of_views' => 'Views',
            'number_of_profit' => 'Profit'
        ] as $id => $label)
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <strong>{{ $label }}</strong>
                </div>
                <div class="card-body">
                    <canvas id="{{ $id }}-chart" height="150"></canvas>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5><i class="fas fa-list mr-2"></i>Detail Laporan Mingguan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Minggu</th>
                        <th>Aktivitas Kunci</th>
                        <th>Permasalahan</th>
                        <th>Target</th>
                    </tr>
                </thead>
                <tbody id="report-detail-body"></tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function () 
    {
        // Initialize Daterangepicker
        $('#date_range').daterangepicker({
            autoUpdateInput: false, // Prevents the input from being automatically populated
            locale: {
                format: 'DD-MM-YYYY',
                cancelLabel: 'Clear' // Adds a clear button to the picker
            }
        });

        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
        });

        $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Capture the date range selection
        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $('#start_date').val(picker.startDate.format('DD-MM-YYYY'));
            $('#end_date').val(picker.endDate.format('DD-MM-YYYY'));
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let charts = {};

    function renderCharts(data) 
    {
        const labels = data.labels;

        for (const key in data.datasets) {
            const ctx = document.getElementById(`${key}-chart`).getContext('2d');
            if (charts[key]) charts[key].destroy();

            charts[key] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: key.replace(/_/g, ' ').toUpperCase(),
                        data: data.datasets[key],
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    }
    
    function renderDetailTable(details) 
    {
        const tbody = $('#report-detail-body');
        tbody.empty();

        details.forEach(row => {
            tbody.append(`
                <tr>
                    <td>${row.week_label}</td>
                    <td>${row.key_activities ?? '-'}</td>
                    <td>${row.problems ?? '-'}</td>
                    <td>${row.targets ?? '-'}</td>
                </tr>
            `);
        });
    }

    function fetchChartData() {
        $('#chart-loading').show();
        $('#charts-container').hide();

        const params = {
            division_id: $('#division-select').val(),
            date_start: $('#date_start').val(),
            date_end: $('#date_end').val(),
        };

        $.get("{{ route('dasboard.weekly-report.fetch') }}", params, function (res) {
            renderCharts(res);
            renderDetailTable(res.details);
            $('#chart-loading').hide();
            $('#charts-container').fadeIn();
        }).fail(() => {
            $('#chart-loading').html('<div class="text-danger">Gagal memuat data chart.</div>');
        });
    }

    $(document).ready(function () {
        fetchChartData();

        $('#filter-form').on('submit', function (e) {
            e.preventDefault();
            fetchChartData();
        });
    });
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@stop

@endcanAccess
@endcanAccess