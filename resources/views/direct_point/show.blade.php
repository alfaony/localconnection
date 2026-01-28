@extends('adminlte::page')

@section('content_header')
    <h1>Detail Direct Point</h1>
@stop

@section('content')
<div class="container-fluid">
    @include('components.alert')
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-gift"></i> Detail Direct Point
                        {!! $directPoint->status_badge !!}
                    </h4>
                </div>
                <div class="card-body">
                    {{-- Main Info --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">
                                        <i class="fas fa-user"></i> Dari
                                    </h6>
                                    <h5 class="mb-0">{{ $directPoint->fromUser->name }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">
                                        <i class="fas fa-user-check"></i> Kepada
                                    </h6>
                                    <h5 class="mb-0">{{ $directPoint->toUser->name }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Point & Division --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Jumlah Point</h6>
                                    
                                    @if($directPoint->approved_point && $directPoint->approved_point != $directPoint->point)
                                        {{-- Show both requested and approved when different --}}
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Diminta:</small>
                                            <h4 class="mb-0 text-muted">
                                                <i class="fas fa-coins"></i> <del>{{ $directPoint->point }}</del>
                                            </h4>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Disetujui:</small>
                                            <h2 class="mb-0 text-success">
                                                <i class="fas fa-check-circle"></i> {{ $directPoint->approved_point }}
                                            </h2>
                                        </div>
                                    @elseif($directPoint->isApproved() && $directPoint->approved_point)
                                        {{-- Approved with same amount --}}
                                        <h2 class="mb-0 text-success">
                                            <i class="fas fa-coins"></i> {{ $directPoint->approved_point }}
                                        </h2>
                                    @else
                                        {{-- Pending or rejected --}}
                                        <h2 class="mb-0 text-info">
                                            <i class="fas fa-coins"></i> {{ $directPoint->point }}
                                        </h2>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">
                                        <i class="fas fa-building"></i> Divisi Sumber
                                    </h6>
                                    <h5 class="mb-0">{{ $directPoint->division->name }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Reason --}}
                    @if($directPoint->reason)
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-comment-dots"></i> Alasan Pemberian
                                </h6>
                                <p class="mb-0">{{ $directPoint->reason }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Timeline --}}
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fas fa-history"></i> Timeline</h6>
                        </div>
                        <div class="card-body">
                            <ul class="timeline">
                                <li class="timeline-item">
                                    <i class="fas fa-plus-circle bg-primary"></i>
                                    <div class="timeline-content">
                                        <h6>Direct Point Dibuat</h6>
                                        <small class="text-muted">
                                            {{ $directPoint->created_at->format('d M Y, H:i') }}
                                            ({{ $directPoint->created_at->diffForHumans() }})
                                        </small>
                                    </div>
                                </li>

                                @if($directPoint->isApproved() || $directPoint->isRejected())
                                    <li class="timeline-item">
                                        <i class="fas {{ $directPoint->isApproved() ? 'fa-check-circle bg-success' : 'fa-times-circle bg-danger' }}"></i>
                                        <div class="timeline-content">
                                            <h6>{{ $directPoint->isApproved() ? 'Disetujui' : 'Ditolak' }}</h6>
                                            <small class="text-muted">
                                                Oleh: {{ $directPoint->approvedBy->name ?? '-' }}<br>
                                                {{ $directPoint->approved_at ? $directPoint->approved_at->format('d M Y, H:i') : '-' }}
                                                @if($directPoint->approved_at)
                                                    ({{ $directPoint->approved_at->diffForHumans() }})
                                                @endif
                                            </small>
                                            @if($directPoint->isRejected() && $directPoint->rejection_reason)
                                                <div class="alert alert-danger mt-2 mb-0">
                                                    <strong>Alasan:</strong> {{ $directPoint->rejection_reason }}
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    {{-- Action buttons for pending status --}}
                    @if($directPoint->isPending() && $canApprove)
                        <div class="card bg-light mt-4">
                            <div class="card-body">
                                <h6 class="mb-3"><i class="fas fa-tasks"></i> Aksi Approval</h6>
                                
                                @canAccess('checkQuota','direct_points')
                                <form action="{{ route('direct-point.approve', $directPoint->id) }}" method="POST" id="approveForm" onsubmit="return confirmApproval()">
                                    @csrf
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="small font-weight-bold">Point Diminta</label>
                                            <input type="text" class="form-control" id="requested_point" value="{{ $directPoint->point }}" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small font-weight-bold">Point Disetujui <span class="text-danger">*</span></label>
                                            <input type="number" 
                                                   name="approved_point" 
                                                   id="approved_point"
                                                   class="form-control @error('approved_point') is-invalid @enderror" 
                                                   value="{{ old('approved_point', $directPoint->point) }}"
                                                   min="1"
                                                   required>
                                            @error('approved_point')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">Bisa lebih dari yang diminta</small>
                                        </div>
                                    </div>

                                    <div id="quota-feedback" class="mb-3"></div>

                                    <div class="btn-group w-100" role="group">
                                        @canAccess('approve','direct_points')
                                        <button type="submit" class="btn btn-success mr-2 mb-1" id="approveBtn">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        @endcanAccess
                                        @canAccess('reject','direct_points')
                                        <button type="button" class="btn btn-danger mb-1" data-toggle="modal" data-target="#rejectModal">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                        @endcanAccess
                                    </div>
                                </form>
                                
                                <script>
                                function confirmApproval() {
                                    // Prevent default
                                    event.preventDefault();
                                    
                                    const requestedPoint = parseInt(document.getElementById('requested_point').value);
                                    const approvedPoint = parseInt(document.getElementById('approved_point').value);
                                    
                                    if (!approvedPoint || approvedPoint < 1) {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Point Tidak Valid',
                                            text: 'Mohon masukkan jumlah point yang akan disetujui!',
                                            confirmButtonColor: '#3085d6'
                                        });
                                        return false;
                                    }
                                    
                                    // Determine point difference status
                                    let pointStatus = '';
                                    let pointStatusColor = '#17a2b8';
                                    
                                    if (approvedPoint < requestedPoint) {
                                        pointStatus = `<div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 10px 0;">
                                            <strong>⚠️ PERHATIAN:</strong> Point disetujui <strong>LEBIH KECIL</strong> dari yang diminta!
                                        </div>`;
                                    } else if (approvedPoint > requestedPoint) {
                                        pointStatus = `<div style="background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 10px; margin: 10px 0;">
                                            <strong>ℹ️ INFO:</strong> Point disetujui <strong>LEBIH BESAR</strong> dari yang diminta.
                                        </div>`;
                                    }
                                    
                                    let htmlMessage = `
                                        <div style="text-align: left; padding: 10px;">
                                            <table style="width: 100%; margin-bottom: 15px;">
                                                <tr>
                                                    <td style="padding: 8px 0;"><strong>Point Diminta:</strong></td>
                                                    <td style="padding: 8px 0;"><span style="font-size: 1.2em; color: #6c757d;">${requestedPoint}</span></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 8px 0;"><strong>Point Disetujui:</strong></td>
                                                    <td style="padding: 8px 0;"><span style="font-size: 1.4em; color: #28a745; font-weight: bold;">${approvedPoint}</span></td>
                                                </tr>
                                            </table>
                                            
                                            ${pointStatus}
                                            
                                            <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 12px; margin-top: 15px;">
                                                <strong>🔒 PERINGATAN PENTING:</strong>
                                                <ul style="margin: 10px 0 0 20px; text-align: left;">
                                                    <li>Data <strong>TIDAK DAPAT DIEDIT</strong> setelah disetujui</li>
                                                    <li>Point akan <strong>LANGSUNG MASUK</strong> ke penerima</li>
                                                    <li>Quota divisi akan <strong>LANGSUNG TERPOTONG</strong></li>
                                                    <li>Pastikan jumlah point sudah <strong>BENAR!</strong></li>
                                                </ul>
                                            </div>
                                        </div>
                                    `;
                                    
                                    Swal.fire({
                                        title: 'Konfirmasi Approval Direct Point',
                                        html: htmlMessage,
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#28a745',
                                        cancelButtonColor: '#6c757d',
                                        confirmButtonText: `<i class="fas fa-check"></i> Ya, Setujui ${approvedPoint} Point`,
                                        cancelButtonText: '<i class="fas fa-times"></i> Batal',
                                        width: '600px',
                                        customClass: {
                                            confirmButton: 'btn btn-success btn-lg',
                                            cancelButton: 'btn btn-secondary btn-lg'
                                        },
                                        buttonsStyling: false
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // Submit the form
                                            document.getElementById('approveForm').submit();
                                        }
                                    });
                                    
                                    return false;
                                }
                                </script>
                                @endcanAccess
                            </div>
                        </div>

                        {{-- Reject Modal --}}
                        <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form action="{{ route('direct-point.reject', $directPoint->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">
                                                <i class="fas fa-times-circle"></i> Tolak Direct Point
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                                                <textarea name="rejection_reason" class="form-control" rows="4" 
                                                          placeholder="Jelaskan alasan penolakan..." required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                <i class="fas fa-times"></i> Batal
                                            </button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-check"></i> Tolak Point Ini
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('direct-point.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>

        {{-- Sidebar Info --}}
        <div class="col-md-4">
            @if($directPoint->isPending() && $canApprove)
                {{-- Quota Info Panel --}}
                <div class="card shadow-sm mb-3 sticky-top" style="top: 20px;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Informasi Quota</h5>
                    </div>
                    <div class="card-body" id="quota-info-panel">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                            <p>Memuat informasi quota...</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($directPoint->divisionQuotaLock)
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar-alt"></i> Periode Quota
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="mb-0">
                            {{ \Carbon\Carbon::create()->month($directPoint->divisionQuotaLock->month)->translatedFormat('F') }} 
                            {{ $directPoint->divisionQuotaLock->year }}
                        </h3>
                        <small class="text-muted">Bulan quota digunakan</small>
                    </div>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle"></i> Informasi Tambahan
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 small">ID</dt>
                        <dd class="col-sm-7 small"><code>{{ Str::substr($directPoint->id, 0, 8) }}...</code></dd>

                        <dt class="col-sm-5 small">Dibuat</dt>
                        <dd class="col-sm-7 small">{{ $directPoint->created_at->format('d M Y, H:i') }}</dd>

                        @if($directPoint->updated_at != $directPoint->created_at)
                            <dt class="col-sm-5 small">Diupdate</dt>
                            <dd class="col-sm-7 small">{{ $directPoint->updated_at->format('d M Y, H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .timeline {
        list-style: none;
        padding: 0 0 0 20px;
        position: relative;
    }
    .timeline:before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 2px;
        height: 100%;
        background: #dee2e6;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
        padding-left: 30px;
    }
    .timeline-item i {
        position: absolute;
        left: -10px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }
    .timeline-content h6 {
        margin-bottom: 5px;
        font-weight: 600;
    }
    .sticky-top {
        z-index: 1020;
    }
</style>
@stop

@section('js')
@if($directPoint->isPending() && $canApprove)
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@canAccess('checkQuota','direct_points')
<script>
    $(document).ready(function() {
        let checkingQuota = false;
        let currentQuotaData = null;

        // Update quota panel like in create page
        function updateQuotaPanel(data, approvedPoint = 0) {
            if (!data) {
                $('#quota-info-panel').html(`
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-chart-pie fa-3x mb-3"></i>
                        <p>Data quota tidak tersedia</p>
                    </div>
                `);
                return;
            }

            const { quota, used, remaining, task_used, direct_point_used } = data;
            
            // Calculate remaining AFTER approval
            const remainingAfterApproval = remaining - approvedPoint;
            const totalUsedAfterApproval = used + approvedPoint;
            const usedPercentage = quota > 0 ? (totalUsedAfterApproval / quota * 100).toFixed(1) : 0;

            let progressColor = 'success';
            if (usedPercentage > 80) progressColor = 'danger';
            else if (usedPercentage > 60) progressColor = 'warning';

            // Color for remaining display
            let remainingColor = 'text-success';
            if (remainingAfterApproval < 0) {
                remainingColor = 'text-danger';
            } else if (remainingAfterApproval < quota * 0.2) {
                remainingColor = 'text-warning';
            }

            $('#quota-info-panel').html(`
                <div class="text-center mb-3">
                    <h2 class="mb-0 ${remainingColor}">${remainingAfterApproval.toLocaleString()}</h2>
                    <small class="text-muted">Point Tersisa ${approvedPoint > 0 ? '(Setelah Approval)' : ''}</small>
                </div>

                <div class="progress mb-3" style="height: 25px;">
                    <div class="progress-bar bg-${progressColor}" role="progressbar" 
                        style="width: ${Math.min(usedPercentage, 100)}%" 
                        aria-valuenow="${totalUsedAfterApproval}" aria-valuemin="0" aria-valuemax="${quota}">
                        ${usedPercentage}%
                    </div>
                </div>

                <div class="row text-center mb-2">
                    <div class="col-6">
                        <div class="border-right">
                            <h4 class="mb-0 text-primary">${quota.toLocaleString()}</h4>
                            <small class="text-muted">Total Quota</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="mb-0 text-secondary">${used.toLocaleString()}</h4>
                        <small class="text-muted">Terpakai Saat Ini</small>
                    </div>
                </div>

                <hr>

                <div class="small">
                    <i class="fas fa-info-circle text-info"></i>
                    <strong>Breakdown Usage:</strong>
                    <div class="ml-3 mt-1">
                        <div class="d-flex justify-content-between">
                            <span>• Task Points:</span>
                            <span class="font-weight-bold">${(task_used || 0).toLocaleString()}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>• Direct Points:</span>
                            <span class="font-weight-bold">${(direct_point_used || 0).toLocaleString()}</span>
                        </div>
                        ${approvedPoint > 0 ? `
                        <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                            <span>• <strong>Akan Digunakan:</strong></span>
                            <span class="font-weight-bold text-warning">${approvedPoint.toLocaleString()}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `);
        }

        // Check quota with debounce
        function checkQuota() {
            const approvedPoint = parseInt($('#approved_point').val()) || 0;

            if (checkingQuota) return;
            checkingQuota = true;

            $.ajax({
                url: '{{ route("direct-point.check-quota") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    division_id: '{{ $directPoint->division_id }}',
                    point: approvedPoint
                },
                success: function(response) {
                    if (response.success) {
                        currentQuotaData = response;
                        // Pass approvedPoint so panel shows remaining AFTER approval
                        updateQuotaPanel(response, approvedPoint);

                        const { remaining, is_sufficient } = response;
                        const requestedPoint = {{ $directPoint->point }};
                        const remainingAfterApproval = remaining - approvedPoint;

                        if (!approvedPoint || approvedPoint < 1) {
                            $('#quota-feedback').html('');
                            $('#approveBtn').prop('disabled', false);
                        } else if (is_sufficient) {
                            let message = `<i class="fas fa-check-circle"></i> <strong>Quota mencukupi!</strong> Tersisa ${remainingAfterApproval.toLocaleString()} point setelah approval.`;
                            
                            if (approvedPoint > requestedPoint) {
                                message += `<br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Anda akan approve <strong>LEBIH</strong> dari yang diminta (${requestedPoint.toLocaleString()} point)</small>`;
                            } else if (approvedPoint < requestedPoint) {
                                message += `<br><small class="text-info"><i class="fas fa-info-circle"></i> Anda akan approve <strong>KURANG</strong> dari yang diminta (${requestedPoint.toLocaleString()} point)</small>`;
                            }

                            $('#quota-feedback').html(`
                                <div class="alert alert-success mb-0">${message}</div>
                            `);
                            $('#approveBtn').prop('disabled', false);
                        } else {
                            $('#quota-feedback').html(`
                                <div class="alert alert-danger mb-0">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Quota tidak mencukupi!</strong> 
                                    Tersisa hanya ${remaining.toLocaleString()} point, Anda akan approve ${approvedPoint.toLocaleString()} point.
                                </div>
                            `);
                            $('#approveBtn').prop('disabled', true);
                        }
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.error === 'no_quota') {
                        $('#quota-info-panel').html(`
                            <div class="alert alert-warning m-3">
                                <h6><i class="fas fa-exclamation-triangle"></i> Quota Tidak Tersedia</h6>
                                <p class="mb-2">${xhr.responseJSON.message}</p>
                                <small>${xhr.responseJSON.guidance}</small>
                            </div>
                        `);
                        $('#quota-feedback').html(`
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-circle"></i> 
                                <strong>Quota belum tersedia!</strong><br>
                                <small>Silakan edit divisi pada menu Divisi untuk menambahkan quota atau hubungi Admin.</small>
                            </div>
                        `);
                        $('#approveBtn').prop('disabled', true);
                    } else {
                        $('#quota-feedback').html(`
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-exclamation-circle"></i> 
                                Error mengecek quota. Silakan coba lagi.
                            </div>
                        `);
                        $('#approveBtn').prop('disabled', true);
                    }
                },
                complete: function() {
                    checkingQuota = false;
                }
            });
        }

        // Load quota on page load
        checkQuota();

        // Real-time check on input change with debounce
        let quotaCheckTimeout;
        $('#approved_point').on('input keyup change', function() {
            clearTimeout(quotaCheckTimeout);
            quotaCheckTimeout = setTimeout(checkQuota, 500);
        });

        // Form submit with Sweet Alert
        $('#approveForm').on('submit', function(e) {
            e.preventDefault();
            
            const approvedPoint = parseInt($('#approved_point').val()) || 0;
            const requestedPoint = {{ $directPoint->point }};
            
            if (!currentQuotaData || !currentQuotaData.is_sufficient) {
                Swal.fire({
                    icon: 'error',
                    title: 'Quota Tidak Cukup',
                    text: 'Quota tidak mencukupi untuk approve Direct Point ini.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            let title = 'Konfirmasi Approval';
            let text = `Approve Direct Point sebesar ${approvedPoint.toLocaleString()} point?`;
            let icon = 'question';
            let html = '';

            if (approvedPoint > requestedPoint) {
                icon = 'warning';
                title = 'Perhatian: Approve Lebih Banyak';
                html = `
                    <p>Anda akan <strong>menyetujui LEBIH</strong> dari yang diminta:</p>
                    <div class="alert alert-warning">
                        <div>Diminta: <strong>${requestedPoint.toLocaleString()}</strong> point</div>
                        <div>Disetujui: <strong>${approvedPoint.toLocaleString()}</strong> point</div>
                        <div class="text-success mt-2">+ ${(approvedPoint - requestedPoint).toLocaleString()} point lebih banyak</div>
                    </div>
                    <p>Apakah Anda yakin?</p>
                `;
            } else if (approvedPoint < requestedPoint) {
                icon = 'info';
                title = 'Approve Sebagian';
                html = `
                    <p>Anda akan <strong>menyetujui KURANG</strong> dari yang diminta:</p>
                    <div class="alert alert-info">
                        <div>Diminta: <strong>${requestedPoint.toLocaleString()}</strong> point</div>
                        <div>Disetujui: <strong>${approvedPoint.toLocaleString()}</strong> point</div>
                        <div class="text-warning mt-2">- ${(requestedPoint - approvedPoint).toLocaleString()} point kurang</div>
                    </div>
                    <p>Apakah Anda yakin?</p>
                `;
            } else {
                html = `
                    <p>Approve Direct Point sesuai permintaan:</p>
                    <div class="alert alert-success">
                        <strong>${approvedPoint.toLocaleString()}</strong> point
                    </div>
                `;
            }

            Swal.fire({
                title: title,
                html: html,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check"></i> Ya, Approve',
                cancelButtonText: '<i class="fas fa-times"></i> Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit form
                    e.target.submit();
                }
            });
        });
    });
</script>
@endcanAccess
@endif
@stop
