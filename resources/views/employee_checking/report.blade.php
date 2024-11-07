@extends('adminlte::page')

@section('content')
<div class="card mt-5">
    <div class="card-body">
        <h1>Laporan Check-in Karyawan</h1>

        <!-- Form pencarian -->
        <form action="{{ route('employee-checking.report') }}" method="GET">
            <div class="row mb-3">
                <div class="col-md-4">
                    <select name="user_id" id="user_id" class="form-control select2">
                        <option value="">Semua Pengguna</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Tanggal" id="date_range" value="{{ request('start_date') && request('end_date') ? request('start_date').' - '.request('end_date') : '' }}">
                        <input type="hidden" id="start_date" name="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" id="end_date" name="end_date" value="{{ request('end_date') }}">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
    </div>
</div>
@endsection

@section('js')
    <!-- Include Select2 JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>   
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2();
        });
        
        $(document).ready(function () {
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
@endsection

@section('css')
    <!-- Include Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
    body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            border-radius: 5px;
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
@endsection
