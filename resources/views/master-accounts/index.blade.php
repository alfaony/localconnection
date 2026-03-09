@extends('adminlte::page')

@section('title', 'Master Accounts')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Master Accounts</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item active">Master Accounts</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Daftar Master Account</h3>
                <a href="{{ route('master-account.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Master Account
                </a>
            </div>
        </div>
        <div class="card-body">
            {{-- Filter --}}
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama akun..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="software_id" class="form-control">
                            <option value="">-- Semua Software --</option>
                            @foreach($softwares as $software)
                            <option value="{{ $software->id }}" {{ request('software_id') == $software->id ? 'selected' : '' }}>
                                {{ $software->nama }} - {{ $software->tipe_paket }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">-- Semua Status --</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info btn-block">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('master-account.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Akun</th>
                            <th>Software</th>
                            <th>Slot</th>
                            <th>Usage</th>
                            <th>Status</th>
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($masterAccounts as $account)
                        <tr>
                            <td>
                                <strong>{{ $account->nama_akun }}</strong>
                                @if($account->email_akun)
                                <br><small class="text-muted"><i class="fas fa-envelope"></i> {{ $account->email_akun }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $account->software->nama }} - {{ $account->software->tipe_paket }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $account->available_slots > 0 ? 'success' : 'danger' }}">
                                    {{ $account->used_slots }}/{{ $account->max_slots }} terpakai
                                </span>
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $account->slot_usage_percentage > 80 ? 'bg-danger' : ($account->slot_usage_percentage > 60 ? 'bg-warning' : 'bg-success') }}" 
                                         role="progressbar" 
                                         style="width: {{ $account->slot_usage_percentage }}%">
                                        {{ $account->slot_usage_percentage }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                           class="custom-control-input toggle-status" 
                                           id="status-{{ $account->id }}" 
                                           data-id="{{ $account->id }}"
                                           {{ $account->status == 'active' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="status-{{ $account->id }}">
                                        <span class="badge badge-{{ $account->status == 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($account->status) }}
                                        </span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('master-account.show', $account) }}" class="btn btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('master-account.customers', $account) }}" class="btn btn-success" title="Customers">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    <a href="{{ route('master-account.edit', $account) }}" class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-delete" 
                                            data-id="{{ $account->id }}"
                                            data-name="{{ $account->nama_akun }}" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Belum ada data master account</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $masterAccounts->links() }}
            </div>
        </div>
    </div>

    {{-- Delete Form (Hidden) --}}
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@section('css')
@stop

@section('js')
<script>
$(document).ready(function() {
    // Toggle Status
    $('.toggle-status').on('change', function() {
        const id = $(this).data('id');
        const isChecked = $(this).is(':checked');
        const url = `{{ route('master-account.toggle-status', ':masterAccount') }}`.replace(':masterAccount', id);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    const badge = $(`#status-${id}`).siblings('label').find('.badge');
                    if(response.status === 'active') {
                        badge.removeClass('badge-danger').addClass('badge-success').text('Active');
                    } else {
                        badge.removeClass('badge-success').addClass('badge-danger').text('Inactive');
                    }
                }
            },
            error: function() {
                toastr.error('Gagal mengupdate status');
                $(`#status-${id}`).prop('checked', !isChecked);
            }
        });
    });

    // Delete Button
    $('.btn-delete').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            html: `Master Account <strong>${name}</strong> akan dihapus permanen!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = $('#delete-form');
                form.attr('action', `/admin/master-accounts/${id}`);
                form.submit();
            }
        });
    });
});
</script>
@stop
