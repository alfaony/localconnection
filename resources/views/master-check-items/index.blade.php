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
            </div>
            <div class="input-group input-group-sm ml-2">
                <select name="type" id="type" class="form-control">
                    <option value="">Semua Tipe</option>
                    @foreach ($masterType as $key => $value)
                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="input-group input-group-sm ml-2">
                <button type="submit" class="btn btn-default btn-sm">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
    
    <div class="card-body p-0">
        <table class="table table-hover">
            <thead class="bg-light">
                <tr>
                    <th width="5%">#</th>
                    <th>Pemeriksaan</th>
                    <th width="15%">Tipe</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($checkItems as $item)
                <tr>
                    <td>{{ ($checkItems->currentPage() - 1) * $checkItems->perPage() + $loop->iteration }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ config('custom.master_type_check.'.$item->type) }} <p>{{ $item->itemCategory ? '('.$item->itemCategory->name .')' : '' }}</p></td>
                    <td>
                        @canAccess('edit', 'master_check_items')
                        <button class="btn btn-sm btn-warning edit-item" 
                                data-id="{{ $item->id }}"
                                data-name="{{ $item->name }}"
                                data-type="{{ $item->type }}"
                                data-category-id="{{ $item->item_category_id }}"
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
                        <label for="type_id">Tipe Item <span class="text-danger">*</span></label>
                        <select class="form-control" id="type_id" name="type" required>
                            <option value="">Pilih Tipe Item</option>
                            @foreach($masterType as $type => $title)
                            <option value="{{ $type }}">{{ $title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Kategori</label>
                        <select class="form-control" id="category_id" name="item_category_id">
                            <option value="">Pilih Kategori</option>
                            @foreach($itemCategories as $cat)
                            <option value="{{ $cat->id }}" data-type="{{ $cat->type }}">{{ $cat->name }}</option>
                            @endforeach
                            <option value="__create_new__">+ Tambah Kategori Baru</option>
                        </select>
                    </div>

                    <!-- Input hidden untuk kategori baru -->
                    <div class="form-group d-none" id="newCategoryWrapper">
                        <input type="text" id="new_category_name" class="form-control mt-2" placeholder="Masukkan nama kategori baru">
                        <input type="hidden" name="new_category_name" id="new_category_name_hidden">
                    </div>

                    <div class="form-group">
                        <label for="name">Pemeriksaan <span class="text-danger">*</span></label>
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
                        <label for="edit_type">Tipe Item <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_type" name="type" required>
                            <option value="">Pilih Tipe Item</option>
                            @foreach($masterType as $type => $title)
                                <option value="{{ $type }}">{{ $title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_item_category_id">Kategori </label>
                        <select class="form-control" id="edit_item_category_id" name="item_category_id">
                            <option value="">Pilih Kategori</option>
                            {{-- Akan diisi secara dinamis pakai JS --}}
                            @foreach ($itemCategories as $category)
                                <option value="{{ $category->id }}" data-type="{{ $category->type }}">{{ $category->name }}</option>
                            @endforeach
                            <option value="__create_new__">+ Tambah Kategori Baru</option>
                        </select>
                    </div>

                    <div class="form-group d-none" id="edit_new_category_container">
                        <label for="edit_new_category_name">Nama Kategori Baru <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_new_category_name" name="new_category_name">
                    </div>

                    <div class="form-group">
                        <label for="edit_name">Pemeriksaan <span class="text-danger">*</span></label>
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
        function filterCategories(type) {
            $('#category_id option').each(function () {
                const optionType = $(this).data('type');
                if (!optionType || optionType === type || $(this).val() === '__create_new__') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            $('#category_id').val('');
            $('#newCategoryWrapper').addClass('d-none');
            $('#new_category_name').val('');
            $('#new_category_name_hidden').val('');
        }

        $('#type_id').on('change', function () {
            const selectedType = $(this).val();
            filterCategories(selectedType);
        });

        $('#category_id').on('change', function () {
            if ($(this).val() === '__create_new__') {
                $('#newCategoryWrapper').removeClass('d-none');
            } else {
                $('#newCategoryWrapper').addClass('d-none');
                $('#new_category_name_hidden').val('');
            }
        });

        $('#new_category_name').on('input', function () {
            $('#new_category_name_hidden').val($(this).val());
        });

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
          $('.edit-item').on('click', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const type = $(this).data('type');
            const categoryId = $(this).data('category-id');

            $('#edit_id').val(id);
            $('#edit_name').val(name);
            $('#edit_type').val(type).trigger('change');

            // Reset kategori dropdown
            $('#edit_item_category_id option').each(function () {
                const optionType = $(this).data('type');
                if (!optionType || optionType === type) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            $('#edit_item_category_id').val(categoryId).trigger('change');
        });

        $('#edit_item_category_id').on('change', function () {
            if ($(this).val() === '__create_new__') {
                $('#edit_new_category_container').removeClass('d-none');
            } else {
                $('#edit_new_category_container').addClass('d-none');
            }
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