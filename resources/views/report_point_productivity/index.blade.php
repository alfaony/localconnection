
@extends('adminlte::page')

@section('content_header')
    <title>Report Point Productivity</title>
@stop

@section('content')

    <div class="container">
        <h2 class="mb-4">Report Point Productivity</h2>
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
                                @foreach ($allUsers as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" onclick="showLoading()">Filter</button>
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
                                <th>Poin Tugas Harian</th>
                                <th>Total Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reports as $report)
                                <tr>
                                    <td>{{ $report['name'] }}</td>
                                    <td>{{ $report['training_points'] }}</td>
                                    <td>{{ $report['ip_right_points'] }}</td>
                                    <td>{{ $report['sales_achievement_points'] }}</td>
                                    <td>{{ $report['daily_task_point'] }}</td>
                                    <td>{{ $report['total_points'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $users->withQueryString()->links('vendor.pagination.bootstrap-4') }}
            </div>
        @endif
    </div>
@stop
@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
     $('.select2').select2({
            width: '100%',
            // placeholder: 'Pilih Quote'
        });
</script>
<script>
    function showLoading() {
        document.getElementById('loading').style.display = 'block';
    }
</script>
@stop
@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        .loading {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }
        .select2-selection__rendered 
        {
            line-height: 31px !important;
        }
        .select2-container .select2-selection--single 
        {
            height: 35px !important;
        }
        .select2-selection__arrow {
            height: 34px !important;
        }
    </style>
@stop

