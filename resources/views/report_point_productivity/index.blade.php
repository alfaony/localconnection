@extends('adminlte::page')

@section('content_header')
    <title>Report Point Productivity</title>
@stop

@section('content')

    <div class="container">
        <h2 class="mb-4">Report Point Productivity</h2>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('report-productivity.index') }}">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="start_date">Start Date:</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="end_date">End Date:</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="user_id">User:</label>
                            <select name="user_id" id="user_id" class="form-control select2">
                                <option value="">All Users</option>
                                <option value="all_user_checkin">All User Checkin</option>
                                @foreach ($allUsers as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" onclick="showLoading()">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    @canAccess('export','report_productivities')
                    <button type="button" class="btn btn-success" onclick="exportData()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    @endcanAccess
                </form>
            </div>
        </div>

        <div id="loading" class="loading">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        @if($reports->isEmpty())
            <div class="alert alert-warning">
                No data available for the selected date range.
            </div>
        @else
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped mt-4">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Poin Training</th>
                                <th>Poin Hak Cipta</th>
                                <th>Poin Pencapaian Penjualan</th>
                                <th>Point Tugas</th>
                                <th>Point Punishment</th>
                                <th>Total Poin</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reports as $index => $report)
                                <tr>
                                    <td>{{ $report['name'] }}</td>
                                    <td>{{ $report['training_points'] }}</td>
                                    <td>{{ $report['ip_right_points'] }}</td>
                                    <td>{{ $report['sales_achievement_points'] }}</td>
                                    <td>{{ $report['daily_task_points'] }}</td>
                                    <td>{{ $report['punishment_points'] }}</td>
                                    <td>{{ $report['total_points'] }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="showPointDetails('{{ $users[$index]->id }}', '{{ $report['name'] }}')">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $users->withQueryString()->links('vendor.pagination.bootstrap-4') }}
            </div>
        @endif
    </div>

    <!-- Modal for Point Details -->
    <div class="modal fade" id="pointDetailsModal" tabindex="-1" role="dialog" aria-labelledby="pointDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pointDetailsModalLabel">Detail Poin - <span id="modalUserName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBody">
                    <div class="text-center" id="loadingSpinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat detail poin...</p>
                    </div>
                    <div id="pointDetailsContent" style="display: none;">
                        <!-- Content will be populated via AJAX -->
                    </div>
                </div>
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
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $('.select2').select2({
        width: '100%',
    });

    function showLoading() {
        document.getElementById('loading').style.display = 'block';
    }

    function exportData() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const userId = document.getElementById('user_id').value;
        
        let url = "{{ route('report-productivity.export') }}?start_date=" + startDate + "&end_date=" + endDate;
        
        if (userId) {
            url += "&user_id=" + userId;
        }
        
        showLoading();
        window.location.href = url;
        
        setTimeout(() => {
            document.getElementById('loading').style.display = 'none';
        }, 2000);
    }

    function showPointDetails(userId, userName) {
        $('#modalUserName').text(userName);
        $('#pointDetailsModal').modal('show');
        $('#loadingSpinner').show();
        $('#pointDetailsContent').hide();
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        $.ajax({
            url: "{{ route('report-productivity.details') }}",
            method: 'GET',
            data: {
                user_id: userId,
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                if (response.success) {
                    let html = buildPointDetailsHtml(response.data);
                    $('#pointDetailsContent').html(html);
                    $('#loadingSpinner').hide();
                    $('#pointDetailsContent').show();
                } else {
                    $('#pointDetailsContent').html('<div class="alert alert-warning">Tidak ada data tersedia</div>');
                    $('#loadingSpinner').hide();
                    $('#pointDetailsContent').show();
                }
            },
            error: function() {
                $('#pointDetailsContent').html('<div class="alert alert-danger">Terjadi kesalahan saat memuat data</div>');
                $('#loadingSpinner').hide();
                $('#pointDetailsContent').show();
            }
        });
    }

    function buildPointDetailsHtml(data) {
        let html = '<div class="accordion" id="pointAccordion">';
        
        // Training
        if (data.trainings.items.length > 0) {
            html += buildCategorySection('training', 'Poin Training', data.trainings.items, data.trainings.total, 'primary');
        }
        
        // IP Rights
        if (data.ip_rights.items.length > 0) {
            html += buildCategorySection('ipright', 'Poin Hak Cipta', data.ip_rights.items, data.ip_rights.total, 'success');
        }
        
        // Sales Achievements
        if (data.sales_achievements.items.length > 0) {
            html += buildCategorySection('sales', 'Poin Pencapaian Penjualan', data.sales_achievements.items, data.sales_achievements.total, 'warning');
        }
        
        // Daily Tasks
        if (data.daily_tasks.items.length > 0) {
            html += buildCategorySection('dailytask', 'Point Tugas', data.daily_tasks.items, data.daily_tasks.total, 'info');
        }
        
        // Punishment Tasks
        if (data.punishment_tasks.items.length > 0) {
            html += buildCategorySection('punishment', 'Point Punishment', data.punishment_tasks.items, data.punishment_tasks.total, 'danger');
        }
        
        html += '</div>';
        
        if (data.trainings.items.length === 0 && data.ip_rights.items.length === 0 && 
            data.sales_achievements.items.length === 0 && data.daily_tasks.items.length === 0 && 
            data.punishment_tasks.items.length === 0) {
            html = '<div class="alert alert-info">Tidak ada detail poin untuk pengguna ini pada periode yang dipilih.</div>';
        }
        
        return html;
    }

    function buildCategorySection(id, title, items, total, color) {
        let html = `
            <div class="card">
                <div class="card-header" id="heading${id}">
                    <h5 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse${id}" aria-expanded="true" aria-controls="collapse${id}">
                            ${title} <span class="badge badge-${color}">${total} poin</span>
                        </button>
                    </h5>
                </div>
                <div id="collapse${id}" class="collapse" aria-labelledby="heading${id}" data-parent="#pointAccordion">
                    <div class="card-body">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Tanggal</th>
                                    <th>Poin</th>
                                </tr>
                            </thead>
                            <tbody>`;
        
        items.forEach(function(item) {
            html += `
                <tr>
                    <td>${item.name}</td>
                    <td>${item.date}</td>
                    <td>${item.point}</td>
                </tr>`;
        });
        
        html += `
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td colspan="2">Total</td>
                                    <td>${total}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>`;
        
        return html;
    }
</script>
@stop

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .loading {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
    }
    .select2-selection__rendered {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }
</style>
@stop