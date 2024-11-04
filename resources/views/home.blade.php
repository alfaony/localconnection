@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')

<div class="col-md-12 mt-2">
    @if(Session::get('updateProfile'))
    <div class="alert alert-success mt-3">Pengguna Berhasil Perbarui</div>
    @endif
</div>
@canAccess('showReport','homes')
<div class="card py-3">
    <div class="card-header">
        <h5>Laporan Overview Proyek</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Proyek Aktif</h5>
                        <p class="card-text">{{ $totalActiveProjects }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Pekerja Aktif</h5>
                        <p class="card-text">{{ $totalActiveWorkers }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Anggaran Pembelian</h5>
                        <p class="card-text">{{ 'Rp. '.number_format($totalPurchaseBudget,0,',','.') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Anggaran Proyek Aktif</h5>
                        <p class="card-text">{{ 'Rp. '.number_format($activeProjectsBudget,0,',','.') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Anggaran Pekerja</h5>
                        <p class="card-text">{{ 'Rp. '.number_format($activeEmployeeBudget,0,',','.') }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-4">
                <a href="{{ route('quote.index') }}">
                <div class="card text-white bg-warning mb-3 hover-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Quote</h5>
                        <p class="card-text">{{ $totalQuote }}</p>
                    </div>
                </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('work-order.index') }}">
                <div class="card text-white bg-warning mb-3 hover-card">
                    <div class="card-body">
                        <h5 class="card-title">Total SPK</h5>
                        <p class="card-text">{{ $totalWorkOrder }}</p>
                    </div>
                </div>
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="card-header">
            <h5> Quote Tanpa SPK </h5>
        </div>
        <!-- Add Search Form -->
        <form method="GET" action="{{ route('home') }}" class="mb-3">
            <div class="row mt-2 align-items-center">
                <div class="col-auto">
                    <input type="text" name="search_quote" class="form-control" placeholder="Cari No Quote">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Cari</button>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>No Quote</th>
                        <th>Total</th>
                        @canAccess('downloadPdf','quotes')
                        <th>Aksi</th>
                        @endcanAccess
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotesWithoutWorkOrder as $quote)
                    <tr>
                        <td>{{ $quote->number_result }}</td>
                        <td>Rp {{ number_format($quote->total, 0, ',', '.') }}</td>
                        @canAccess('downloadPdf','quotes')
                        <td>
                            <a href="{{ route('quote.download.pdf', $quote->slug) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-eye"></i> Quote
                            </a>
                        </td>
                        @endcanAccess
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">Tidak ada quotes tanpa WorkOrder.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $quotesWithoutWorkOrder->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>
@endcanAccess
@canAccess('showReportPointDaily','homes')
<div class="card py-3">
    <div class="card-header">
        <h5>Laporan Overview Pekerjaan Harian</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <!-- Date filters -->
                <form method="GET" action="{{ route('home') }}" class="mb-3">
                    <div class="mb-4 row">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Tanggal Mulai:</label>
                            <input type="date" class="form-control" name="start_date" id="start_date" value="{{ request('start_date') ?? $startDate->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="end_date" class="form-label">Tanggal Akhir:</label>
                            <input type="date" class="form-control" name="end_date" id="end_date" value="{{ request('end_date')  ?? $endDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-12 mt-2">
                            <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Cari</button>
                            <button type="button" onclick="window.location.href='{{ route('home') }}'" class="btn btn-secondary"><i class="fa fa-times"></i> Reset</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Poin Tugas</div>
                    <div class="card-body">
                        <p class="card-text">{{ $dailyTaskPoints }} Poin</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Poin Training</div>
                    <div class="card-body">
                        <p class="card-text">{{ $trainingPoints }} Poin</p>
                    </div>
                </div>
            </div>
            @if(Auth::user()->role->name == \App\Schemas\RoleSchema::SALES)
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Poin Penjualan</div>
                    <div class="card-body">
                        <p class="card-text">{{ $ipRightPoints }} Poin</p>
                    </div>
                </div>
            </div>
            @else
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Poin Hak Cipta</div>
                    <div class="card-body">
                        <p class="card-text">{{ $ipRightPoints }} Poin</p>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-md-3">
                <div class="card bg-success mb-3">
                    <div class="card-header">Jumlah Tugas Diselesaikan</div>
                    <div class="card-body">
                        <p class="card-text">{{ $dailyTaskCompleteCount }} Tugas</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Todo Card -->
            <div class="col-md-2 mb-4">
                <div class="card shadow-sm border-left-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title">Todo</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Jumlah Task: {{ $dailyTaskTodoCount }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Doing Card -->
            <div class="col-md-2 mb-4">
                <div class="card shadow-sm border-left-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title">Doing</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Jumlah Task: {{ $dailyTasDoingCount }}</p>
                    </div>
                </div>
            </div>

            <!-- In Review Card -->
            <div class="col-md-2 mb-4">
                <div class="card shadow-sm border-left-warning">
                    <div class="card-header bg-warning text-white">
                        <h5 class="card-title">In Review</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Jumlah Task: {{ $dailyTaskInreviewCount }}</p>
                    </div>
                </div>
            </div>

            <!-- Complete Card -->
            <div class="col-md-2 mb-4">
                <div class="card shadow-sm border-left-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title">Complete</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Jumlah Task: {{ $dailyTaskCompleteCount }}</p>
                    </div>
                </div>
            </div>

            <!-- Not Complete Card -->
            <div class="col-md-2 mb-4">
                <div class="card shadow-sm border-left-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title">Not Complete</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Jumlah Task: {{ $dailyTaskNotComplateCount }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card py-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-light mb-3">
                    <div class="card-header text-danger">Jumlah Tugas Overdue</div>
                    <div class="card-body">
                        <p class="card-text">{{ $dailyTaskCountOverdue }} Tugas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light mb-3">
                    <div class="card-header text-primary">Jumlah Hari Ini</div>
                    <div class="card-body">
                        <p class="card-text">{{ $dailyTaskCountToday }} Tugas</p>
                    </div>
                </div>
            </div>
            <div class="col-md3">
                <div class="card bg-light mb-3">
                    <div class="card-header text-green">Jumlah Tugas Mendatang</div>
                    <div class="card-body">
                        <p class="card-text">{{ $dailyTaskCountUpcoming }} Tugas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endcanAccess


@canAccess('showScheduleOb','homes')
<div class="card py-3">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>
@endcanAccess

@if(Auth::user()->role->name == \App\Schemas\RoleSchema::BM)
<div class="row">
    <div class="py-4">
        <h2>Perlengkapan Stok Habis</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Perlengkapan</th>
                        <th>Kode Perlengkapan</th>
                        <th>Stok Tersedia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($equipments as $equipment)
                        <tr>
                            <td>{{ $equipment->name }}</td>
                            <td>{{ $equipment->code }}</td>
                            <td>{{ $equipment->total_stock }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada data stok habis.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.7.2/main.min.js"></script>
<script>
    $(document).ready(function () {
        $('input[name="start_date"]').on('change', function() {
            var startDateValue = $(this).val();
            $('input[name="end_date"]').val(startDateValue);
        });
    });
</script>
@canAccess('showScheduleOb','homes')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: [
                @foreach($schedules as $schedule)
                {
                    title: '{{ $schedule->user->name }} - {{ $schedule->shiftingOb->name }}',
                    start: '{{ $schedule->date }}',
                    description: `
                        <b>User:</b> {{ $schedule->user->name }}<br>
                        <b>Shift:</b> {{ $schedule->shiftingOb->name }}<br>
                        <b>Clock In:</b> {{ $schedule->shiftingOb->clock_in }}<br>
                        <b>Clock Out:</b> {{ $schedule->shiftingOb->clock_out }}<br>
                        <b>Real Clock In:</b> {{ $schedule->attendance ? $schedule->attendance->clock_in : '-' }}<br>
                        <b>Real Clock Out:</b> {{ $schedule->attendance ? $schedule->attendance->clock_out : '-' }}<br>
                    `,
                    id: '{{ $schedule->id }}',
                    extendedProps: {
                        user_id: '{{ $schedule->user_id }}',
                        shifting_ob_id: '{{ $schedule->shifting_ob_id }}'
                    }
                },
                @endforeach
            ],
            eventDidMount: function(info) {
                new bootstrap.Tooltip(info.el, {
                    title: info.event.extendedProps.description,
                    html: true,
                    container: 'body'
                });
            }
        });

        calendar.render();
    });
</script>
@endcanAccess
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cek apakah token FCM di localStorage sama dengan yang ada di server
        const savedToken = localStorage.getItem('fcm_token');


        // Jika token tidak sama atau tidak ada, lakukan registrasi FCM
        // if (savedToken !== serverToken) {
        // }
        initFirebaseMessagingRegistration();

        // Tambahkan listener untuk memantau interaksi pertama pengguna
        document.addEventListener('click', triggerNotificationRequestOnInteraction);
        document.addEventListener('keydown', triggerNotificationRequestOnInteraction);
        document.addEventListener('scroll', triggerNotificationRequestOnInteraction);
    });

    function getBrowserName() 
    {
        const agent = navigator.userAgent.toLowerCase();
        if (agent.indexOf("firefox") > -1) {
            return "Mozilla Firefox";
        } else if (agent.indexOf("safari") > -1 && agent.indexOf("chrome") === -1) {
            return "Safari";
        } else if (agent.indexOf("chrome") > -1) {
            return "Google Chrome";
        } else if (agent.indexOf("edge") > -1) {
            return "Microsoft Edge";
        } else if (agent.indexOf("opera") > -1 || agent.indexOf("opr") > -1) {
            return "Opera";
        } else {
            return "Browser tidak dikenal";
        }
    }

    function initFirebaseMessagingRegistration() 
    {
        const storedToken = localStorage.getItem('fcm_token');
        const messaging = firebase.messaging();
        
        // if (storedToken) {
        //     console.log("Token yang tersimpan:", storedToken);
            
        // } else {
            if (Notification.permission === 'granted') {
                // Jika izin sudah diberikan, ambil token FCM
                messaging.getToken({ vapidKey: "{{ config('services.firebase.vapid_key') }}" })
                    .then((token) => {
                        if (token) {
                            // Simpan token di Local Storage
                            localStorage.setItem('fcm_token', token);
                            sendTokenToServer(token);
                        } else {
                            console.log('No registration token available. Request permission to generate one.');
                        }
                    })
                    .catch((err) => {
                        console.error('An error occurred while retrieving token. ', err);
                    });
            } else if (Notification.permission === 'default') {
                // Panggil fungsi untuk meminta izin notifikasi
                requestNotificationPermission();
            } else {
                alert("Notifikasi telah diblokir di pengaturan browser. Silakan aktifkan notifikasi di pengaturan untuk menerima pemberitahuan.");
            }
    //     }
    }

    function requestNotificationPermission() {
        Notification.requestPermission().then(function(permission) {
            console.log('Notification permission status:', permission);
            if (permission === 'granted') {
                // Jika pengguna memberikan izin, inisialisasi FCM
                initFirebaseMessagingRegistration();
            } else if (permission === 'denied') 
            {
                console.log("Notifikasi ditolak. Silakan aktifkan notifikasi di pengaturan browser untuk menerima pemberitahuan.");
            }
        }).catch(function(err) {
            console.error('Failed to get notification permission:', err);
        });
    }

    function sendTokenToServer(token) {
        const browserName = getBrowserName();

        $.ajax({
            url: "{{ route('user.updatefcm') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                token: token,
                browser_name: browserName
            },
            success: function(response) {
                console.log("Token berhasil disimpan.");
            },
            error: function(err) {
                console.log($(err).text());
                
                console.error("Gagal menyimpan token:", err);
            }
        });
    }

    // Fungsi untuk memantau interaksi pertama pengguna
    function triggerNotificationRequestOnInteraction() {
        if (Notification.permission === 'default') 
        {
            // Permintaan izin notifikasi akan muncul pada interaksi pertama
            requestNotificationPermission();

            // Hapus event listener setelah permintaan dikirim
            document.removeEventListener('click', triggerNotificationRequestOnInteraction);
            document.removeEventListener('keydown', triggerNotificationRequestOnInteraction);
            document.removeEventListener('scroll', triggerNotificationRequestOnInteraction);
        }
    }

    // Memantau interaksi pengguna di halaman (scroll, klik, atau ketik)

    // window.onload = function() {
    //     // Cek status izin notifikasi
    //     initFirebaseMessagingRegistration();

    //     // Tambahkan listener untuk memantau interaksi pertama pengguna
    //     document.addEventListener('click', triggerNotificationRequestOnInteraction);
    //     document.addEventListener('keydown', triggerNotificationRequestOnInteraction);
    //     document.addEventListener('scroll', triggerNotificationRequestOnInteraction);
    // };
</script>

@stop

@section('css')
<link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.7.2/main.min.css" rel="stylesheet">
<style>
    .card-header {
        font-weight: bold;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .form-label {
        font-weight: bold;
    }
</style>
<style>
    .hover-card {
        transition: transform 0.3s ease, background-color 0.3s ease;
    }

    .hover-card:hover {
        background-color: #ffcf63 !important; /* Warna hover */
        transform: scale(1.05); /* Efek zoom */
    }

    .hover-card .card-title,
    .hover-card .card-text {
        transition: color 0.3s ease;
    }

    .hover-card:hover .card-title,
    .hover-card:hover .card-text {
        color: #000000; /* Warna teks saat hover */
    }
</style>

@endsection
