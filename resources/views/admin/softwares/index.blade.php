@extends('adminlte::page')

@section('title', 'Software Management')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Software Management</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item active">Software</li>
            </ol>
        </div>
    </div>
    @stop
    
@section('content')
@include('components.alert')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Daftar Software</h3>
                @canAccess('create', 'software')
                <a href="{{ route('software.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Software
                </a>
                @endcanAccess
            </div>
        </div>
        <div class="card-body">
            {{-- Filter --}}
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari software..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
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
                        <a href="{{ route('software.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover" id="softwaresTable">
                    <thead>
                        <tr>
                            <th width="80">Logo</th>
                            <th>Nama Software</th>
                            <th>Tipe Paket</th>
                            <th>Packages</th>
                            <th>Master Accounts</th>
                            @canAccess('toggleStatus', 'software')
                            <th>Status</th>
                            @endcanAccess
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($softwares as $software)
                        <tr>
                            <td>
                                @if($software->logo)
                                <img src="{{ s3_asset(true,10,$software->logo) }}" alt="{{ $software->nama }}" class="img-thumbnail" style="max-width: 60px;">
                                @else
                                <div class="bg-secondary text-white text-center" style="width: 60px; height: 60px; line-height: 60px;">
                                    <i class="fas fa-image"></i>
                                </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $software->nama }}</strong>
                                <br>
                                <small class="text-muted">{{ $software->slug }}</small>
                                <br>
                                <small class="text-muted">{{ $software->pic->name ?? "" }}</small>
                                
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $software->tipe_paket }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('software.packages.index', $software) }}" class="badge badge-primary">
                                    {{ $software->packages->count() }} packages
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success">
                                    {{ $software->masterAccounts->count() }} accounts
                                </span>
                            </td>
                            @canAccess('toggleStatus', 'software')
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                           class="custom-control-input toggle-status" 
                                           id="status-{{ $software->id }}" 
                                           data-id="{{ $software->id }}"
                                           {{ $software->status == 'active' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="status-{{ $software->id }}">
                                        <span class="badge badge-{{ $software->status == 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($software->status) }}
                                        </span>
                                    </label>
                                </div>
                            </td>
                            @endcanAccess
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    @canAccess('show', 'software')
                                    <a href="{{ route('software.show', $software) }}" class="btn btn-info mb-1 mr-1" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcanAccess
                                    @canAccess('edit', 'software')
                                    <a href="{{ route('software.edit', $software) }}" class="btn btn-warning mb-1 mr-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcanAccess
                                    @canAccess('destroy', 'software')
                                    <button type="button" class="btn btn-danger btn-delete mb-1 mr-1" 
                                            data-id="{{ $software->id }}"
                                            data-name="{{ $software->nama }}" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcanAccess
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Belum ada data software</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $softwares->withQueryString()->links('vendor.pagination.bootstrap-4') }}
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
        let url = "{{ route('software.toggleStatus', ':id') }}".replace(':id', id);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    // Update badge
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
                // Revert checkbox
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
            html: `Software <strong>${name}</strong> akan dihapus permanen!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = $('#delete-form');
                form.attr('action', `/admin/softwares/${id}`);
                form.submit();
            }
        });
    });
});
</script>
@stop
