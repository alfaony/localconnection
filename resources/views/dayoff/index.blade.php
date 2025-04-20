@extends('adminlte::page')

@section('title', 'Daftar Cuti Saya')

@section('content_header')
    @include('components.alert')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="m-0 text-dark font-weight-bold">📋 Daftar Pengajuan Cuti</h1>
            <small class="text-muted">Riwayat permohonan cuti Anda</small>
        </div>
        @canAccess('create', 'dayoffs')
        <a href="{{ route('dayoff.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle mr-2"></i>Ajukan Cuti
        </a>
        @endcanAccess
    </div>
@stop
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="row mb-4">
            <div class="col-md-10">
                @if(Auth::user()->isSearchDayoff())
                <form method="GET" action="{{ route('dayoff.index') }}" class="form-inline justify-content-end">
                    <div class="form-row align-items-center w-100">
                        <div class="col-auto">
                            <select name="user_id" class="form-control select2">
                                <option value="">Semua User</option>
                                @foreach($users as $userOption)
                                    <option value="{{ $userOption->id }}" {{ request('user_id') == $userOption->id ? 'selected' : '' }}>
                                        {{ $userOption->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-auto">
                            <select name="type_id" class="form-control select2">
                                <option value="">Semua Jenis</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Tanggal" id="date_range" value="{{ request('start_date') && request('end_date') ? request('start_date').' - '.request('end_date') : '' }}">
                                <input type="hidden" id="start_date" name="start_date" value="{{ request('start_date') }}">
                                <input type="hidden" id="end_date" name="end_date" value="{{ request('end_date') }}">
                            </div>
                        </div>

                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search mr-1"></i>Cari
                            </button>
                        </div>
                        <div class="col-auto">
                            <button type="button" onclick="window.location.href='{{ route('dayoff.index') }}'" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i>Reset
                            </button>
                        </div>

                    </div>
                </form>
                @endif
            </div>
            <div class="col-md-2 d-flex justify-content-end">
                @canAccess('export', 'dayoffs')
                @canAccess('checkExportStatus', 'dayoffs')
                @canAccess('clearExportSession', 'dayoffs')
                <div class="col-auto">
                    <a href="{{ route('dayoff.export', array_merge(request()->all(), ['format' => 'xlsx'])) }}" class="btn btn-success ">
                        <i class="fas fa-file-excel mr-1"></i>Export Excel
                    </a>
                </div>
                @endcanAccess
                @endcanAccess
                @endcanAccess
            </div>
        </div>

        @canAccess('hrApprovement', 'dayoffs')
        <form id="form-hr" method="POST" action="{{ route('dayoff.hrApprovement') }}">
            @csrf
            <input type="hidden" name="cuti_ids" id="cuti_ids_input_hr">
            <input type="hidden" name="action_type" id="action_type_input_hr">

            <small class="text-muted selected-count-hr" >Info: Approval HR</small>
            <div class="d-flex justify-content-between align-items-center px-3 py-2">
                <div>
                    <button type="button" class="btn btn-success btn-sm" onclick="return submitHR('approve')">
                        <i class="fas fa-check-circle mr-1"></i>Setujui Terpilih
                    </button>

                    <button type="button" class="btn btn-danger btn-sm ml-2" onclick="return submitHR('reject')">
                        <i class="fas fa-times-circle mr-1"></i>Tolak Terpilih
                    </button>
                </div>
            </div>
        </form>
        @endcanAccess
        @canAccess('financeApprovement', 'dayoffs')
        <form id="form-finance" method="POST" action="{{ route('dayoff.financeApprovement') }}">
            @csrf
            <input type="hidden" name="cuti_ids" id="cuti_ids_input">
            <input type="hidden" name="action_type" id="action_type_input">

            <small class="text-muted selected-count-finance" >Info: Approval Finance</small>
            <div class="d-flex justify-content-between align-items-center px-3 py-2">
                <div>
                    <button type="button" class="btn btn-success btn-sm" onclick="return submitFinance('approve')">
                        <i class="fas fa-check-circle mr-1"></i>Setujui Terpilih
                    </button>

                    <button type="button" class="btn btn-danger btn-sm ml-2" onclick="return submitFinance('reject')">
                        <i class="fas fa-times-circle mr-1"></i>Tolak Terpilih
                    </button>
                </div>
            </div>
        </form>
        @endcanAccess

        <div class="card border-0 shadow-lg">
            <div class="card-body p-0">
                <div class="table-responsive rounded-lg shadow-sm">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                @canAccess('hrApprovement', 'dayoffs')
                                <th class="pl-4 py-3 align-middle text-center" style="width: 5%">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="select-all-hr">
                                        <label class="custom-control-label" for="select-all-hr">HR</label>
                                    </div>
                                </th>
                                @endcanAccess

                                @canAccess('financeApprovement', 'dayoffs')
                                <th class="pl-4 py-3 align-middle text-center" style="width: 5%">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="select-all-finance">
                                        <label class="custom-control-label" for="select-all-finance">Finance</label>
                                    </div>
                                </th>
                                @endcanAccess
                                <th class="pl-4 py-3" style="width: 18%">
                                    <div class="d-flex align-items-center text-uppercase text-secondary font-weight-600">
                                        <i class="far fa-calendar-alt mr-2 fs-14"></i>Tanggal
                                    </div>
                                </th>
                                <th class="py-3" style="width: 12%">
                                    <div class="d-flex align-items-center text-uppercase text-secondary font-weight-600">
                                        <i class="fas fa-tag mr-2 fs-14"></i>Jenis
                                    </div>
                                </th>
                                <th class="py-3" style="width: 10%">
                                    <div class="d-flex align-items-center text-uppercase text-secondary font-weight-600">
                                        <i class="fas fa-clock mr-2 fs-14"></i>Durasi
                                    </div>
                                </th>
                                <th class="py-3" style="width: 18%">
                                    <div class="d-flex align-items-center text-uppercase text-secondary font-weight-600">
                                        <i class="fas fa-comment mr-2 fs-14"></i>Alasan
                                    </div>
                                </th>
                                <th class="py-3" style="width: 18%">
                                    <div class="d-flex align-items-center text-uppercase text-secondary font-weight-600">
                                        <i class="fas fa-user mr-2 fs-14"></i>Pengaju
                                    </div>
                                </th>
                                <th class="py-3" style="width: 18%">
                                    <div class="d-flex align-items-center text-uppercase text-secondary font-weight-600">
                                        <i class="fas fa-user-check mr-2 fs-14"></i>Persetujuan
                                    </div>
                                </th>
                                <th class="py-3" style="width: 10%">
                                    <div class="d-flex align-items-center text-uppercase text-secondary font-weight-600">
                                        <i class="fas fa-info-circle mr-2 fs-14"></i>Status
                                    </div>
                                </th>
                                <th class="pr-4 py-3 text-right" style="width: 9%"></th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse ($cutis as $cuti)
                                <tr class="border-bottom">
                                    @canAccess('hrApprovement', 'dayoffs')
                                    <td class="pl-4 align-middle">
                                        @if($cuti->rejected_at)
                                            <i class="fas fa-times-circle text-danger"></i>
                                        @elseif(!$cuti->approvalHR)
                                        <input type="checkbox" class="cuti-checkbox cuti-checkbox-hr" value="{{ $cuti->id }}">
                                        @else
                                            <i class="fas fa-check-circle text-success"></i>
                                        @endif
                                    </td>
                                    @endcanAccess
                                    @canAccess('financeApprovement', 'dayoffs')
                                    <td class="pl-4 align-middle">
                                        @if($cuti->rejected_at)
                                        <i class="fas fa-times-circle text-danger"></i>
                                        @elseif(!$cuti->approvalFinance)
                                            <input type="checkbox" class="cuti-checkbox cuti-checkbox-finance" value="{{ $cuti->id }}">
                                        @else
                                            <i class="fas fa-check-circle text-success"></i>
                                        @endif
                                    </td>
                                    @endcanAccess
                                    <td class="pl-4 align-middle">
                                        <div class="d-flex flex-column">
                                            <span class="text-dark font-weight-500">
                                                {{ \Carbon\Carbon::parse($cuti->date_start)->format('d M') }}
                                                <span class="text-muted">-</span>
                                                {{ \Carbon\Carbon::parse($cuti->date_end)->format('d M Y') }}
                                            </span>
                                            <small class="text-muted fs-12">
                                                ({{ $cuti->durationInDays() }} hari)
                                            </small>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-{{ $cuti->type->badge_color ?? 'secondary' }} text-capitalize">
                                            {{ $cuti->type->name ?? 'Sesuaikan' }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="text-dark font-weight-500">
                                            {{ $cuti->durationInDays() }} Hari
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="text-truncate position-relative" style="max-width: 200px;" 
                                            data-toggle="tooltip" title="{{ $cuti->reason }}">
                                            {{ $cuti->reason }}
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        {{ $cuti->user->name ?? '-' }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex align-items-center">
                                                <div class="badge badge-circle bg-soft-primary mr-2">
                                                    <i class="fas fa-user-tie fs-12"></i>
                                                </div>
                                                <div class="flex-fill">
                                                    @if($cuti->approvalHR)
                                                        <div class="text-success fs-13">
                                                            {{ $cuti->approvalHR->name ?? '-' }}
                                                            <div class="text-muted fs-12">
                                                                {{ \Carbon\Carbon::parse($cuti->approved_hr_at)->format('d M H:i') }}
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted fs-13">Menunggu HR</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="badge badge-circle bg-soft-info mr-2">
                                                    <i class="fas fa-coins fs-12"></i>
                                                </div>
                                                <div class="flex-fill">
                                                    @if($cuti->approvalFinance)
                                                        <div class="text-success fs-13">
                                                            {{ $cuti->approvalFinance->name ?? '-' }}
                                                            <div class="text-muted fs-12">
                                                                {{ \Carbon\Carbon::parse($cuti->approved_finance_at)->format('d M H:i') }}
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted fs-13">Menunggu Finance</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        {!! $cuti->statusBadge !!}
                                    </td>
                                    <td class="pr-4 text-right align-middle">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('dayoff.show', $cuti->id) }}" 
                                                class="btn btn-icon btn-sm btn-soft-info"
                                                data-toggle="tooltip"
                                                title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @if($cuti->permissionChanged)
                                                @canAccess('edit', 'dayoffs')
                                                <a href="{{ route('dayoff.edit', $cuti->id) }}" 
                                                class="btn btn-icon btn-sm btn-soft-primary"
                                                data-toggle="tooltip"
                                                title="Edit">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                @endcanAccess
                                                @canAccess('destroy', 'dayoffs')
                                                <form action="{{ route('dayoff.destroy', $cuti->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-icon btn-sm btn-soft-danger"
                                                            data-toggle="tooltip"
                                                            title="Hapus"
                                                            onclick="return confirm('Yakin hapus cuti ini?')">
                                                        <i class="far fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                                @endcanAccess
                                            @else
                                                <span class="text-muted fs-14" data-toggle="tooltip" title="Terkunci">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center text-muted">
                                            <i class="fas fa-cloud fs-40 mb-3"></i>
                                            <p class="fs-14 mb-0">Belum ada data pengajuan cuti</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
    
                @if($cutis->hasPages())
                    <div class="card-footer border-0 bg-light">
                        <div class="d-flex justify-content-center">
                            {{ $cutis->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
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

            fetch('{{ route('dayoff.checkExportStatus') }}')
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
                            fetch('{{ route('dayoff.clearExportSession') }}')
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
@canAccess('hrApprovement', 'dayoffs')
<script>
    function submitHR(action)
    {
        const selected = $('.cuti-checkbox-hr:checked').map(function () {
            return this.value;
        }).get();

        if (selected.length === 0) {
            alert('Pilih minimal satu cuti untuk HR!');
            return false;
        }

        if (action === 'approve') {
            const confirm = confirm('Apakah Anda yakin ingin mensetujui cuti terpilih?');
            if (!confirm) {
                return false;
            }
        } else if (action === 'reject') {
            const confirm = confirm('Apakah Anda yakin ingin menolak cuti terpilih?');
            if (!confirm) {
                return false;
            }
        }

        $('#cuti_ids_input_hr').val(JSON.stringify(selected));
        $('#action_type_input_hr').val(action);

        document.getElementById('form-hr').submit();
    }

    $(document).ready(function () 
    {
        // HR Checkbox Control
        $('#select-all-hr').on('change', function () {
            $('.cuti-checkbox-hr').prop('checked', this.checked).trigger('change');
        });

        $('.cuti-checkbox-hr').on('change', function () {
            let total = $('.cuti-checkbox-hr').length;
            let checked = $('.cuti-checkbox-hr:checked').length;
            $('#select-all-hr').prop('checked', total === checked);
            $('.selected-count-hr').first().text(`${checked} terpilih (HR)`);
        });
    });

</script>
@endcanAccess

@canAccess('financeApprovement', 'dayoffs')
<script>
    function submitFinance(action) 
    {
        const selected = $('.cuti-checkbox-finance:checked').map(function () {
            return this.value;
        }).get();

        if (selected.length === 0) {
            alert('Pilih minimal satu cuti untuk Finance!');
            return false;
        }

        if (action === 'approve') 
        {
            const confirm = confirm('Apakah Anda yakin ingin mensetujui cuti terpilih?');
            if (!confirm) {
                return false;
            }
        } else if (action === 'reject') {
            const confirm = confirm('Apakah Anda yakin ingin menolak cuti terpilih?');
            if (!confirm) {
                return false;
            }
        }

        $('#cuti_ids_input').val(JSON.stringify(selected));
        $('#action_type_input').val(action);
        document.getElementById('form-finance').submit();
    }

    $(document).ready(function () 
    {
        // Finance Checkbox Control
        $('#select-all-finance').on('change', function () {
            $('.cuti-checkbox-finance').prop('checked', this.checked).trigger('change');
        });

        $('.cuti-checkbox-finance').on('change', function () {
            let total = $('.cuti-checkbox-finance').length;
            let checked = $('.cuti-checkbox-finance:checked').length;
            $('#select-all-finance').prop('checked', total === checked);
            $('.selected-count-finance').last().text(`${checked} terpilih (Finance)`);
        });
    });
</script>
@endcanAccess
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
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