@extends('adminlte::page')

@section('title', 'Master Partner Type')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-tags"></i> Master Partner Type</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Partner Types</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')
    <div class="row mb-3">
        <div class="col-12">
            @canAccess('create','partner_types')
            <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">
                <i class="fas fa-plus"></i> Tambah Partner Type
            </button>
            @endcanAccess
        </div>
    </div>

    <!-- Partner Types Table -->
    <div class="card">
        <div class="card-body">
            @if($partnerTypes->count() > 0)
                <table class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Jumlah Partner</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($partnerTypes as $type)
                            <tr>
                                <td><strong>{{ $type->name }}</strong></td>
                                <td>
                                    @if($type->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Non-Aktif</span>
                                    @endif
                                </td>
                                <td>{{ $type->partners->count() }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        @canAccess('edit','partner_types')   
                                        <button class="btn btn-sm btn-warning mb-1 mr-1" title="Edit" 
                                                data-toggle="modal" data-target="#editModal" 
                                                data-id="{{ $type->id }}" 
                                                data-name="{{ $type->name }}" 
                                                data-is_active="{{ $type->is_active ? 1 : 0 }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endcanAccess
                                        @canAccess('destroy','partner_types')
                                        <form action="{{ route('partner-type.destroy', $type) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tipe ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger mb-1 mr-1" title="Delete" {{ $type->partners->count() > 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcanAccess
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $partnerTypes->links('vendor.pagination.bootstrap-4') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada Partner Type data.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('partner-type.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createModalLabel">Tambah Partner Type</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nama Tipe <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                                <label class="custom-control-label" for="is_active">Status Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Partner Type</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_name">Nama Tipe <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active" value="1">
                                <label class="custom-control-label" for="edit_is_active">Status Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $('#editModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var name = button.data('name');
        var isActive = button.data('is_active');
        
        var formAction = "{{ url('partner-type') }}/" + id;
        
        var modal = $(this);
        modal.find('#editForm').attr('action', formAction);
        modal.find('#edit_name').val(name);
        modal.find('#edit_is_active').prop('checked', isActive == 1);
    });
</script>
@stop
