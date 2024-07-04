@extends('adminlte::page')

@section('title', 'Kategori Produk')

@section('content_header')
    <h1>Daftar Kategori Produk</h1>
@stop

@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
        <div class="alert alert-success mt-3">Kategori berhasil ditambahkan!</div>
    @endif
    @if(Session::get('update'))
        <div class="alert alert-success mt-3">Kategori berhasil diperbarui!</div>
    @endif
    @if(Session::get('delete'))
        <div class="alert alert-success mt-3">Kategori berhasil dihapus!</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>


<div class="card py-3">
    <div class="card-body">
        <form id="categoryForm" method="post" action="{{ route('product-category.store') }}">
            @csrf
            <input type="hidden" id="categoryId" name="category_id" value="">
            <div class="form-group">
                <label for="name">Nama Kategori</label>
                <input type="text" class="form-control" id="categoryName" name="name" placeholder="Masukkan nama kategori" value="{{ old('name') }}" required>
            </div>
            <button type="submit" class="btn btn-primary" id="formSubmitButton">Simpan</button>
            <button type="button" class="btn btn-secondary" id="formCancelButton" style="display:none;">Batal</button>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">Daftar Kategori</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $index => $category)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $category->name }}</td>
                        <td>
                            <button class="btn btn-primary btn-sm edit-category" data-id="{{ $category->slug }}" data-name="{{ $category->name }}">Edit</button>
                            <form action="{{ route('product-category.destroy', $category->slug) }}" method="post" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.edit-category').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            $('#categoryForm').attr('action', '{{ url("product-category") }}/' + id);
            $('#categoryForm').append('<input type="hidden" name="_method" value="PUT">');
            $('#categoryId').val(id);
            $('#categoryName').val(name);
            $('#formSubmitButton').text('Perbarui');
            $('#formCancelButton').show();
        });

        $('#formCancelButton').click(function() {
            $('#categoryForm').attr('action', '{{ route("product-category.store") }}');
            $('#categoryForm').find('input[name="_method"]').remove();
            $('#categoryId').val('');
            $('#categoryName').val('');
            $('#formSubmitButton').text('Simpan');
            $('#formCancelButton').hide();
        });
    });
</script>
@stop

@section('css')
<style>
    .table td, .table th {
        vertical-align: middle;
    }

    .card {
        margin-bottom: 20px;
    }
</style>
@stop
