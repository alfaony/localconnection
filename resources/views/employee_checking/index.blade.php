@extends('adminlte::page')

@section('title', 'Laporan Check-in Karyawan')

@section('content_header')
<div class="card">
    <div class="card-header">
        <h4>Daftar & Laporan Check-in Karyawan</h4>
    </div>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        
        <form action="{{ route('employee-checking.index') }}" method="GET">
            <div class="row justify-content-end">
                <div class="col-md-3">
                        <select name="sort_order" id="sort_order" class="form-control">
                            <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Dari Point Tertinggi</option>
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Dari Point Terendah</option>
                        </select>
                </div>
                <div class="col-md-3">
                    <input type="hidden" name="tab" value="{{ request('tab') }}">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Tanggal" id="date_range" value="{{ $start && $end ? \Carbon\Carbon::parse($start)->format('d-m-Y').' - '.\Carbon\Carbon::parse($end)->format('d-m-Y') : '' }}">
                        <input type="hidden" id="start_date" name="start_date" value="{{ $start ? \Carbon\Carbon::parse($start)->format('d-m-Y') : '' }}">
                        <input type="hidden" id="end_date" name="end_date" value="{{ $end ? \Carbon\Carbon::parse($end)->format('d-m-Y') : '' }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="user_id" id="user_id" class="form-control select2">
                        <option value="">Semua Pengguna</option>
                        @foreach($userSelect as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <button type="button" class="btn btn-secondary ml-2" onclick="window.location.href='{{ route('employee-checking.index') }}'">Reset</button>
            </div>
        </form>
    </div>
    <div class="card-body">
        <!-- Tab Navigation -->
        <div class="d-flex justify-content-between">
            <div>
                <span class="form-control-plaintext text-muted">
                    Periode : {{ $start ? \Carbon\Carbon::parse($start)->format('d F Y') : '' }} - {{ $end ? \Carbon\Carbon::parse($end)->format('d F Y') : '' }}
                </span>
            </div>
            <div class="ml-auto">
                {{-- Tombol Export --}}
                @canAccess('export','employee_checkings')
                @canAccess('checkExportStatus','employee_checkings')
                @canAccess('clearsession','employee_checkings')
                <a href="{{ route('employee-checking.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="btn btn-primary">
                    <i class="fa fa-file-excel"></i> Export Excel
                </a>
                
                <a href="{{ route('employee-checking.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="btn btn-info">
                    <i class="fa fa-file-csv"></i> Export CSV
                </a>
                @endcanAccess
                @endcanAccess
                @endcanAccess

                @canAccess('create','pass_checkings')
                <a class="btn btn-success" href="{{ route('pass-checking.index') }}"><i class="fa fa-list"></i> Pass Checking</a>
                @endcanAccess
            </div>
        </div>
        <ul class="nav nav-tabs" id="reportTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ request('tab') == 'detail_checkin' || is_null(request('tab')) ? 'active' : '' }}" 
                id="detail-checkin-tab" 
                data-toggle="tab" 
                href="#detail_checkin" 
                role="tab" 
                aria-controls="detail_checkin" 
                aria-selected="{{ request('tab') == 'detail_checkin' || is_null(request('tab')) ? 'true' : 'false' }}">
                    Daftar Check-in Karyawan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('tab') == 'point_checkin' ? 'active' : '' }}" 
                   id="point-checkin-tab" 
                   data-toggle="tab" 
                   href="#point_checkin" 
                   role="tab" 
                   aria-controls="point_checkin" 
                   aria-selected="{{ request('tab') == 'point_checkin' ? 'true' : 'false' }}">
                   Jumlah Check-in
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-3">
            @if(request('tab') == 'detail_checkin' || is_null(request('tab')))
            <div class="tab-pane fade {{ request('tab') == 'detail_checkin' || is_null(request('tab')) ? 'show active' : '' }}" 
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
                                @if($manualCheck['manual_checkin'])
                                <th>Check-In</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            
                            @forelse($employeeCheckings as $index => $checking)
                                <tr>
                                    <td>{{ $checking->user->name }}
                                    </td>
                                    <td>
                                        @if(!$checking->is_active && !$checking->isDayoff())
                                            @if($checking->is_permission)
                                            {{ $checking->scheduled_time ? \Carbon\Carbon::parse($checking->scheduled_time)->locale('id')->translatedFormat('F d,y') : '' }}
                                            @else
                                            {{ $checking->checkin_start_time ? \Carbon\Carbon::parse($checking->checkin_start_time)->locale('id')->translatedFormat('F d,y H:i:s') : \Carbon\Carbon::parse($checking->scheduled_time)->locale('id')->translatedFormat('F d,y H:i:s') }}
                                            @if($manualCheck['manual_checkin'])
                                            <br>
                                            <span class="badge bg-primary">
                                                Waktu Check-In : {{ $checking->scheduled_time ? \Carbon\Carbon::parse($checking->scheduled_time)->locale('id')->translatedFormat('H:i:s') : '' }}
                                            </span>
                                            @endif
                                            @endif
                                        @else
                                            {{ $checking->scheduled_time ? \Carbon\Carbon::parse($checking->scheduled_time)->locale('id')->translatedFormat('F d,y') : \Carbon\Carbon::parse($checking->scheduled_time)->locale('id')->translatedFormat('F d,y') }}
                                            @if($manualCheck['manual_checkin'])
                                            <br>
                                            <span class="badge bg-primary">
                                                Waktu Check-In : {{ $checking->scheduled_time ? \Carbon\Carbon::parse($checking->scheduled_time)->locale('id')->translatedFormat('H:i:s') : '' }}
                                            </span>
                                            @endif
                                        @endif
                                    </td>   
                                    <td>
                                        @if(!$checking->is_active && !$checking->isDayoff() && !$checking->isSick())
                                        @if($checking->is_completed)
                                            <span class="badge bg-success"><i class="fa fa-check"></i></span>
                                        @else
                                            <span class="badge bg-danger"><i class="fa fa-times"></i></span>
                                        @endif
                                        @else
                                            @if($checking->isDayoff())
                                            <span class="badge bg-info"><i class="fa fa-suitcase"></i></span>
                                                Sedang Cuti
                                            @elseif($checking->is_permission)
                                            <span class="badge bg-info"><i class="fa fa-hospital"></i></span>
                                                Izin
                                            @else
                                                @if($checking->isToday($checking->user->start_time, $checking->user->end_time ))
                                                    <span class="badge bg-warning"><i class="fa fa-clock"></i></span>
                                                @else
                                                    <span class="badge bg-danger"><i class="fa fa-times"></i></span>
                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$checking->is_pass && ($checking->location_latitude && $checking->location_longitude) || ($checking->photo_path))
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
                                        @elseif($checking->is_pass)
                                            <a href="{{ route('pass-checking.show', $checking->passChecking->id) }}">{{ $checking->passChecking ? $checking->passChecking->name : "" }}</a>
                                        @else
                                            <span class="text-muted">Tidak ada detail</span>
                                        @endif
                                    </td>
                                    @if($manualCheck['manual_checkin'])
                                    @php
                                        // Ambil objek setelahnya jika ada
                                        $nextChecking = $employeeCheckings[$index + 1] ?? null;
                                    @endphp
                                    @if($checking->user_id == Auth::user()->id)
                                    <td>
                                        @if(!$checking->is_active)
                                            @if($checking->is_completed)
                                                <span class="badge bg-success"><i class="fa fa-check"></i></span>
                                            @else
                                                <span class="badge bg-danger"><i class="fa fa-times"></i></span>
                                            @endif
                                        @else
                                            @if($checking->isToday(Auth::user()->start_time, Auth::user()->end_time))
                                            @if($checking->user_id == Auth::user()->id)                    
                                                @if($checking->is_active && (!$nextChecking || !$nextChecking->is_active))
                                                    <button class="btn btn-info btn-sm" type="button"
                                                        onclick="checkLastScheduledCheckin('{{ $checking->id }}', {{ $manualCheck['requires_photo'] ? 'true' : 'false' }}, {{ $manualCheck['requires_location'] ? 'true' : 'false' }})" >
                                                        <i class="fa fa-pencil"></i> Manual Check-In
                                                    </button>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            @else
                                                <span class="badge bg-danger"><i class="fa fa-times"></i></span>
                                            @endif
                                            @else
                                                <span class="badge bg-danger"><i class="fa fa-times"></i></span>
                                            @endif

                                        @endif
                                    </td>
                                    @else
                                        <td> - </td>
                                    @endif
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No check-ins found for the selected criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <!-- Pagination Links -->
                     @if(count($employeeCheckings) > 0)
                    <div class="d-flex justify-content-center mt-4">
                        {{ $employeeCheckings->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                    </div>
                    @endif
                </div>

            </div>
            @endif
            @if(request('tab') == 'point_checkin')
            <div class="tab-pane fade {{ request('tab') == 'point_checkin' ? 'show active' : '' }}" 
                 id="point_checkin" 
                 role="tabpanel" 
                 aria-labelledby="point-checkin-tab">
                <!-- Tabel hasil pencarian -->
                <div class="table-responsive">
                    <table class="table table-bordered mt-4">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Total Check-In</th>
                                <th>Total Check-In Hari Ini</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->point_checkin }}</td>
                                    <td>{{ $user->today_percentage }}</td>
                                    <td>
                                        @if($user->isShow())
                                        <form action="{{ route('employee-checking.index') }}" method="GET">
                                            <input type="hidden" name="tab" value="detail_checkin">
                                            <div class="input-group">
                                                <input type="hidden" id="user_id" name="user_id" value="{{ $user->id }}">
                                            </div>
                                            <button type="submit" class="btn btn-primary"><i class="fa fa-eye"></i></button>
                                        </form>
                                        @else
                                        
                                        @endif
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

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $users->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if($manualCheck['manual_checkin'])
<!-- Global Popup Check-In -->
<div id="globalCheckinPopup" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="globalCheckinPopupLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="globalCheckinPopupLabel">Time to Check-In</h5>
                <button type="button" class="close" id="btnclosemodal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Foto -->
                <div id="globalPhotoSection" class="form-group" style="display: none; margin-top: 15px;">
                    <video id="globalVideoFeed" autoplay playsinline style="width: 100%; height: auto;"></video>
                    <canvas id="globalCanvas" style="display:none;"></canvas>
                    <button id="globalTakePhotoButton" class="btn btn-secondary mt-2" onclick="takeGlobalPhoto()">Take Photo</button>
                    <button id="toggleCameraButton" class="btn btn-info mt-2" onclick="toggleCamera()">Switch Camera</button>

                    <img id="globalPhotoPreview" src="#" alt="Photo Preview" style="display:none;" class="img-thumbnail mt-3">
                    <input type="file" id="globalPhoto" name="photo" style="display: none;"> <!-- Hidden file input for form submission -->
                </div>
                <span id="globalPhotoWarning" style="color: red; font-size: 12px;"></span> <!-- Peringatan foto -->

                <!-- Lokasi -->
                <div id="globalLocationSection" class="form-group" style="display: none; margin-top: 15px;">
                    <button class="btn btn-success" onclick="getLocation()">Share Location</button>
                    <p id="globalLocationStatus"></p>
                    <input type="hidden" id="globalLatitude">
                    <input type="hidden" id="globalLongitude">
                    <span id="globalLocationWarning" style="color: red; font-size: 15px;"></span>
                </div>

                <!-- reCAPTCHA -->
                <div id="globalCaptchaSection" class="mt-4">
                    <div class="g-recaptcha" data-sitekey="{{ config('captcha.sitekey') }}" 
                    data-callback="onRecaptchaSuccessGlobal"
                    data-expired-callback="onRecaptchaExpiredGlobal"
                    data-error-callback="onRecaptchaErrorGlobal">
                    ></div>
                    <span id="globalCaptchaWarning" style="color: red; font-size: 15px;"></span>
                </div>
            </div>
            <div class="modal-footer" id="FooterglobalSubmitCheckin">
                <button id="globalSubmitCheckin" class="btn btn-primary" onclick="onSubmit()">Submit Check-in</button>
                <button id="globalCloseCheckin"type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>   
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @canAccess('export','employee_checkings')
    @canAccess('checkExportStatus','employee_checkings')
    @canAccess('clearsession','employee_checkings')

    @if(Session::get('export'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let isDownloaded = false; // Track if file has been downloaded
            const loadingOverlay = document.createElement('div');
            
            // Add a loading overlay
            loadingOverlay.innerHTML = `
                <div id="loading-overlay" style="display: flex; justify-content: center; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999; color: white; font-size: 20px;">
                    <div>
                        <div style="display: flex; justify-content: center; align-items: center;">
                            <div class="spinner-border text-light" role="status"></div>
                        </div>
                        <p>Exporting your file, please wait...</p>
                    </div>
                </div>
            `;
            document.body.appendChild(loadingOverlay);

            const checkExportStatus = () => {
                if (isDownloaded) return; // Stop if already downloaded

                fetch('{{ route('employee-checking.checkExportStatus') }}')
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);
                        
                        if (data.ready) {
                            isDownloaded = true; // Mark as downloaded

                            // Create a hidden download link to trigger download
                            const downloadLink = document.createElement('a');
                            downloadLink.href = data.download_url;
                            downloadLink.style.display = 'none';
                            downloadLink.download = ''; // Optional: specify a filename
                            
                            document.body.appendChild(downloadLink);
                            
                            // Add onload callback to clear session after download
                            downloadLink.onclick = () => {
                                // Clear export session AFTER file download starts
                                fetch('{{ route('employee-checking.clearsession') }}')
                                    .then(() => {
                                        // Hide the loading overlay
                                        document.getElementById('loading-overlay').remove();
                                    })
                                    .catch(error => console.error('Error clearing session:', error));
                            };

                            // Trigger download
                            downloadLink.click();

                            // Remove the link element after triggering download
                            document.body.removeChild(downloadLink);
                        } else {
                            setTimeout(checkExportStatus, 3000); // Retry every 3 seconds
                        }
                    })
                    .catch(error => {
                        console.error('Error checking export status:', error);
                        // Hide loading overlay if error occurs
                        document.getElementById('loading-overlay').remove();
                    });
            };

            checkExportStatus();
        });
    </script>
    @endif
    @endcanAccess
    @endcanAccess
    @endcanAccess

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

@if($manualCheck['manual_checkin'])
@canAccess('checkLastScheduledCheckin','employee_checkings')
<script>
    function checkLastScheduledCheckin(id, requiresPhoto, requiresLocation) {
        $.ajax({
            url: "{{ route('employee-checking.checkLastScheduledCheckin') }}",
            type: 'GET',
            success: function(response) {
                let status = response.status;
                let message = response.message;
                if (status) {
                    // Jika responsnya 'true', izinkan membuka modal
                    setCheckinId(id, requiresPhoto, requiresLocation);
                } else {
                    // Jika responsnya 'false', tampilkan pesan error
                    Swal.fire({
                        icon: 'error',
                        title: 'Check-in Gagal',
                        text: message,
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat memeriksa waktu check-in.',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            }
        });
    }
</script>
@endcanAccess
<script>
    let recaptchaTokenGlobal = '';

    function onRecaptchaSuccessGlobal(token) 
    {
        recaptchaTokenGlobal = token;
        document.getElementById('globalCaptchaWarning').textContent = ''; // Reset pesan peringatan
    }

    // Fungsi callback yang dipanggil saat reCAPTCHA token kadaluarsa
    function onRecaptchaExpiredGlobal() {
        recaptchaTokenGlobal = ''; // Hapus token
        document.getElementById('globalCaptchaWarning').textContent = 'Captcha telah kadaluarsa. Silakan ulangi lagi.';
    }

    // Fungsi callback yang dipanggil jika ada error pada reCAPTCHA
    function onRecaptchaErrorGlobal() {
        document.getElementById('globalCaptchaWarning').textContent = 'Terjadi kesalahan pada reCAPTCHA. Silakan coba lagi.';
    }
    // Fungsi untuk mengatur ID check-in dan memeriksa foto/lokasi
    function setCheckinId(id, requiresPhoto, requiresLocation) 
    {   
        $('#globalCheckinPopup').modal('show'); // Tampilkan modal dengan Bootstrap 4
        resetGlobalPopup(); // Reset data sebelumnya

        // Set ID check-in di popup
        document.getElementById('globalCheckinPopup').dataset.checkinId = id;

        // Tampilkan atau sembunyikan bagian foto sesuai dengan parameter
        const photoSection = document.getElementById('globalPhotoSection');
        const photoInput = document.getElementById('globalPhoto');
        if (requiresPhoto) {
            openGlobalCamera();
            
            photoSection.style.display = 'block';
            photoInput.setAttribute('required', 'required');
        } else {
            photoSection.style.display = 'none';
            photoInput.removeAttribute('required');
        }

        // Tampilkan atau sembunyikan bagian lokasi sesuai dengan parameter
        const locationSection = document.getElementById('globalLocationSection');
        if (requiresLocation) {
            locationSection.style.display = 'block';
            getLocation();
        } else {
            locationSection.style.display = 'none';
        }
    }

    document.getElementById('globalCheckinPopup').addEventListener('hidden.bs.modal', closeCamera);
    
    function closeCamera() 
    {
        const video = document.getElementById('globalVideoFeed');
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop()); // Stop all video tracks
            video.srcObject = null; // Remove the video source
        }
    }
    // Fungsi untuk mereset popup
    function resetGlobalPopup() {
        document.getElementById('globalPhoto').value = '';
        document.getElementById('globalLatitude').value = '';
        document.getElementById('globalLongitude').value = '';
        document.getElementById('globalPhotoPreview').style.display = 'none';
        document.getElementById('globalLocationWarning').textContent = '';
        document.getElementById('globalCaptchaWarning').textContent = '';
        grecaptcha.reset();
    }

    // Fungsi untuk submit check-in
    function onSubmit() 
    {
        const latitude = document.getElementById('globalLatitude').value;
        const longitude = document.getElementById('globalLongitude').value;
        const photoInput = document.getElementById('globalPhoto');
        const photo = document.getElementById('globalPhoto').files[0];
        const recaptchaTokenGlobal = grecaptcha.getResponse();
        const id = document.getElementById('globalCheckinPopup').dataset.checkinId;
        const storedToken = localStorage.getItem('fcm_token');

        const requiresPhoto = photoInput.hasAttribute('required');
        if (requiresPhoto && !photo) {
            document.getElementById('globalPhotoWarning').textContent = 'Foto diperlukan sebelum melakukan check-in.';
            document.getElementById('globalPhotoWarning').style.color = 'red';
            return; // Hentikan eksekusi jika foto belum diisi
        } else {
            document.getElementById('globalPhotoWarning').textContent = '';
        }

        // Validasi lokasi jika diperlukan
        const requiresLocation = document.getElementById('globalLocationSection').style.display === 'block';
        if (requiresLocation && (!latitude || !longitude)) {
            document.getElementById('globalLocationWarning').textContent = 'Lokasi diperlukan sebelum melakukan check-in.';
            document.getElementById('globalLocationWarning').style.color = 'red';
            return; // Hentikan eksekusi jika lokasi belum diisi
        } else 
        {
            document.getElementById('globalLocationWarning').textContent = '';
        }
        // Validasi reCAPTCHA
        if (!recaptchaTokenGlobal) {
            document.getElementById('globalCaptchaWarning').textContent = 'Captcha belum terverifikasi.';
            return;
        }

        document.getElementById('FooterglobalSubmitCheckin').classList.add('d-flex', 'justify-content-center');
        
        document.getElementById('globalSubmitCheckin').disabled = true;
        document.getElementById('globalSubmitCheckin').style.display = 'none';

        document.getElementById('globalCloseCheckin').disabled = true;
        document.getElementById('globalCloseCheckin').style.display = 'none';

        document.getElementById('FooterglobalSubmitCheckin').insertAdjacentHTML('beforeend', `
            <i class="fas fa-spinner fa-spin text-muted"></i> Data sedang diproses...
        `);

        let formData = new FormData();
        formData.append('latitude', latitude);
        formData.append('longitude', longitude);
        formData.append('recaptcha', recaptchaTokenGlobal);
        formData.append('source', "manual_checkin");
        formData.append('fcm_token', storedToken);
        formData.append('_method', 'PUT');

        if (photo) {
            formData.append('photo', photo);
        }

        let url = "{{ route('employee-checking.update', ':id') }}".replace(":id", id);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#globalCheckinPopup').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Check-in berhasil!',
                    timer: 4000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(function() {
                    location.reload();
                });

                $('#globalCheckinPopup').modal('hide');
            },
            error: function(xhr) {
                $('#globalCheckinPopup').modal('hide');

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal melakukan check-in',
                    text: xhr.responseText,
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(function() {
                    location.reload();
                });

                $('#globalCheckinPopup').modal('hide');
            }
        });
    }
</script>

<script>
    let currentFacingModeManual = 'environment'; // Default kamera belakang

    function compressAndPreviewImageCheckin() 
    {
        const fileInput = document.getElementById('globalPhoto');
        const preview = document.getElementById('globalPhotoPreview');

        if (!fileInput.files[0]) {
            preview.src = "";
            return;
        }

        const reader = new FileReader();
        reader.readAsDataURL(fileInput.files[0]);
        reader.onload = function (event) {
            const imgElement = document.createElement("img");
            imgElement.src = event.target.result;
            imgElement.onload = function (e) {
                const canvas = document.createElement("canvas");
                const MAX_WIDTH = 150;

                const scaleSize = MAX_WIDTH / e.target.width;
                canvas.width = MAX_WIDTH;
                canvas.height = e.target.height * scaleSize;

                const ctx = canvas.getContext("2d");
                ctx.drawImage(e.target, 0, 0, canvas.width, canvas.height);
                ctx.canvas.toBlob((blob) => {
                    const file = new File([blob], "compressed_image.jpg", {
                        type: 'image/jpeg',
                        quality: 0.8 // Lowering the quality to reduce file size
                    });

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;

                    const reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onloadend = function () {
                        preview.src = reader.result;
                        preview.style.display = 'block';
                    }
                }, 'image/jpeg', 0.6);
            }
        }
    }

    function getLocation() 
    {
        // Reset pesan peringatan lokasi
        const locationWarning = document.getElementById('globalLocationWarning');
        if (locationWarning) {
            locationWarning.textContent = '';
            locationWarning.style.color = '';
        }

        // Cek apakah browser mendukung geolocation
        if (navigator.geolocation) {
            // Minta izin pengguna untuk mendapatkan lokasi saat ini
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // Jika lokasi berhasil didapatkan
                    document.getElementById('globalLatitude').value = position.coords.latitude;
                    document.getElementById('globalLongitude').value = position.coords.longitude;
                    document.getElementById('globalLocationStatus').textContent = "Lokasi berhasil didapatkan!";
                    document.getElementById('globalLocationStatus').style.color = 'green';
                },
                function(error) {
                    // Menangani kesalahan saat mendapatkan lokasi
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            document.getElementById('globalLocationStatus').textContent = "Akses lokasi ditolak. Silakan aktifkan izin lokasi di browser Anda.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            document.getElementById('globalLocationStatus').textContent = "Informasi lokasi tidak tersedia.";
                            break;
                        case error.TIMEOUT:
                            document.getElementById('globalLocationStatus').textContent = "Permintaan lokasi melebihi batas waktu.";
                            break;
                        case error.UNKNOWN_ERROR:
                            document.getElementById('globalLocationStatus').textContent = "Terjadi kesalahan tidak diketahui.";
                            break;
                    }
                    document.getElementById('globalLocationStatus').style.color = 'red';
                }
            );
        } else {
            // Jika geolocation tidak didukung oleh browser
            document.getElementById('globalLocationStatus').textContent = "Geolocation tidak didukung oleh browser ini.";
            document.getElementById('globalLocationStatus').style.color = 'red';
        }
    }
    function openGlobalCamera() 
    {
        const video = document.getElementById('globalVideoFeed');
        
        // Request the video stream with the specified facing mode
        navigator.mediaDevices.getUserMedia({ video: { facingMode: currentFacingModeManual } })
            .then((stream) => {
                videoStream = stream; // Store the stream to manage it later
                video.srcObject = stream;
            })
            .catch((err) => {
                console.error("Error accessing camera: ", err);
                alert("Could not access camera. Please allow camera access.");
            });
    }

    function takeGlobalPhoto() 
    {
        const video = document.getElementById('globalVideoFeed');
        const canvas = document.getElementById('globalCanvas');
        const photoInput = document.getElementById('globalPhoto');
        const photoPreview = document.getElementById('globalPhotoPreview');
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob((blob) => {
            const file = new File([blob], "checkin_photo.jpg", { type: "image/jpeg" });

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            photoInput.files = dataTransfer.files;

            photoPreview.src = URL.createObjectURL(blob);
            photoPreview.style.display = 'block';
            video.style.display = 'none';
            document.getElementById('globalTakePhotoButton').style.display = 'none';

            video.srcObject.getTracks().forEach(track => track.stop());
        }, "image/jpeg", 0.7);
    }
    
    function toggleCamera() 
    {
        // Beralih antara kamera depan dan belakang
        currentFacingModeManual = currentFacingModeManual === 'environment' ? 'user' : 'environment';
        
        // Hentikan aliran video yang aktif sebelum beralih
        const video = document.getElementById('globalVideoFeed');
        if (video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
        }
        
        // Buka kamera dengan facingMode yang diperbarui
        openGlobalCamera();
    }

    // Automatically close the camera when the modal closes
    function closeCamera() 
    {
        const video = document.getElementById('globalVideoFeed');
        if (video.srcObject) 
        {
            // Stop all tracks of the video stream
            video.srcObject.getTracks().forEach(track => track.stop());
            
            // Release the video resource by setting srcObject to null
            video.srcObject = null;
        }
    }

    document.getElementById('globalCheckinPopup').addEventListener('hidden.bs.modal', closeCamera);
</script>
@endif
@stop
@section('css')
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
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