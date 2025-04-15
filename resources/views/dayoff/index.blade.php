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

        <div class="card border-0 shadow-lg">
            <div class="card-body p-0">
                <div class="table-responsive rounded-lg shadow-sm">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                @canAccess('financeApprovement', 'dayoffs')
                                <th class="pl-4 py-3 align-middle text-center" style="width: 5%">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="select-all-hr">
                                        <label class="custom-control-label" for="select-all-hr">HR</label>
                                    </div>
                                </th>
                                @endcanAccess

                                @canAccess('hrApprovement', 'dayoffs')
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
                                    @canAccess('financeApprovement', 'dayoffs')
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
                                    @canAccess('hrApprovement', 'dayoffs')
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
                                        <span class="badge badge-soft-{{ $cuti->type->color ?? 'secondary' }} px-3 py-1">
                                            {{ $cuti->type->name ?? '-' }}
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
                                        @if($cuti->permissionChanged)
                                            <div class="d-flex justify-content-end gap-2">
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
                                            </div>
                                        @else
                                            <span class="text-muted fs-14" data-toggle="tooltip" title="Terkunci">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                        @endif
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