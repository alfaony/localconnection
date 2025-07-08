@extends('adminlte::page')

@section('title', 'Master Item Pemeriksaan')

@section('content_header')
    @canAccess('create', 'master_check_items')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Master Item Pemeriksaan</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">
            <i class="fas fa-plus mr-1"></i> Tambah Baru
        </button>
    </div>
    @endcanAccess
@stop

@section('content')
@component('components.alert')
<div class="card">
    <div class="card-header">
        <form action="{{ route('master-check-item.index') }}" method="GET" class="form-inline">
            <div class="input-group input-group-sm">
                <input type="text" name="search" class="form-control" placeholder="Cari item..." value="{{ request('search') }}">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <div class="card-body p-0">
        <table class="table table-hover">
            <thead class="bg-light">
                <tr>
                    <th width="5%">#</th>
                    <th>Nama Item</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($checkItems as $item)
                <tr>
                    <td>{{ ($checkItems->currentPage() - 1) * $checkItems->perPage() + $loop->iteration }}</td>
                    <td>{{ $item->name }}</td>
                    <td>
                        @canAccess('edit', 'master_check_items')
                        <button class="btn btn-sm btn-warning edit-item" 
                                data-id="{{ $item->id }}"
                                data-name="{{ $item->name }}"
                                data-toggle="modal" 
                                data-target="#editModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        @endcanAccess
                        @canAccess('destroy', 'master_check_items')
                        <form action="{{ route('master-check-item.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus item ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endcanAccess
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-2x mb-2 text-muted"></i>
                        <p class="text-muted">Tidak ada data item pemeriksaan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($checkItems->hasPages())
    <div class="card-footer">
        {{ $checkItems->withQueryString()->links('vendor.pagination.bootstrap-4') }}
    </div>
    @endif
</div>


@canAccess('store', 'master_check_items')
<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="createModalLabel">Tambah Item Pemeriksaan</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Nama Item <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <small class="form-text text-muted">Contoh: Layar, Keyboard, Touchpad, dll.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcanAccess

@canAccess('update', 'master_check_items')
<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editModalLabel">Edit Item Pemeriksaan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name">Nama Item <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcanAccess
@stop

@section('css')
<style>
    .pagination {
        margin: 0;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        // Handle create form submission
        $('#createForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: "{{ route('master-check-item.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        const createModal = new bootstrap.Modal(document.getElementById('createModal'));
                        createModal.hide();
                        $('#createForm')[0].reset();
                        showToast('success', response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = '';
                    
                    if (errors) {
                        $.each(errors, function(key, value) {
                            errorMessage += value[0] + '<br>';
                        });
                    } else {
                        errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
                    }
                    
                    showToast('error', errorMessage);
                }
            });
        });
        
        // Handle edit button click
        $('.edit-item').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            $('#edit_id').val(id);
            $('#edit_name').val(name);
        });
        
        // Handle edit form submission
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#edit_id').val();
            
            $.ajax({
                url: `/master-check-item/${id}`,
                method: "POST",
                data: $(this).serialize() + '&_method=PUT',
                success: function(response) {
                    if (response.success) {
                        const modal = new bootstrap.Modal(document.getElementById('editModal'));
                        modal.hide();
                        showToast('success', response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = '';
                    
                    if (errors) {
                        $.each(errors, function(key, value) {
                            errorMessage += value[0] + '<br>';
                        });
                    } else {
                        errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
                    }
                    
                    showToast('error', errorMessage);
                }
            });
        });
        
        // Show toast notification
        function showToast(type, message) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            
            Toast.fire({
                icon: type,
                title: message
            });
        }
    });
</script>
@stop