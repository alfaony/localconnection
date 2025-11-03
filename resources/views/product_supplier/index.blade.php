@extends('adminlte::page')

@section('title', 'Supplier Produk')

@section('content_header')
    <h1>Supplier Produk</h1>
@stop

@section('content')
@include('components.alert')
<div class="card mt-3 d-none" id="progressCard">
    <div class="card-header">
        <h3 class="card-title">Progres Import</h3>
    </div>
    <div class="card-body">
        <div class="progress">
            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
        <p class="mt-2" id="progressText">{{__('please_wait')}}...</p>
        <div id="errorContainer" class="alert alert-danger mt-2 d-none"></div>
    </div>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        @canAccess('store','product_suppliers')
        <a href="{{ route('product-supplier.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Supplier</a>
        @endcanAccess
        <div class="ml-auto">
            <form action="{{ route('product-supplier.index') }}" method="GET" class="form-inline d-flex">
                @canAccess('import','product_suppliers')
                <button class="btn btn-primary mr-2" type="button" data-toggle="modal" data-target="#importModal"><i class="fas fa-file-import"></i>  Supplier</button>
                @endcanAccess
                <div class="input-group">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Search suppliers..." 
                           value="{{ request('search') }}"
                           aria-label="Search">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-3">Pemilik</th>
                        <th class="py-3">Nama</th>
                        <th class="py-3">Telepon</th>
                        <th class="py-3">Kategori</th>
                        <th class="py-3 text-end">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td class="align-middle">
                                {{ $supplier->owner_name }}
                            </td>
                            <td class="align-middle">{{ $supplier->store_name }} {{ $supplier->supplierType ? '('.$supplier->supplierType->name.')' : '' }}</td>
                            <td class="align-middle">
                                {{ $supplier->phone_number }}
                            </td>
                            <td class="align-middle">
                                @foreach($supplier->supplierCategories as $category)
                                    <span class="badge bg-info bg-opacity-10 text-info me-1">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="align-middle text-end">
                                <div class="d-inline-flex gap-2">

                                    @canAccess('show','product_suppliers')
                                    <a href="{{ route('product-supplier.show', $supplier->id) }}" 
                                       class="btn btn-sm btn-outline-info mr-2"
                                       data-bs-toggle="tooltip" 
                                       title="Show">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcanAccess
                                    
                                    @canAccess('edit','product_suppliers')
                                    <a href="{{ route('product-supplier.edit', $supplier->id) }}" 
                                       class="btn btn-sm btn-outline-warning mr-2"
                                       data-bs-toggle="tooltip" 
                                       title="Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    @endcanAccess

                                    @canAccess('destroy','product_suppliers')
                                    <form action="{{ route('product-supplier.destroy', $supplier->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure?')"
                                                data-bs-toggle="tooltip" 
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcanAccess
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center text-muted">
                                    <i class="fas fa-box-open fa-2x mb-2"></i>
                                    No suppliers found
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($suppliers->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $suppliers->withQueryString()->links('vendor.pagination.bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Product Suppliers</h5>
                <button type="button" id="closeImportModal" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- <form  action="{{ route('product-supplier.import') }}" method="POST" enctype="multipart/form-data"> -->
            <form id="importForm">
                <!-- @csrf -->
                <div class="modal-body">
                    <div class="form-group">
                        <label for="importFile">Upload File (Excel)</label>
                        <input type="file" class="form-control" name="file" id="importFile" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="startImport" class="btn btn-primary">Start Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let batchId = null;

    $('#importForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $('#progressCard').removeClass('d-none');
        $('#progressBar').css('width', '0%').text('0%');
        $('#progressText').text('Mengunggah...');
        $('#errorContainer').addClass('d-none').empty();

        $.ajax({
            url: "{{ route('product-supplier.import') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $("#closeImportModal").click();

                batchId = response.batchId;
                $('#progressText').text("Import dimulai...");
                checkProgress();
            },
            error: function(xhr) {
                alert('Gagal mengunggah file');
                $('#progressCard').addClass('d-none');
            }
        });
    });

    function checkProgress() {
        if (!batchId) return;

        $.ajax({
            url: "{{ url('product-supplier/importProgress') }}/" + batchId,
            type: "GET",
            success: function(response) {
                let progress = Math.round(response.progress);
                $('#progressBar').css('width', progress + '%').text(progress + '%');
                $('#progressText').text(`Diproses: ${response.processed} / ${response.total} baris`);

                if (progress < 100) {
                    setTimeout(checkProgress, 2000);
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Selesai!',
                        text: 'Import selesai!',
                        timer: 2000,
                        showConfirmButton: false,
                    }).then(() => {
                        $('#progressCard').addClass('d-none');
                        location.reload();
                    });
                    if (response.errors.length > 0) {
                        $('#errorContainer').removeClass('d-none').html(response.errors.join("<br>"));
                    }
                }
            },
            error: function() {
                $('#progressText').text("Gagal mendapatkan progres!");
            }
        });
    }
</script>
@stop
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.2.0/sweetalert2.min.css">
@stop
