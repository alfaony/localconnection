@extends('adminlte::page')

@section('title', 'Daftar Absensi')

@section('content')
    @include('components.alert')
    <div class="row mb-4 mt-2">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-check mr-2"></i>
                        Daftar Absensi Karyawan
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                  <form method="GET" action="{{ route('office-attendance.index') }}" class="mb-4">
                        <div class="row g-3">

                            {{-- Filter Tanggal (Date Range) --}}
                            <div class="col-md-4">
                                <label for="dateRange">Tanggal</label>
                                <input type="text" class="form-control" id="dateRange" placeholder="Pilih rentang tanggal" autocomplete="off">
                                <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                                <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                            </div>

                            {{-- Filter Karyawan --}}
                            <div class="col-md-4">
                                <label for="employeeFilter" class="form-label">Karyawan</label>
                                <select class="form-control select2" name="employee">
                                    <option value="">Semua Karyawan</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ request('employee') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filter Urutan --}}
                            <div class="col-md-4">
                                <label for="sortBy" class="form-label">Urutkan</label>
                                <select class="form-control" id="sortBy" name="sort">
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                                </select>
                            </div>

                            {{-- Tombol Filter --}}
                            <div class="col-md-2 d-flex align-items-end mt-2 mb-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Filter
                                </button>
                            </div>

                            {{-- Tombol Reset --}}
                            <div class="col-md-2 d-flex align-items-end mt-2 mb-2">
                                <a href="{{ route('office-attendance.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-sync-alt me-1"></i> Reset
                                </a>
                            </div>
                            @canAccess('export','office_attendances')
                            <div class="col-md-3 d-flex align-items-end mt-2 mb-2">
                                <a href="{{ route('office_attendance.export', request()->query()) }}" class="btn btn-success w-100">
                                    <i class="fas fa-file-excel mr-2"></i> Export Excel
                                </a>
                            </div>
                            @endcanAccess

                        </div>
                    </form>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Absensi</span>
                                    <span class="info-box-number">{{ $totalAttendance }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Jumlah total record absensi
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Hadir Hari Ini</span>
                                    <span class="info-box-number">{{ $todayAttendance }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ $totalEmployees > 0 ? ($todayAttendance/$totalEmployees)*100 : 0 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $totalEmployees }} total karyawan
                                    </span>
                                </div>
                            </div>
                        </div>
                        {{-- 
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Rata-rata Waktu</span>
                                    <span class="info-box-number">08:15</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 75%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Rata-rata jam absensi
                                    </span>
                                </div>
                            </div>
                        </div>
                        --}}
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-map-marker-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Lokasi Terdeteksi</span>
                                    <span class="info-box-number">{{ $locationCount }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ $totalAttendance > 0 ? ($locationCount/$totalAttendance)*100 : 0 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $totalAttendance > 0 ? round(($locationCount/$totalAttendance)*100, 2) : 0 }}% dari total
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Absensi -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nama</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Lokasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($officeAttendance as $index => $attendance)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm mr-3">
                                                    <div class="avatar-title bg-primary rounded-circle">
                                                        @if($attendance->selfie_path)
                                                            <img src="{{ s3_asset(true,10, $attendance->selfie_path) }}" alt="" class="avatar-sm rounded-circle">

                                                        @endif
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold">{{ $attendance->user->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <div class="font-weight-bold">{{ $attendance->created_at->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($attendance->created_at)->locale('id')->translatedFormat('l') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info p-2">
                                                <i class="fas fa-clock mr-1"></i> {{ $attendance->created_at->format('H:i:s') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($attendance->location_lat && $attendance->location_long)
                                                <span class="badge badge-success">Tersedia</span>
                                            @else
                                                <span class="badge badge-secondary">Tidak Tersedia</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attendance->location_lat && $attendance->location_long)
                                                <button type="button" class="btn btn-sm btn-primary view-location" 
                                                        data-lat="{{ $attendance->location_lat }}" 
                                                        data-long="{{ $attendance->location_long }}"
                                                        data-name="{{ $attendance->user->name }}"
                                                        data-date="{{ $attendance->created_at->format('d/m/Y H:i') }}">
                                                    <i class="fas fa-map-marker-alt"></i> Lihat Peta
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-secondary" disabled>
                                                    <i class="fas fa-map-marker-alt"></i> Tidak Ada Lokasi
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="dataTables_info">
                            Menampilkan {{ $officeAttendance->firstItem() }} hingga {{ $officeAttendance->lastItem() }} dari {{ $officeAttendance->total() }} entri
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $officeAttendance->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Map -->
    <div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="mapModalLabel">Lokasi Absensi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="attendanceMap" style="height: 400px; width: 100%;"></div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="location-details">
                                <h6>Detail Lokasi:</h6>
                                <p id="locationDetails"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <!-- Daterangepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        .avatar-sm {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .avatar-title {
            color: white;
            font-weight: bold;
        }
        .info-box {
            box-shadow: 0 0 1px rgba(0, 0, 0, 0.125), 0 1px 3px rgba(0, 0, 0, 0.2);
            border-radius: 0.5rem;
        }
        .table th {
            border-top: none;
        }
        .card-title {
            margin-bottom: 0;
        }
        .view-location {
            transition: all 0.3s;
        }
        .view-location:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        #attendanceMap {
            border-radius: 8px;
            z-index: 1;
        }

        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 0.25rem 0.5rem;
            border: none;
        }
        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da !important;
            height: 38px !important;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 2.1;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }
    </style>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <!-- jQuery dan Moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(function() {
            const start = "{{ request('start_date') }}";
            const end = "{{ request('end_date') }}";

            $('#dateRange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    applyLabel: 'Terapkan',
                    cancelLabel: 'Batal'
                }
            });

            $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
                $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' s/d ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
                $('#start_date').val('');
                $('#end_date').val('');
                $(this).val('');
            });

            // Set value saat reload form (jika ada filter sebelumnya)
            if (start && end) {
                $('#dateRange').val(start + ' s/d ' + end);
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2();

            // Initialize map variable
            let map = null;
            let marker = null;

            // Handle view location button click
            $('.view-location').click(function() {
                const lat = $(this).data('lat');
                const long = $(this).data('long');
                const name = $(this).data('name');
                const date = $(this).data('date');
                
                // Update modal title and details
                $('#mapModalLabel').text(`Lokasi Absensi - ${name}`);
                $('#locationDetails').html(`
                    <strong>Nama:</strong> ${name}<br>
                    <strong>Waktu:</strong> ${date}<br>
                    <strong>Koordinat:</strong> ${lat}, ${long}
                `);
                
                // Initialize map if not already initialized
                if (!map) {
                    map = L.map('attendanceMap').setView([lat, long], 15);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);
                } else {
                    map.setView([lat, long], 15);
                }
                
                // Remove existing marker if any
                if (marker) {
                    map.removeLayer(marker);
                }
                
                // Add new marker
                marker = L.marker([lat, long]).addTo(map)
                    .bindPopup(`
                        <b>${name}</b><br>
                        ${date}<br>
                        <small>Koordinat: ${lat}, ${long}</small>
                    `)
                    .openPopup();
                
                // Show the modal
                new bootstrap.Modal(document.getElementById('mapModal')).show();
            });

            // Reset filter button
            $('#resetFilter').click(function() {
                $('#dateFilter').val('');
                $('#employeeFilter').val('').trigger('change');
                $('#sortBy').val('newest');
                $('#filter').val('');
                $(this).closest('form')[0].submit();
            });
        });
    </script>
@endsection