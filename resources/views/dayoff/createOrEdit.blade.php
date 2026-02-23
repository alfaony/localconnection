@extends('adminlte::page')

@section('title', $mode === 'edit' ? 'Edit Cuti' : 'Ajukan Cuti')

@section('content_header')
    <h1>{{ $mode === 'edit' ? 'Edit Pengajuan Cuti' : 'Ajukan Cuti' }}</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm">
    <div class="card-body">
        <form 
            action="{{ $mode === 'edit' ? route('dayoff.update', $cuti->id) : route('dayoff.store') }}" 
            method="POST" 
            enctype="multipart/form-data">
            @csrf
            @if($mode === 'edit') @method('PATCH') @endif

            <!-- Leave Type Section -->
            <div class="mb-4">
                <label class="form-label fw-bold text-primary">Jenis Cuti <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-tag-fill"></i>
                    </span>
                    <select name="dayoff_type_id" id="dayoff_type_id" class="form-control" required {{ $mode === 'edit' ? 'disabled' : '' }}>
                        <option value="">Pilih Jenis Cuti</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" data-permissionrequire="{{ $type->permission_required }}" {{ old('dayoff_type_id', $cuti->dayoff_type_id ?? '') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Leave Information Card -->
                <div id="cuti-info-box" class="mt-3 alert alert-info d-none" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <h5 class="alert-heading mb-0">Informasi Cuti</h5>
                    </div>
                    <hr>
                    <div id="cuti-warnings" class="mb-2"></div>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-calendar-check me-2"></i>
                            <strong>Sisa Cuti Saat Ini:</strong> 
                            <span id="cuti-sisa-awal" class="badge bg-primary">0 hari</span>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-clock-history me-2"></i>
                            <strong>Durasi Cuti:</strong> 
                            <span id="cuti-durasi" class="badge bg-secondary">0 hari</span>
                        </li>
                        <li>
                            <i class="bi bi-calendar-x me-2"></i>
                            <strong>Sisa Setelah Diajukan:</strong> 
                            <span id="cuti-sisa-akhir" class="badge bg-success">0 hari</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Date Range Section -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-primary">Tanggal Mulai <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-calendar-event"></i>
                        </span>
                        <input type="date" name="date_start" class="form-control " 
                               id="date_start" value="{{ old('date_start', optional($cuti)->date_start) }}" 
                                max="{{ now()->endOfYear()->format('Y-m-d') }}"
                               required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-bold text-primary">Tanggal Selesai <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-calendar-check"></i>
                        </span>
                        <input type="date" name="date_end" class="form-control " 
                               id="date_end" value="{{ old('date_end', optional($cuti)->date_end) }}" 
                               max="{{ now()->endOfYear()->format('Y-m-d') }}"
                               required >
                    </div>
                </div>

                {{-- 
                <!-- Date Information -->
                <div class="col-12 mt-2">
                    <div class="alert alert-secondary mb-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-lightbulb-fill me-2"></i>
                            <div>
                                <span id="info-durasi" class="fw-bold">Durasi: 0 hari kerja</span>
                                <span class="mx-2">|</span>
                                <span id="info-sisa" class="fw-bold">Sisa cuti setelah pengajuan: 0 hari</span>
                            </div>
                        </div>
                    </div>
                </div>
                --}}
            </div>

            <!-- Reason Section -->
            <div class="mb-4">
                <label class="form-label fw-bold text-primary">Alasan Cuti</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-chat-text-fill"></i>
                    </span>
                    <textarea name="reason" class="form-control " 
                              rows="4" placeholder="Masukkan alasan lengkap cuti Anda">{{ old('reason', optional($cuti)->reason) }}</textarea>
                </div>
            </div>

            <!-- File Upload Section -->
            <div class="mb-4" id="reason-group">
                <label class="form-label fw-bold text-primary">Upload Dokumen Pendukung</label>
                <div class="input-group">
                    <input type="file" name="file" id="file" class="form-control" 
                           accept="image/*" aria-describedby="file-name">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid gap-2 mt-5">
                @if(!Auth::user()->dayoff_active)
                    <div class="alert alert-danger" role="alert">
                        <h5 class="alert-heading">Cuti Belum Tersedia</h5>
                        <p>
                            Hubungi Admin atau Leader perusahaan Anda untuk mengaktifkan fitur cuti.
                        </p>
                    </div>
                @else
                @canAccess('store', 'dayoffs')
                @canAccess('update', 'dayoffs')
                <button type="submit" class="btn btn {{ $mode === 'edit' ? 'btn-warning' : 'btn-primary' }}">
                    <i class="bi bi-send-check-fill me-2"></i>
                    {{ $mode === 'edit' ? 'Perbarui Pengajuan' : 'Ajukan Cuti' }}
                </button>
                @endcanAccess
                @endcanAccess
                @endif
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    let validationTimeout;
    
    function updateInfoBox(info) 
    {
        $('#cuti-sisa-awal').text(info.quota === 'Unlimited' ? 'Unlimited' : `${info.quota} hari`);
        $('#cuti-durasi').text(`${info.durasi} hari`);
        $('#cuti-sisa-akhir').text(info.remaining === 'Unlimited' ? 'Unlimited' : `${info.remaining} hari`);
        $('#cuti-info-box').removeClass('d-none');

        let warnings = [];

        if (info.overlaps) {
            warnings.push('<i class="bi bi-x-circle-fill text-danger"></i> Tanggal bentrok dengan cuti lain.');
        }

        if (info.quota_insufficient) {
            warnings.push('<i class="bi bi-x-circle-fill text-danger"></i> Kuota cuti tidak mencukupi.');
        }

        if (warnings.length > 0) {
            $('#cuti-warnings').html(warnings.map(w => `<p class="text-danger">${w}</p>`).join(''));
            $('button[type=submit]').attr('disabled', true);
        } else {
            $('#cuti-warnings').html('');
            $('button[type=submit]').attr('disabled', false);
        }
    }

    function toggleReasonField() 
    {
        const selectedOption = $('#dayoff_type_id :selected');
        const isReasonRequired = selectedOption.data('permissionrequire');
        let mode = "{{ $mode }}";
        

        
        if (isReasonRequired ) {
            $('#reason-group').show();

            if(mode != 'edit') { $('#file').attr('required', true); }
        } else {
            $('#reason-group').hide();
            $('#file').attr('required', false);
        }

        if ($('#date_start').val() && !$('#date_end').val()) 
        {
            $('#date_end').val($('#date_start').val());
        }
    }

    function fetchInfo() {
        console.log("CHANESSS");
        

        toggleReasonField();

        let typeId = $('#dayoff_type_id').val();
        let start = $('#date_start').val();
        let end = $('#date_end').val();
        let excludeId = "{{ $mode === 'edit' && $cuti ? $cuti->id : '' }}";


        if (!typeId || !start || !end) {
            $('#cuti-info-box').addClass('d-none');
            $('#cuti-warnings').html('');
            $('button[type=submit]').attr('disabled', false);
            return;
        }

        // Clear previous timeout
        if (validationTimeout) {
            clearTimeout(validationTimeout);
        }

        // Debounce API call to prevent excessive requests
        validationTimeout = setTimeout(function() {
            $.get(`{{ route('dayoff.checkInfo') }}`, {
                dayoff_type_id: typeId,
                date_start: start,
                date_end: end,
                exclude_id: excludeId
            }, function (res) {
                updateInfoBox(res);
            }).fail(function() {
                console.error('Failed to fetch day-off info');
            });
        }, 300); // 300ms debounce
    }

    function previewFile() 
    {
        const fileInput = document.getElementById('file');
        const fileName = document.getElementById('file-name');
        fileName.textContent = fileInput.files[0] ? fileInput.files[0].name : 'Belum ada file dipilih';
    }

    $(document).ready(function () {
        // Trigger validation on both change and input events
        $('#dayoff_type_id').on('change', fetchInfo);
        $('#date_start, #date_end').on('change ', fetchInfo);
        
        // Initial fetch on page load
        fetchInfo();
    });
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
.file-upload-wrapper {
    border: 2px dashed #ced4da;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}
.file-upload-wrapper:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}
</style>
@stop