@extends('adminlte::page')

{{-- @section('title', 'User') --}}

@section('content_header')
    <h1>Role List</h1>
@stop

@section('content')

@php $no = 1; @endphp

<div class="container">
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 d-flex gap-2">
                    <a href="{{ route('role.create') }}" class="btn btn-primary btn-sm mr-2">
                        <i class="fa fa-plus"></i> Create
                    </a>

                    @canAccess('clearAllCache','roles')
                    <form action="{{ route('role.clear-all-cache') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="button" class="btn btn-warning btn-sm" id="btnClearAllCache">
                            <i class="fas fa-broom"></i> Clear All Cache
                        </button>
                    </form>
                    @endcanAccess
                </div>
            </div>

            @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <p class="mb-0">{{ $message }}</p>
            </div>
            @endif

            @if ($message = Session::get('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <p class="mb-0">{{ $message }}</p>
            </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Name</th>
                                <th width="220">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($role as $a)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $a->name }}</td>
                                <td>
                                    <a class="btn btn-xs btn-default text-teal mx-1 shadow" href="{{ route('role.show', $a) }}" title="Lihat">
                                        <i class="fa fa-lg fa-fw fa-eye"></i>
                                    </a>
                                    <a class="btn btn-xs btn-default text-primary mx-1 shadow" href="{{ route('role.edit', $a) }}" title="Edit">
                                        <i class="fa fa-lg fa-fw fa-pen"></i>
                                    </a>

                                    {{-- Duplicate --}}
                                    @canAccess('duplicate','roles')
                                    <form action="{{ route('role.duplicate', $a) }}" method="POST" style="display:inline;" class="form-duplicate">
                                        @csrf
                                        <button type="button"
                                                class="btn btn-xs btn-default text-warning mx-1 shadow btn-duplicate"
                                                data-name="{{ $a->name }}"
                                                title="Duplikat">
                                            <i class="fa fa-lg fa-fw fa-copy"></i>
                                        </button>
                                    </form>
                                    @endcanAccess

                                    {{-- Delete --}}
                                    <form action="{{ route('role.destroy', $a) }}" method="POST" style="display:inline;" class="form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                class="btn btn-xs btn-default text-danger mx-1 shadow btn-delete"
                                                data-name="{{ $a->name }}"
                                                title="Hapus">
                                            <i class="fa fa-lg fa-fw fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Data Kosong</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
$(document).ready(function () {

    // Duplicate button
    $(document).on('click', '.btn-duplicate', function () {
        const name = $(this).data('name');
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Duplikat Role?',
            html: `Role <strong>${name}</strong> akan diduplikat beserta seluruh permission-nya.<br><small class="text-muted">Nama baru: <em>Copy of ${name}</em></small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-copy"></i> Ya, Duplikat!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Delete button
    $(document).on('click', '.btn-delete', function () {
        const name = $(this).data('name');
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Hapus Role?',
            html: `Role <strong>${name}</strong> akan dihapus permanen.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-trash"></i> Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Clear All Cache button
    $('#btnClearAllCache').on('click', function () {
        Swal.fire({
            title: 'Clear Semua Cache Role?',
            html: 'Ini akan menghapus cache permission <strong>semua role</strong> dari Redis.<br><small class="text-muted">User akan memuat ulang permission dari database.</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e67e22',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-broom"></i> Ya, Clear Cache!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).closest('form').submit();
            }
        });
    });

});
</script>
@endpush
