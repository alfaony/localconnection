@extends('adminlte::page')

@section('title', 'Detail Pengajuan Rapat')

@section('content_header')
    <h1 class="m-0 text-dark">Detail Pengajuan Rapat</h1>
@stop

@section('content')
   @include('components.alert')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('meeting.index') }}">Daftar Rapat</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $meeting->meeting_name }}</li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-check mr-2"></i>Detail Pengajuan Rapat
                    </h3>
                    @canAccess('update','meetings')
                    <div class="card-tools">
                        <a href="{{ route('meeting.edit', $meeting->slug) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                    </div>
                    @endcanAccess
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Informasi Utama -->
                            <div class="info-box bg-light mb-4 p-3 border rounded">
                                <div class="info-box-content">
                                    <h3 class="info-box-text font-weight-bold text-primary mb-1">{{ $meeting->meeting_name }}</h3>
                                    <span class="info-box-number mb-2">
                                        <i class="fas fa-tag mr-1 text-muted"></i>
                                        {{ $meeting->meeting_type == 'online' ? 'Rapat Online' : 'Rapat Offline' }}
                                    </span>
                                    <div class="text-muted" style="max-height: 50vh; overflow-y: auto;">
                                        <i class="fas fa-clipboard-list mr-1"></i>
                                        {!! $meeting->meeting_agenda !!}
                                    </div>
                                </div>
                            </div>

                            <!-- Detail Waktu -->
                            <div class="card card-outline card-primary mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="far fa-clock mr-2"></i>Waktu Rapat
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="border-bottom pb-2 mb-2">
                                                <div class="text-muted small">Tanggal Mulai</div>
                                                <div class="font-weight-bold">
                                                    <i class="far fa-calendar mr-1 text-primary"></i>
                                                    {{ \Carbon\Carbon::parse($meeting->start_date)->locale('id_ID')->translatedFormat('l, j F Y') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border-bottom pb-2 mb-2">
                                                <div class="text-muted small">Tanggal Berakhir</div>
                                                <div class="font-weight-bold">
                                                    <i class="far fa-calendar mr-1 text-primary"></i>
                                                    {{ \Carbon\Carbon::parse($meeting->end_date)->locale('id_ID')->translatedFormat('l, d F Y') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border-bottom pb-2 mb-2">
                                                <div class="text-muted small">Jam Mulai</div>
                                                <div class="font-weight-bold">
                                                    <i class="far fa-clock mr-1 text-primary"></i>
                                                    {{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border-bottom pb-2 mb-2">
                                                <div class="text-muted small">Jam Berakhir</div>
                                                <div class="font-weight-bold">
                                                    <i class="far fa-clock mr-1 text-primary"></i>
                                                    {{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <div class="text-muted small">Durasi</div>
                                            <div class="font-weight-bold">
                                                <i class="fas fa-hourglass-half mr-1 text-primary"></i>

                                                <span class="text-muted">({{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }})</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Catatan -->
                            @if($meeting->catatan)
                            <div class="card card-outline card-info mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-sticky-note mr-2"></i>Catatan
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <p>{{ $meeting->catatan }}</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <!-- Status & Info -->
                            <div class="card card-outline card-success mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-info-circle mr-2"></i>Informasi
                                    </h3>
                                </div>
                                <div class="card-body">
                                    {{-- 
                                    <div class="mb-3">
                                        <div class="text-muted small">Status</div>
                                        <div>
                                            @if($meeting->status == 'diproses')
                                                <span class="badge bg-warning p-2">Diproses</span>
                                            @elseif($meeting->status == 'disetujui')
                                                <span class="badge bg-success p-2">Disetujui</span>
                                            @elseif($meeting->status == 'ditolak')
                                                <span class="badge bg-danger p-2">Ditolak</span>
                                            @elseif($meeting->status == 'selesai')
                                                <span class="badge bg-info p-2">Selesai</span>
                                            @else
                                                <span class="badge bg-secondary p-2">Belum Diproses</span>
                                            @endif
                                        </div>
                                    </div>
                                    --}}
                                     @if($meeting->attachment)
                                    <div class="mb-3">
                                        <div class="text-muted small">Lampiran</div>
                                        <div>
                                            <i class="fas fa-paperclip mr-1"></i>
                                            <a href="{{ url('storage/' . $meeting->attachment) }}" target="_blank">Lampiran</a>
                                            
                                        </div>
                                    </div>
                                    @endif
                                    @if($meeting->project)
                                    <div class="mb-3">
                                        <div class="text-muted small">
                                            Proyek
                                        </div>
                                        <div>
                                            <i class="fas fa-project-diagram mr-1"></i>
                                            @if(App\Helpers\Access::can('show','projects'))
                                            <a href="{{ route('project.show', $meeting->project->slug) }}" target="_blank">{{ $meeting->project->title }}</a>
                                            @else
                                            {{ $meeting->project->title }}
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <div class="mb-3">
                                        <div class="text-muted small">Dibuat Pada</div>
                                        <div>
                                            <i class="far fa-calendar mr-1"></i>
                                            {{ \Carbon\Carbon::parse($meeting->created_at)->translatedFormat('d F Y H:i') }}
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="text-muted small">Terakhir Diperbarui</div>
                                        <div>
                                            <i class="far fa-calendar mr-1"></i>
                                            {{ \Carbon\Carbon::parse($meeting->updated_at)->translatedFormat('d F Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tempat Rapat -->
                            <div class="card card-outline card-purple mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        @if($meeting->meeting_type == 'offline')
                                            <i class="fas fa-building mr-2"></i>Tempat Rapat
                                        @else
                                            <i class="fas fa-video mr-2"></i>Rapat Online
                                        @endif
                                    </h3>
                                </div>
                                <div class="card-body">
                                    @if($meeting->meeting_type != 'offline')
                                        <div class="mb-2">
                                            <div class="text-muted small">Link Meet</div>
                                            @if($meeting->google_meet_link)
                                                <a href="{{ $meeting->google_meet_link }}" target="_blank" class="font-weight-bold text-primary">
                                                    <i class="fas fa-external-link-alt mr-1"></i>
                                                    {{ $meeting->google_meet_link }}
                                                </a>
                                            @else
                                                <span class="text-muted">Tidak tersedia</span>
                                            @endif
                                        </div>
                                        
                                        @if($meeting->google_event_id)
                                        <div class="mb-2">
                                            <div class="text-muted small">ID Google Event</div>
                                            <div class="font-weight-bold">
                                                {{ $meeting->google_event_id ?? 'Tidak tersedia' }}
                                            </div>
                                        </div>
                                        @endif

                                        @if($meeting->public_token && $meeting->public_token_generated_at)
                                        <div class="mb-3">
                                            <div class="text-muted small">Link Share</div>
                                            <div class="font-weight-bold">
                                                <a href="{{ route('meeting.public.join', ['slug' => $meeting->slug, 'token' => $meeting->public_token]) }}" target="_blank" class="text-primary">
                                                    <i class="fas fa-share-alt mr-1"></i>Shareable Link
                                                </a>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="text-muted small">Kode Public</div>
                                            <div class="font-weight-bold">
                                                {{ $meeting->public_code ?? 'Tidak tersedia' }}
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <button class="btn btn-sm btn-outline-primary" onclick="copyMeetingInfo()">Copy Info</button>
                                        </div>
                                        @endif
                                    @else
                                    
                                        <div class="font-weight-bold">
                                            <i class="fas fa-map-marker-alt mr-1 text-purple"></i>
                                            {{ $meeting->meeting_location ?? 'Belum ditentukan' }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- PIC -->
                            <div class="card card-outline card-indigo mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-user-tie mr-2"></i>Penanggung Jawab (PIC)
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="font-weight-bold">{{ $meeting->user->name }}</div>
                                    <div class="text-muted small">{{ $meeting->user->email }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Peserta Rapat -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-users mr-2"></i>Peserta Rapat
                                    </h3>
                                    <div class="card-tools">
                                        <span class="badge badge-info p-2">
                                            {{ count($meeting->combined_participants ?? []) }} Peserta
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th>Nama Peserta</th>
                                                    <th>Email</th>
                                                    <th>Join</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($meeting->combined_participants ?? [] as $index => $participant)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="symbol symbol-40 symbol-light mr-3">
                                                                    <span class="symbol-label bg-info text-white font-weight-bold">
                                                                        {{ strtoupper(substr($participant['name'], 0, 1)) }}
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <div class="font-weight-bold">{{ $participant['name'] }}</div>
                                                                    <div class="text-muted small">
                                                                        {{ optional(\App\Models\User::find($participant['id']))->name ?? 'Eksternal' }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            {{ filter_var($participant['id'], FILTER_VALIDATE_EMAIL) 
                                                                ? $participant['id'] 
                                                                : (\App\Models\User::find($participant['id'])->email ?? '-') }}
                                                        </td>
                                                        <td>
                                                            @canAccess('join', 'meetings')
                                                            @if($participant['status'] == App\Schemas\ParamSchema::INTERNAL && $participant['id'] == auth()->user()->id && !$participant['is_attended'] && $meeting->is_active)
                                                                @if($meeting->is_already)
                                                                <button class="btn btn-success btn-sm" onclick="joinMeeting('{{ auth()->user()->id }}')">
                                                                    <i class="fas fa-sign-in-alt"></i> Bergabung
                                                                </button>
                                                                @else
                                                                <span class="badge bg-warning text-dark mt-1">Segera Dimulai</span>
                                                                @endif
                                                            @elseif($participant['status'] == App\Schemas\ParamSchema::INTERNAL)
                                                                @if($participant['is_attended'])
                                                                <span class="badge badge-success">Hadir</span>
                                                                <br/><small class="text-muted"> Bergabung pada {{ \Carbon\Carbon::parse($participant['join_time'])->format('d-m-Y H:i') }}</small>
                                                                @else
                                                                <span class="badge badge-danger">{{ $meeting->is_active ? 'Belum Hadir' : 'Tidak Hadir' }}</span>
                                                                @endif
                                                            @endif
                                                            @endcanAccess
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">Tidak ada peserta</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    @if($meeting->status == 'diproses')
                        <a href="{{ route('meeting.edit', $meeting->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit mr-1"></i>Edit Pengajuan
                        </a>
                    @endif
                    
                    {{--
                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                        @if($meeting->status == 'diproses')
                            <button class="btn btn-success" data-toggle="modal" data-target="#approveModal">
                                <i class="fas fa-check-circle mr-1"></i>Setujui
                            </button>
                            <button class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times-circle mr-1"></i>Tolak
                            </button>
                        @endif
                    @endif
                    
                    @if($meeting->status == 'disetujui' && $meeting->isUpcoming())
                        <a href="#" class="btn btn-info">
                            <i class="fas fa-calendar-plus mr-1"></i>Tambahkan ke Kalender
                        </a>
                    @endif
                    --}}
                </div>
            </div>
        </div>
    </div>

    {{--
    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="approveModalLabel">
                        <i class="fas fa-check-circle mr-2"></i>Setujui Pengajuan Rapat
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('meeting.approve', $meeting->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Anda yakin ingin menyetujui pengajuan rapat ini?</p>
                        <div class="form-group">
                            <label for="approvalNotes">Catatan (Opsional)</label>
                            <textarea class="form-control" id="approvalNotes" name="notes" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Setujui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel">
                        <i class="fas fa-times-circle mr-2"></i>Tolak Pengajuan Rapat
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('meeting.reject', $meeting->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Anda yakin ingin menolak pengajuan rapat ini?</p>
                        <div class="form-group">
                            <label for="rejectReason">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectReason" name="reason" rows="3" required placeholder="Berikan alasan penolakan"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    --}}
@stop
@section('js')

@canAccess('join','meetings')
<script>
    function joinMeeting(userId) 
    {   
        let meetingId = "{{ $meeting->id }}";

        $.ajax({
            url: '{{ route("meeting.join") }}',
            method: 'POST',
            data: {
                meeting_id: meetingId,
                user_id: userId,
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                toastr.info('Processing...');
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Berhasil hadir.');
                    
                    if (response.redirect_url) {
                        setTimeout(() => 
                        {
                            window.location.href = response.redirect_url;
                        }, 1000);
                    } else {
                        setTimeout(() => location.reload(), 1000);
                    }
                } else {
                    toastr.error(response.message || 'Gagal mencatat kehadiran.');
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON.message || 'Terjadi kesalahan.');
            }
        });
    }
</script>
@endcanAccess

@if($meeting->slug && $meeting->public_token)
<script>
function copyMeetingInfo() {
    const name = @json($meeting->meeting_name);
    const link = @json(route('meeting.public.join', ['slug' => $meeting->slug, 'token' => $meeting->public_token]));
    const code = @json($meeting->public_code)

    const text = `${name}\nLink: ${link}\nKode: ${code}`;

    navigator.clipboard.writeText(text).then(function () {
        toastr.success('Informasi meeting berhasil disalin!');
    }, function () {
        toastr.error('Gagal menyalin teks.');
    });
}
</script>
@endif
@endsection

@section('css')
    <style>
        .info-box {
            transition: all 0.3s ease;
            border-left: 4px solid #3c8dbc;
        }
        
        .info-box:hover {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .symbol {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        
        .symbol-label {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            font-size: 1.2rem;
        }
        
        .card-outline {
            border-top: 3px solid !important;
        }
        
        .card-outline.card-primary {
            border-top-color: #3c8dbc !important;
        }
        
        .card-outline.card-success {
            border-top-color: #28a745 !important;
        }
        
        .card-outline.card-info {
            border-top-color: #17a2b8 !important;
        }
        
        .card-outline.card-purple {
            border-top-color: #6f42c1 !important;
        }
        
        .card-outline.card-indigo {
            border-top-color: #6610f2 !important;
        }
        
        .table thead th {
            border-top: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            color: #495057;
            background-color: #f8f9fa;
        }
        
        .list-group-item {
            border-left: none;
            border-right: none;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.5em 0.8em;
            border-radius: 20px;
        }
    </style>
@stop