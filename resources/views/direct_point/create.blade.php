@extends('adminlte::page')

@section('content_header')
    <h1>Buat Direct Point</h1>
@stop

@section('content')
<div class="container-fluid">
    @include('components.alert')
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-gift"></i> Buat Direct Point Baru</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('direct-point.store') }}" method="POST" id="directPointForm" onsubmit="return confirmCreate()">
                        @csrf
                        
                        @canAccess('checkQuota','direct_points')
                        <div class="form-group">
                            <label class="font-weight-bold">Penerima Point <span class="text-danger">*</span></label>
                            <select name="to_user_id" id="to_user_id" class="form-control @error('to_user_id') is-invalid @enderror" required>
                                <option value="">-- Pilih User --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('to_user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('to_user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Jumlah Point <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-coins"></i></span>
                                </div>
                                <input type="number" name="point" id="point" 
                                       class="form-control @error('point') is-invalid @enderror" 
                                       value="{{ old('point', 0) }}" placeholder="Masukkan jumlah point (bisa min)" required>
                                @error('point')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="quota-feedback" class="mt-2"></div>
                        </div>
                        
                        <div class="form-group" id="division-container" style="display: none;">
                            <label class="font-weight-bold">Divisi Sumber Quota <span class="text-danger" id="division-star">*</span></label>
                            <select name="division_id" id="division_id" class="form-control @error('division_id') is-invalid @enderror">
                                <option value="">-- Pilih Divisi --</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('division_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Quota point akan diambil dari divisi ini</small>
                        </div>
                        @endcanAccess

                        <div class="form-group">
                            <label class="font-weight-bold">Alasan Pemberian</label>
                            <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" 
                                      rows="3" placeholder="Jelaskan alasan pemberian point (opsional)">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('direct-point.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            @canAccess('store','direct_points')
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-check"></i> Submit Direct Point
                            </button>
                            @endcanAccess
                        </div>
                    </form>
                    
                    <script>
                    function confirmCreate() {
                        // Prevent default form submission
                        event.preventDefault();
                        
                        // Get form values
                        const divisionSelect = document.getElementById('division_id');
                        const userSelect = document.getElementById('to_user_id');
                        const pointInput = document.getElementById('point');
                        const reasonInput = document.querySelector('textarea[name="reason"]');
                        
                        const divisionName = divisionSelect.options[divisionSelect.selectedIndex]?.text || '';
                        const userName = userSelect.options[userSelect.selectedIndex]?.text || '';
                        const point = parseInt(pointInput.value);
                        const reason = reasonInput.value.trim();
                        
                        // Validation
                        if (point > 0 && (!divisionSelect.value || divisionName === '-- Pilih Divisi --')) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Divisi Belum Dipilih',
                                text: 'Mohon pilih Divisi Sumber Quota terlebih dahulu!',
                                confirmButtonColor: '#3085d6'
                            });
                            return false;
                        }
                        
                        if (!userSelect.value || userName === '-- Pilih User --') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Penerima Belum Dipilih',
                                text: 'Mohon pilih Penerima Point terlebih dahulu!',
                                confirmButtonColor: '#3085d6'
                            });
                            return false;
                        }
                        
                        if (point === 0 || isNaN(point)) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Point Tidak Valid',
                                text: 'Mohon masukkan Jumlah Point yang valid (tidak boleh 0)!',
                                confirmButtonColor: '#3085d6'
                            });
                            return false;
                        }
                        
                        // Build HTML message
                        let htmlMessage = `
                            <div style="text-align: left; padding: 10px;">
                                <table style="width: 100%; margin-bottom: 20px;">
                                    <tr>
                                        <td style="padding: 8px 0;"><strong>Divisi Sumber:</strong></td>
                                        <td style="padding: 8px 0;">${divisionName}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0;"><strong>Penerima:</strong></td>
                                        <td style="padding: 8px 0;">${userName}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0;"><strong>Jumlah Point:</strong></td>
                                        <td style="padding: 8px 0;"><span style="font-size: 1.2em; color: #28a745; font-weight: bold;">${point}</span></td>
                                    </tr>
                                    ${reason ? `<tr><td style="padding: 8px 0;"><strong>Alasan:</strong></td><td style="padding: 8px 0;">${reason.substring(0, 100)}${reason.length > 100 ? '...' : ''}</td></tr>` : ''}
                                </table>
                                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin-top: 15px;">
                                    <strong>⚠️ PERINGATAN PENTING:</strong>
                                    <ul style="margin: 10px 0 0 20px; text-align: left;">
                                        <li>Data <strong>TIDAK DAPAT DIEDIT</strong> setelah dibuat</li>
                                        <li>Request akan menunggu approval (jika tidak auto-approve)</li>
                                        <li>Quota akan dialokasikan saat disetujui</li>
                                        <li>Pastikan semua data sudah <strong>BENAR!</strong></li>
                                    </ul>
                                </div>
                            </div>
                        `;
                        
                        Swal.fire({
                            title: 'Konfirmasi Pembuatan Direct Point',
                            html: htmlMessage,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: '<i class="fas fa-check"></i> Ya, Buat Direct Point',
                            cancelButtonText: '<i class="fas fa-times"></i> Batal',
                            customClass: {
                                confirmButton: 'btn btn-success btn-md mr-2 mb-1',
                                cancelButton: 'btn btn-secondary btn-md mb-1'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Submit the form
                                document.getElementById('directPointForm').submit();
                            }
                        });
                        
                        return false;
                    }
                    </script>
                </div>
            </div>
        </div>

        <!-- Quota Info Sidebar -->
        <div class="col-md-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Quota</h5>
                </div>
                <div class="card-body" id="quota-info-panel">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-chart-pie fa-3x mb-3"></i>
                        <p>Pilih divisi untuk melihat informasi quota</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-question-circle"></i> Ketentuan</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 pl-3">
                        <li class="mb-2">Point akan <strong>auto-approved</strong> jika Anda member divisi dengan weekly report required</li>
                        <li class="mb-2">Jika tidak, perlu <strong>approval</strong> dari member divisi</li>
                        <li class="mb-2">Quota dihitung per bulan sesuai periode perusahaan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>
@canAccess('checkQuota','direct_points')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('#division_id, #to_user_id').select2({
            theme: 'bootstrap4',
            placeholder: '-- Pilih --',
            allowClear: true,
            width: '100%'
        });

        let checkingQuota = false;
        let currentQuotaData = null;


        function updateQuotaPanel(data, point = 0) {
            if (!data) {
                $('#quota-info-panel').html(`
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-chart-pie fa-3x mb-3"></i>
                        <p>Pilih divisi untuk melihat informasi quota</p>
                    </div>
                `);
                return;
            }

            const { quota, used, remaining } = data;
            
            // Calculate remaining AFTER point usage (negative takes 0 quota)
            const requiredQuota = point > 0 ? point : 0;
            const remainingAfterUsage = remaining - requiredQuota;
            const totalUsedAfter = used + requiredQuota;
            const usedPercentage = quota > 0 ? (totalUsedAfter / quota * 100).toFixed(1) : 0;

            let progressColor = 'success';
            if (usedPercentage > 80) progressColor = 'danger';
            else if (usedPercentage > 60) progressColor = 'warning';

            // Color for remaining
            let remainingColor = '';
            if (remainingAfterUsage < 0) {
                remainingColor = 'text-danger';
            } else if (remainingAfterUsage < quota * 0.2) {
                remainingColor = 'text-warning';
            } else {
                remainingColor = 'text-success';
            }

            $('#quota-info-panel').html(`
                <div class="text-center mb-3">
                    <h2 class="mb-0 ${remainingColor}">${remainingAfterUsage.toLocaleString()}</h2>
                    <small class="text-muted">Point Tersisa ${point > 0 ? '(Setelah Transaksi)' : ''}</small>
                </div>

                <div class="progress mb-3" style="height: 25px;">
                    <div class="progress-bar bg-${progressColor}" role="progressbar" 
                        style="width: ${Math.min(usedPercentage, 100)}%" 
                        aria-valuenow="${totalUsedAfter}" aria-valuemin="0" aria-valuemax="${quota}">
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
                            <span class="font-weight-bold">${(data.task_used || 0).toLocaleString()}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>• Direct Points:</span>
                            <span class="font-weight-bold">${(data.direct_point_used || 0).toLocaleString()}</span>
                        </div>
                        ${point > 0 ? `
                        <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                            <span>• <strong>Akan Digunakan:</strong></span>
                            <span class="font-weight-bold text-warning">${point.toLocaleString()}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `);
        }

        function checkQuota() {
            const divisionId = $('#division_id').val();
            const point = $('#point').val();
            const pointVal = parseInt(point) || 0;

            if (pointVal <= 0) {
                $('#division-container').hide();
                
                let panelContent = '';
                if (pointVal === 0) {
                    panelContent = `
                        <i class="fas fa-info-circle fa-3x mb-3 text-secondary"></i>
                        <p>Total point 0 tidak diizinkan.<br>Masukkan point > 0 untuk menggunakan Quota Divisi.</p>
                    `;
                    $('#submitBtn').prop('disabled', true);
                } else {
                    panelContent = `
                        <i class="fas fa-minus-circle fa-3x mb-3 text-secondary"></i>
                        <p>Total point negatif (Punishment).<br>Tidak memotong quota divisi.</p>
                    `;
                    $('#submitBtn').prop('disabled', false);
                }
                
                $('#quota-info-panel').html(`
                    <div class="text-center text-muted py-5">
                        ${panelContent}
                    </div>
                `);
                
                $('#quota-feedback').html('');
                return;
            } else {
                $('#division-container').show();
            }

            if (!divisionId) {
                updateQuotaPanel(null);
                $('#quota-feedback').html('');
                $('#submitBtn').prop('disabled', false);
                return;
            }

            // Show loading in panel
            if (!currentQuotaData) {
                $('#quota-info-panel').html(`
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                        <p>Memuat informasi quota...</p>
                    </div>
                `);
            }

            if (checkingQuota) return;
            checkingQuota = true;
            
            $.ajax({
                url: '{{ route("direct-point.check-quota") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    division_id: divisionId,
                    point: point || null
                },
                success: function(response) {
                    if (response.success) {
                        currentQuotaData = response;
                        // Pass point so panel shows remaining AFTER usage
                        updateQuotaPanel(response, parseInt(point) || 0);

                        const { remaining, is_sufficient } = response;
                        const pointVal = parseInt(point) || 0;
                        const requiredQuota = pointVal > 0 ? pointVal : 0;
                        const remainingAfterUsage = remaining - requiredQuota;
                        
                        if (point === 0 || isNaN(point)) {
                            $('#quota-feedback').html('');
                            $('#submitBtn').prop('disabled', false);
                        } else if (is_sufficient) {
                            $('#quota-feedback').html(`
                                <div class="alert alert-success mb-0">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Quota mencukupi!</strong> 
                                    Tersisa ${remainingAfterUsage.toLocaleString()} point setelah transaksi ini.
                                </div>
                            `);
                            $('#submitBtn').prop('disabled', false);
                        } else {
                            $('#quota-feedback').html(`
                                <div class="alert alert-danger mb-0">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Quota tidak mencukupi!</strong> 
                                    Tersisa hanya ${remaining.toLocaleString()} point, Anda meminta ${point} point.
                                </div>
                            `);
                            $('#submitBtn').prop('disabled', true);
                        }
                    }
                },
                error: function(xhr) {
                    // Handle specific error responses
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.error === 'no_quota') {
                        // No quota lock exists
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
                        $('#submitBtn').prop('disabled', true);
                    } else {
                        // General error
                        $('#quota-feedback').html(`
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-exclamation-circle"></i> 
                                Error mengecek quota. Silakan coba lagi.
                            </div>
                        `);
                        $('#submitBtn').prop('disabled', true);
                    }
                },
                complete: function() {
                    checkingQuota = false;
                }
            });
        }

        // Trigger check on division or point change
        $('#division_id').on('change', _.debounce(checkQuota, 300));
        $('#point').on('keyup change', _.debounce(checkQuota, 500));

        // Initial check if values are pre-filled or starting at 0
        checkQuota();
    });
</script>
@endcanAccess
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .card {
        border-radius: 8px;
    }
    .card-header {
        border-radius: 8px 8px 0 0 !important;
    }
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    .progress {
        border-radius: 10px;
    }
    .sticky-top {
        z-index: 1020;
    }
    .select2-container--bootstrap4 .select2-selection {
        height: calc(1.5em + 0.75rem + 2px) !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: calc(1.5em + 0.75rem) !important;
    }
</style>
@stop
