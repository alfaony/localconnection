@extends('adminlte::page')

@section('title', 'Laporan Check-in Karyawan')

@section('content_header')
<div class="card">
    <div class="card-header">
        <h4>Laporan Check-in Karyawan</h4>
    </div>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('employee-checking.index') }}" method="GET">
            <div class="row mb-3 justify-content-end">
                <div class="col-md-4">
                    <input type="hidden" name="tab" value="{{ request('tab') }}">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Tanggal" id="date_range" value="{{ request('start_date') && request('end_date') ? request('start_date').' - '.request('end_date') : '' }}">
                        <input type="hidden" id="start_date" name="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" id="end_date" name="end_date" value="{{ request('end_date') }}">
                    </div>
                </div>
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
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
    <div class="card-body">
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="reportTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ request('tab') == 'point_checkin' ? 'active' : '' }}" 
                   id="point-checkin-tab" 
                   data-toggle="tab" 
                   href="#point_checkin" 
                   role="tab" 
                   aria-controls="point_checkin" 
                   aria-selected="{{ request('tab') == 'point_checkin' ? 'true' : 'false' }}">
                   Point Check-in
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('tab') == 'detail_checkin' ? 'active' : '' }}" 
                   id="detail-checkin-tab" 
                   data-toggle="tab" 
                   href="#detail_checkin" 
                   role="tab" 
                   aria-controls="detail_checkin" 
                   aria-selected="{{ request('tab') == 'detail_checkin' ? 'true' : 'false' }}">
                   Detail Check-in
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-3">
            @if(request('tab') == 'point_checkin')
            <div class="tab-pane fade {{ request('tab') == 'point_checkin' ? 'show active' : '' }}" 
                 id="point_checkin" 
                 role="tabpanel" 
                 aria-labelledby="point-checkin-tab">
                <!-- Tabel hasil pencarian -->
                <div class="table-reponsive">
                    <table class="table table-bordered mt-4">
                        <thead>
                            <tr>
                                <th>Nama Pengguna</th>
                                <th>Tanggal</th>
                                <th>Check-in Berhasil</th>
                                <th>Check-in Gagal</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($checkins as $checkin)
                                <tr>
                                    <td>{{ $checkin->user->name }}</td>
                                    <td>{{ $checkin->checkin_date }}</td>
                                    <td>{{ $checkin->total_successful }}</td>
                                    <td>{{ $checkin->total_failed }}</td>
                                    <td>
                                        <form action="{{ route('employee-checking.index') }}" method="GET">
                                            <input type="hidden" name="tab" value="{{ 'detail_checkin' }}">
                                            <div class="input-group">
                                                <input type="hidden" id="start_date" name="start_date" value="{{ $checkin->checkin_date }}">
                                                <input type="hidden" id="end_date" name="end_date" value="{{ $checkin->checkin_date }}">
                                                <input type="hidden" id="user_id" name="user_id" value="{{ $checkin->user->id }}">
                                            </div>
                                            <button type="submit" class="btn btn-primary"><i class="fa fa-eye"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Tidak ada data untuk ditampilkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $checkins->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
            @endif
            @if(request('tab') == 'detail_checkin')
            <div class="tab-pane fade {{ request('tab') == 'detail_checkin' ? 'show active' : '' }}" 
                 id="detail_checkin" 
                 role="tabpanel" 
                 aria-labelledby="detail-checkin-tab">

                  <!-- Employee Check-ins Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Jadwal Check-In</th>
                                <th>Status</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            @forelse($employeeCheckings as $checking)
                                <tr>
                                    <td>{{ $checking->user->name }}</td>
                                    <td>{{ $checking->scheduled_time ? \Carbon\Carbon::parse($checking->scheduled_time)->locale('id')->translatedFormat('F d,y H:i:s') : '' }}</td>
                                    <td>
                                        @if($checking->is_completed)
                                            <span class="badge bg-success"><i class="fa fa-check"></i></span>
                                        @else
                                            <span class="badge bg-danger"><i class="fa fa-times"></i></span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(($checking->location_latitude && $checking->location_longitude) || ($checking->photo_path))
                                            <button type="button" class="btn btn-info btn-sm show-detail" 
                                                    data-toggle="modal" 
                                                    data-target="#detailModal{{ $checking->id }}" 
                                                    data-lat="{{ $checking->location_latitude }}" 
                                                    data-lng="{{ $checking->location_longitude }}" 
                                                    data-id="{{ $checking->id }}">
                                                <i class="fa fa-eye"></i> Detail
                                            </button>

                                            <!-- Modal Detail -->
                                            <div class="modal fade" id="detailModal{{ $checking->id }}" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <!-- Modal Header -->
                                                        <div class="modal-header bg-primary text-white">
                                                            <h5 class="modal-title" id="detailModalLabel"><i class="fa fa-info-circle"></i> Detail Check-In</h5>
                                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>

                                                        <!-- Modal Body -->
                                                        <div class="modal-body">
                                                            <!-- Check-In Photo -->
                                                            @if($checking->photo_path)
                                                                <div class="mb-3 text-center">
                                                                    <label class="font-weight-bold"><i class="fa fa-camera"></i> Foto Check-In:</label>
                                                                    <div class="border rounded p-3">
                                                                        <img src="{{ asset($checking->photo_path) }}" alt="Foto Check-In" class="img-fluid rounded">
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <!-- Check-In Location -->
                                                            @if($checking->location_latitude && $checking->location_longitude)
                                                                <div class="mb-3">
                                                                    <label class="font-weight-bold"><i class="fa fa-map-marker-alt"></i> Lokasi Check-In:</label>
                                                                    <div class="border rounded p-3">
                                                                        <iframe 
                                                                            width="100%" 
                                                                            height="350" 
                                                                            frameborder="0" 
                                                                            style="border:0; border-radius: 8px;"
                                                                            src="https://www.google.com/maps?q={{ $checking->location_latitude }},{{ $checking->location_longitude }}&hl=id&z=14&output=embed" 
                                                                            allowfullscreen>
                                                                        </iframe>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <!-- Modal Footer -->
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-times"></i> Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Tidak ada detail</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No check-ins found for the selected criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                 @if(count($employeeCheckings) > 0)
                <div class="d-flex justify-content-center mt-4">
                    {{ $employeeCheckings->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>


@endsection

@section('js')
<!-- Include Select2 JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>   
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
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
                $(this).val(''); // Clear the displayed date range
                $('#start_date').val(''); // Clear the hidden start_date input
                $('#end_date').val(''); // Clear the hidden end_date input
            });

            // Capture the date range selection
            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                $('#start_date').val(picker.startDate.format('DD-MM-YYYY'));
                $('#end_date').val(picker.endDate.format('DD-MM-YYYY'));
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            // Menyimpan tab yang diklik dan mempertahankan filter pencarian
            $('a[data-toggle="tab"]').on('click', function(e) {
                var tabName = $(e.target).attr('href').replace('#', '');
                
                // Ambil nilai dari input pencarian yang ada
                var userId = $('#user_id').val();
                var startDate = $('#start_date').val();
                var endDate = $('#end_date').val();

                // Buat URL dengan semua parameter
                var url = "{{ route('employee-checking.index') }}?tab=" + tabName;

                // Tambahkan parameter pencarian jika ada
                if (userId) {
                    url += "&user_id=" + userId;
                }
                if (startDate) {
                    url += "&start_date=" + startDate;
                }
                if (endDate) {
                    url += "&end_date=" + endDate;
                }

                // Redirect ke URL baru dengan parameter
                window.location.href = url;
            });
        });
    </script>
@stop
@section('css')
    <!-- Include Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
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