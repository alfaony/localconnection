@extends('adminlte::page')

@section('title', 'Permintaan Barang')

@section('content_header')
    <h1>Permintaan Barang</h1>
@stop

@canAccess('dataTableJson', 'item_requests')

@section('content')

@include('components.alert')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Daftar Permintaan Barang</h3>
                @canAccess('create', 'item_requests')
                <div class="ml-auto">
                    <a href="{{ route('item-request.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus-circle mr-1"></i>Tambah Permintaan
                    </a>
                </div>
                @endcanAccess
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table id="item-request-table" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Estimasi Harga</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- DataTables will handle rows --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script>
    $(document).ready(function () {
        $('#filter-status').on('change', function () {
            $('#item-request-table').DataTable().ajax.reload();
        });


        $('#item-request-table').DataTable({
            processing: true,
            serverSide: true,
             ajax: {
                url: "{{ route('item-request.datatable') }}",
                data: function (d) {
                    d.status = $('#filter-status').val(); // ⬅️ Filter dikirim ke server
                }
            },
            columns: 
            [
                { data: 'item_name', name: 'item_name' },
                { data: 'category.name', name: 'category.name' },
                { data: 'qty', name: 'qty' },
                { data: 'estimated_price', name: 'estimated_price' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            language: {
                processing: '<i class="fas fa-spinner fa-spin"></i> Memuat...'
            },
            initComplete: function () {
            // Inject dropdown ke sebelah search box
            const filterHtml = `
                <label class="ml-3 mb-0">
                    <select id="filter-status" class="form-control form-control-sm">
                        <option value="" selected>All</option>
                        @foreach($stepsRequest as $key => $values)
                            <option value="{{ $key }}">{{ $values }}</option>
                        @endforeach
                    </select>
                </label>`;
            
            $('#item-request-table_filter').append(filterHtml);

            // Trigger reload saat filter berubah
            $('#filter-status').on('change', function () {
                $('#item-request-table').DataTable().ajax.reload();
            });
        }
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Delete confirmation
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Hapus Permintaan?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            })
        });
    });
</script>
@endsection

@endcanAccess

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css"> 
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
@endsection
@stop