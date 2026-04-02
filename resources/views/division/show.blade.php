@extends('adminlte::page')

@section('title', 'Task Tracking')

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('division.index') }}">Divisi</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $division->name ?? '' }}</li>
    </ol>
</nav>

{{-- Quota History Section --}}
<div class="card p-3 mt-3">
    <div class="card-header bg-gradient-info text-white">
        <h5 class="mb-0">
            <i class="fas fa-history"></i> Riwayat Quota Periode
        </h5>
    </div>
    <div class="card-body">
        @if($quotaLocks->isEmpty())
            <div class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <p>Belum ada riwayat quota untuk divisi ini</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th width="150">Periode</th>
                            <th width="120" class="text-center">Total Quota</th>
                            <th width="120" class="text-center">Terpakai</th>
                            <th width="120" class="text-center">Tersisa</th>
                            <th width="150" class="text-center">Usage</th>
                            <th>Breakdown</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotaLocks as $lock)
                            @php
                                $monthName = \Carbon\Carbon::create()->month($lock['month'])->translatedFormat('F');
                                $progressColor = 'success';
                                if ($lock['percentage'] > 80) $progressColor = 'danger';
                                elseif ($lock['percentage'] > 60) $progressColor = 'warning';
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $monthName }} {{ $lock['year'] }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary badge-pill">{{ number_format($lock['quota']) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary badge-pill">{{ number_format($lock['used']) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $lock['remaining'] < 0 ? 'danger' : ($lock['remaining'] < $lock['quota'] * 0.2 ? 'warning' : 'success') }} badge-pill">
                                        {{ number_format($lock['remaining']) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-{{ $progressColor }}" 
                                             role="progressbar" 
                                             style="width: {{ min($lock['percentage'], 100) }}%">
                                            {{ $lock['percentage'] }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span><i class="fas fa-tasks text-primary"></i> Task Points:</span>
                                            <strong>{{ number_format($lock['task_used']) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span><i class="fas fa-gift text-success"></i> Direct Points:</span>
                                            <strong>{{ number_format($lock['direct_point_used']) }}</strong>
                                        </div>
                                    </small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@canAccess('ajaxObjectiveChart', 'divisions')
{{-- Objective Daily Task Chart --}}
<div class="card p-3 mt-3">
    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-chart-bar mr-2"></i>Distribusi Daily Task per Objective</h5>
        <small class="text-white-50">berdasarkan objective_division</small>
    </div>
    <div class="card-body">
        {{-- Filter --}}
        <div class="row mb-3 align-items-end">
            <div class="col-md-3">
                <label class="small font-weight-bold">Dari Tanggal</label>
                <input type="date" id="objChartStartDate" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold">Sampai Tanggal</label>
                <input type="date" id="objChartEndDate" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <button id="objChartLoadBtn" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-sync-alt mr-1"></i>Tampilkan
                </button>
            </div>
            <div class="col-md-2">
                <button id="objChartResetBtn" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="fas fa-times mr-1"></i>Semua Waktu
                </button>
            </div>
            <div class="col-md-2 text-right">
                <span id="objChartTotal" class="badge badge-info px-3 py-2" style="font-size:0.85rem;display:none;">
                    Total: <strong id="objChartTotalVal">0</strong> task
                </span>
            </div>
        </div>

        {{-- Loading --}}
        <div id="objChartLoading" class="text-center py-5" style="display:none;">
            <div class="spinner-border text-primary" role="status" style="width:2.5rem;height:2.5rem;">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2 text-muted small">Memuat data chart...</p>
        </div>

        {{-- Empty State --}}
        <div id="objChartEmpty" class="text-center text-muted py-5" style="display:none;">
            <i class="fas fa-chart-bar fa-3x mb-3 text-light"></i>
            <p>Tidak ada data daily task untuk periode ini.</p>
        </div>

        {{-- Chart --}}
        <div id="objChartWrapper" style="display:none;">
            <canvas id="objDivisionChart" style="max-height:350px;"></canvas>
        </div>
    </div>
</div>
@endcanAccess

<div id="accordion">
@canAccess('fetchusertask', 'project_dashboards')
  <div class="card p-3 mt-3">
    <div class="card-header" id="headingOne">
      <h5 class="mb-0">
        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Pantauan Tugas
        </button>
      </h5>
    </div>

    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
        <div class="card-body">
            <div class="accordion" id="taskAccordion">
                <div class="card">
                    <div class="card-header" id="headingOverdue">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOverdue" aria-expanded="true" aria-controls="collapseOverdue">
                                Overdue Tasks
                            </button>
                        </h5>
                    </div>
                    <div id="collapseOverdue" class="collapse show" aria-labelledby="headingOverdue" data-parent="#taskAccordion">
                        <div class="card-body">
                            <canvas id="overdueTasksChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingUpcoming">
                        <h5 class="mb-0">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseUpcoming" aria-expanded="false" aria-controls="collapseUpcoming">
                                Upcoming Tasks
                            </button>
                        </h5>
                    </div>
                    <div id="collapseUpcoming" class="collapse" aria-labelledby="headingUpcoming" data-parent="#taskAccordion">
                        <div class="card-body">
                            <canvas id="upcomingTasksChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5">
                <h3>Tasks : <span id="userName"></span></h3>
                <div id="userTasks"></div>
            </div>
        </div>
    </div>
  </div>
@endcanAccess
@canAccess('ajaxDivisionTasks', 'divisions')
  <div class="card p-3 mt-3">
    <div class="card-header" id="headingTwo">
      <h5 class="mb-0">
        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
            Tracking Poin Tugas
        </button>
      </h5>
    </div>

    <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordion">
        <div class="card-body">
            {{-- FILTER BULAN --}}
            <div class="row mb-3">
                <div class="col-md-3">
                <label>Bulan & Tahun</label>
                <div class="input-group date" id="monthPicker">
                    <input type="text" id="monthInput" class="form-control" readonly>
                    <div class="input-group-append">
                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    </div>
                </div>
                </div>
                <div class="col-md-2 align-self-end">
                <button id="loadBtn" class="btn btn-primary w-100">Tampilkan</button>
                </div>
            </div>

            {{-- RINGKASAN QUOTA --}}
            <div id="division-summary" class="row mb-4">
                <div class="col-md-4">
                <div class="card shadow-sm border-left-primary">
                    <div class="card-body py-2">
                    <p class="mb-1 text-muted">Kuota Bulanan</p>
                    <h5 id="quota" class="mb-0 fw-bold text-primary">-</h5>
                    </div>
                </div>
                </div>
                <div class="col-md-4">
                <div class="card shadow-sm border-left-warning">
                    <div class="card-body py-2">
                    <p class="mb-1 text-muted">Point Terpakai</p>
                    <h5 id="used" class="mb-0 fw-bold text-warning">-</h5>
                    </div>
                </div>
                </div>
                <div class="col-md-4">
                <div class="card shadow-sm border-left-success">
                    <div class="card-body py-2">
                    <p class="mb-1 text-muted">Sisa Point</p>
                    <h5 id="remaining" class="mb-0 fw-bold text-success">-</h5>
                    </div>
                </div>
                </div>
            </div>

            {{-- TASK LIST --}}
            <h5 class="mb-3">📋 Daftar Task</h5>
            <div id="task-container" class="row g-3"></div>
            <div id="no-task-msg" class="text-center text-muted mt-3" style="display:none;">
                <i class="fas fa-info-circle"></i> Tidak ada task untuk periode ini.
            </div>
        </div>
    </div>
  </div>
@endcanAccess
</div>
@endsection
@section('js')
<!-- 1. JQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- 2. Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- 3. Bootstrap Datepicker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>

<!-- 4. Plugin tambahan lainnya -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<!-- 5. Inisialisasi Datepicker -->
@canAccess('ajaxDivisionTasks', 'divisions')
<script>
  $(document).ready(function () {
    $('#monthPicker').datepicker({
      format: "mm-yyyy",
      minViewMode: 1,
      autoclose: true
    }).datepicker('setDate', new Date());
  });
</script>

<script>
  const ajaxTaskUrl = "{{ route('divisions.ajax.tasks', ['division' => $division->id]) }}";

  $(document).ready(function () {
    $('#monthPicker').datepicker({
      format: "mm-yyyy",
      minViewMode: 1,
      autoclose: true
    }).datepicker('setDate', new Date());

    loadDivisionData();

    $('#loadBtn').on('click', loadDivisionData);
  });

  function loadDivisionData() 
  {
    const [month, year] = $('#monthInput').val().split('-');

    $.get(ajaxTaskUrl, { month, year }, function (res) {
        $('#quota').text(res.quota);
        $('#used').text(res.used);
        $('#remaining').text(res.remaining);

        const container = $('#task-container');
        container.empty();

        // Show summary if we have data
        if (res.task_used > 0 || res.direct_point_used > 0) {
            const summaryCard = `
                <div class="col-md-12 mb-3">
                    <div class="card shadow-sm border-left-info">
                        <div class="card-body py-2">
                            <h6 class="mb-2">📊 Breakdown Penggunaan Point</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Task Point:</small>
                                    <h5 class="mb-0 text-primary">${res.task_used}</h5>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Direct Point:</small>
                                    <h5 class="mb-0 text-success">${res.direct_point_used}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.append(summaryCard);
        }

        const allItems = [];

        // Add tasks
        if (res.tasks && res.tasks.length > 0) {
            res.tasks.forEach(t => {
                allItems.push({
                    type: 'task',
                    ...t
                });
            });
        }

        // Add direct points
        if (res.direct_points && res.direct_points.length > 0) {
            res.direct_points.forEach(dp => {
                allItems.push({
                    type: 'direct_point',
                    ...dp
                });
            });
        }

        if (allItems.length === 0) {
            $('#no-task-msg').show();
            return;
        } else {
            $('#no-task-msg').hide();
        }

        allItems.forEach(item => {
            const taskDate = new Date(item.created_at).toLocaleDateString('id-ID');
            const icon = item.type === 'direct_point' ? '💰' : '📋';
            const typeLabel = item.type === 'direct_point' ? 'Direct Point' : 'Task';
            
            const card = `
            <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-left-${item.type === 'direct_point' ? 'success' : 'primary'}">
                        <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-1">${icon} ${item.name}</h5>
                            <span class="badge badge-${item.type === 'direct_point' ? 'success' : 'primary'}">${typeLabel}</span>
                        </div>

                        <p class="card-text mb-1 small text-muted">
                            🗓 <strong>${taskDate}</strong><br>
                            💠 <strong>Point:</strong> ${item.point}<br>
                            👤 <strong>Dibuat oleh:</strong> ${item.user_name ?? '-'}<br>
                            ${item.assign_name ? `🎯 <strong>${item.type === 'direct_point' ? 'Diterima' : 'Ditugaskan'} oleh:</strong> ${item.assign_name}<br>` : ''}
                        </p>

                        ${item.description ? `<p class="mb-0 text-muted small"><em>${item.description}</em></p>` : ''}
                        </div>
                    </div>
                </div>
            `;
            container.append(card);
        });
    });
    }
</script>
@endcanAccess

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Data for Overdue Tasks
        var overdueLabels = @json($overdueTasks->pluck('name'));
        var overdueData = @json($overdueTasks->pluck('daily_task_assigns_count'));
        
        // Data for Upcoming Tasks
        var upcomingLabels = @json($upcomingTasks->pluck('name'));
        var upcomingData = @json($upcomingTasks->pluck('daily_task_assigns_count'));

        // Overdue Tasks Chart
        var ctxOverdue = document.getElementById('overdueTasksChart').getContext('2d');
        var overdueTasksChart = new Chart(ctxOverdue, {
            type: 'bar',
            data: {
                labels: overdueLabels,
                datasets: [{
                    label: 'Overdue Tasks',
                    data: overdueData,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                onClick: function(e, elements) {
                    if (elements.length > 0) {
                        var index = elements[0].index;
                        var userName = overdueLabels[index];
                        var userId = @json($overdueTasks->pluck('id'))[index];
                        console.log('User ID:', userId, 'User Name:', userName);
                        fetchUserTasks(userId, userName, 'overdue');
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });

        // Upcoming Tasks Chart
        var ctxUpcoming = document.getElementById('upcomingTasksChart').getContext('2d');
        var upcomingTasksChart = new Chart(ctxUpcoming, {
            type: 'bar',
            data: {
                labels: upcomingLabels,
                datasets: [{
                    label: 'Upcoming Tasks',
                    data: upcomingData,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                onClick: function(e, elements) {
                    if (elements.length > 0) {
                        var index = elements[0].index;
                        var userName = upcomingLabels[index];
                        var userId = @json($upcomingTasks->pluck('id'))[index];
                        fetchUserTasks(userId, userName,'upcoming');
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });

        function fetchUserTasks(userId, userName, filter = 'overdue') 
        {
            document.getElementById('userName').innerText = userName;
            document.getElementById('userTasks').innerHTML = 'Loading...';
            
            let url = `{{ route('division.fetchusertask', [':userId', ':filter']) }}`;
            url = url.replace(':userId', userId);
            url = url.replace(':filter', filter);

            fetch(url.replace(':userId', userId))
                .then(response => response.json())
                .then(tasks => {
                    let tasksHtml = '<table class="table table-bordered"><thead><tr><th>Tugas</th><th>Status</th><th>Tanggal</th><th>Dibuat</th><th>Action</th></tr></thead><tbody>';
                    tasks.forEach(task => {
                        let statusIcon = '';
                        let url = '';
                        switch (task.task_status.name) {
                            case 'todo':
                                statusIcon = '<i class="fa fa-list-alt"></i>';
                                break;
                            case 'doing':
                                statusIcon = '<i class="fa fa-hourglass-start"></i>';
                                break;
                            case 'in review':
                                statusIcon = '<i class="fa fa-eye" style="color: green;"></i>';
                                break;
                            case 'not complete':
                                statusIcon = '<i class="fa fa-times-circle" style="color: red;"></i>';
                                break;
                            case 'complete':
                                statusIcon = '<i class="fa fa-check" style="color: green;"></i>';
                                break;
                            default:
                                statusIcon = task.task_status.name;
                        }

                        if(task)
                        {
                            url = `<a href="${task.url}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>`;
                        }else
                        {
                            url = '-';
                        }
                        
                        if(task.is_overdue)
                        {
                            date_show = `<span class="text-danger"> ${task.date_show }</span>`;
                        }else
                        {
                            date_show = `<span> ${task.date_show}</span>`;
                        }


                        tasksHtml += `<tr>
                                        <td>${task.name_show}</td>
                                        <td>${statusIcon} ${task.task_status.name}</td>
                                        <td>${date_show}</td>
                                        <td>${task.user_create}</td>
                                        <td>
                                            ${url}
                                        </td>
                                    </tr>`;
                    });
                    tasksHtml += '</tbody></table>';
                    document.getElementById('userTasks').innerHTML = tasksHtml;
                })
                .catch(error => {
                    document.getElementById('userTasks').innerHTML = 'Error fetching tasks.';
                    console.error('Error fetching tasks:', error);
            });
        }
    });
</script>

@canAccess('ajaxObjectiveChart', 'divisions')
<script>
(function () {
    const objChartUrl = "{{ route('divisions.ajax.objective-chart', ['division' => $division->id]) }}";
    let objChart = null;

    $(document).ready(function () {
        loadObjChart();
        $('#objChartLoadBtn').on('click', loadObjChart);
        $('#objChartResetBtn').on('click', function () {
            $('#objChartStartDate').val('');
            $('#objChartEndDate').val('');
            loadObjChart();
        });
    });

    function loadObjChart() {
        const startDate = $('#objChartStartDate').val();
        const endDate   = $('#objChartEndDate').val();

        $('#objChartLoading').show();
        $('#objChartWrapper').hide();
        $('#objChartEmpty').hide();
        $('#objChartTotal').hide();

        $.get(objChartUrl, { start_date: startDate, end_date: endDate }, function (res) {
            $('#objChartLoading').hide();

            if (!res.labels || res.labels.length === 0) {
                $('#objChartEmpty').show();
                return;
            }

            $('#objChartTotal').show();
            $('#objChartTotalVal').text(res.total);
            $('#objChartWrapper').show();

            const colors = res.labels.map((_, i) => `hsl(${(i * 47) % 360}, 65%, 55%)`);

            if (objChart) objChart.destroy();

            const ctx = document.getElementById('objDivisionChart').getContext('2d');
            objChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: res.labels,
                    datasets: [{
                        label: 'Jumlah Daily Task',
                        data: res.data,
                        backgroundColor: colors.map(c => c.replace('55%)', '55%, 0.75)')),
                        borderColor: colors,
                        borderWidth: 1.5,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.y} task`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, precision: 0 },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            ticks: {
                                maxRotation: 30,
                                callback: function(val, i) {
                                    const lbl = this.getLabelForValue(val);
                                    return lbl.length > 22 ? lbl.substring(0, 22) + '…' : lbl;
                                }
                            }
                        }
                    }
                }
            });
        }).fail(function () {
            $('#objChartLoading').hide();
            $('#objChartEmpty').show();
        });
    }
})();
</script>
@endcanAccess
<script>
    $(document).ready(function() {
        $('.collapse').on('show.bs.collapse', function () {
            $(this).parent().find('.rotate-icon').addClass('rotate');
        }).on('hide.bs.collapse', function () {
            $(this).parent().find('.rotate-icon').removeClass('rotate');
        });
    });
</script>
@endsection
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<!-- Bootstrap Datepicker -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
<style>
.card-header .btn-link {
    color: #007bff;
    text-decoration: none;
}
.card-header .btn-link:hover {
    text-decoration: underline;
}
.card-header i {
    transition: transform 0.3s ease;
}
.card-header .collapsed .fa-chevron-right {
    transform: rotate(0);
}
.card-header[aria-expanded="true"] .fa-chevron-right {
    transform: rotate(90deg);
}
.list-group-item {
    border: none;
    padding-left: 2rem;
}
</style>
<style>
    .list-group-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .task-details {
        flex: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .task-details span {
        margin-right: 10px;
    }
    .task-actions {
        display: flex;
        align-items: center;
    }
    .task-actions form {
        margin: 0;
    }
    .task-actions a,
    .task-actions button {
        margin-right: 5px;
    }
    .select2-selection__choice
    {
        background-color: #007bff !important;
        border: 2px solid #007bff !important;
    }

    .select2-selection__choice__remove
    {
        color: #fe0700 !important;
        border: 2px solid #007bff !important;
    }
    #userTasks 
    {
        max-height: 500px;
        overflow-y: auto;
        margin-top: 20px;
    }

    .accordion .card-header {
        cursor: pointer;
    }

</style>
<style>
  #objDivisionChart { width: 100% !important; }
  .border-left-primary { border-left: 5px solid #007bff !important; }
  .border-left-warning { border-left: 5px solid #ffc107 !important; }
  .border-left-success { border-left: 5px solid #28a745 !important; }
  .badge-info {
    background-color: #17a2b8;
    color: #fff;
    font-size: 0.75rem;
    padding: 0.4em 0.6em;
  }
</style>
@endsection
