
@extends('adminlte::page')

@section('content_header')
    <h1>List Produk</h1>
@stop
@php
$no = ($product->currentPage() - 1) * $product->perPage() + 1;


@endphp
@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Produk Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Produk Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Produk Berhasil Terhapus</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<div class="container">
    <div class="card">
        <div class="card-body">            
            @if(@$productEdit)
            <form method="post" action="{{ route('product.update',$productEdit) }}">
            @php $totalProducts = $nomor; @endphp
            @method('put')
            @else
            <form method="post" action="{{ route('product.store') }}">
            @php $totalProducts = $totalProduct + 1; @endphp
            @endif
                @csrf
                <div class="form-group">
                    <p id="productNo"></p>
                </div>
                <div class="form-group">
                    <label>Nama Produk/Jasa:</label>
                    <input type="text" class="form-control" placeholder="Google Ads" name="name"  value="{{ old('name') ?? @$productEdit->name }}" required>
                </div>
                <div class="form-group">
                    <label>Harga Jual:</label>
                    <input type="text" class="form-control"  id="price_buy_show" placeholder="Rp 30.000.000" oninput="formatRupiahFormat(this,'price_buy')" required/>
                    <input type="hidden" id="price_buy" name="price_buy" name="name"  value="{{ old('price_buy') ?? @$productEdit->price_buy }}">
                </div>
                <div class="form-group">
                    <label>Harga Beli:</label>
                    <input type="text" class="form-control" id="price_sell_show"  placeholder="Rp 30.000.000" oninput="formatRupiahFormat(this,'price_sell')" required/>
                    <input type="hidden" id="price_sell" name="price_sell" name="price_sell"  value="{{ old('price_sell') ?? @$productEdit->price_sell }}">
                </div>
        
                <div class="form-group">
                    <label>Satuan Penghitung:</label>
                    <input type="text" class="form-control" name="method_count"  value="{{ old('method_count') ?? @$productEdit->method_count }}" required>
                </div>
                
                <button class="btn btn-primary">Simpan</button>
            </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <form action="{{ route('product.index') }}" method="get">
                    <div class="d-flex flex-row-reverse">
                        <div class="p-2">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                        </div>
                        <div class="p-2">
                            <input type="text" name="product" class="form-control" placeholder="Search">
                        </div>
                    </div>
                </form>
            
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    @forelse($product as $a)
                    <tr>
                        <td>{{ $no }}</td>
                        <td>{{ $a->name }}</td>
                        <td>
                            <form method="post" action="{{ route('product.destroy',$a) }}">
                                @csrf
                                @method('delete')
                                <a href="{{ route('product.edit',$a->slug).'?nomor='.$no }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                                <button onclick="return window.confirm('{{ __('Apakah Anda Yakin Hapus Data ? ') }}')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @php $no++ @endphp
                    @empty
                    <tr>
                        <td colspan="3">
                            <center>Data Kosong</center>
                        </td>
                    </tr>
                    @endforelse
                </table>
                {{ $product->withQueryString()->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
</div>
@stop
@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script>
    $(document).ready(function () 
    {

        let nomor = "{{ $totalProducts }}";
        document.getElementById('productNo').innerHTML = "No Produk :"+nomor;

        let price_buy = document.getElementById("price_buy").value;
        if (price_buy) 
        {
            document.getElementById("price_buy_show").value = price_buy;
            formatRupiahFormat(document.getElementById("price_buy_show"),"price_buy"); // Format default value
        }

        let price_sell = document.getElementById("price_sell").value;
        if (price_sell) 
        {
            document.getElementById("price_sell_show").value = price_sell;
            formatRupiahFormat(document.getElementById("price_sell_show"),"price_sell"); // Format default value
        }
    });
    function formatRupiahFormat(input, inputNonFormat) 
    {
        let numStr = input.value.toString().replace(/[^,\d]/g, '');
        let split = numStr.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;

        if (numStr === "" || parseInt(numStr) === 0) {
            input.value = '';
            numStr = '';
        } else {
            // Menghapus angka 0 di depan jika input diawali dengan 0
            rupiah = rupiah.replace(/^0+/, '');
            input.value = 'Rp '+rupiah;
        }

        // Update 'salary' input with non-formatted number
        document.getElementById(inputNonFormat).value = parseInt(numStr);
    }
</script>
@stop
@section('css')
    <style>
        body 
        {
            font-family: Arial, sans-serif;
            /* padding: 20px; */
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            padding: 10px;
            border-radius: 5px;
        }

        .btn-custom {
            background-color: #007bff;
            color: #ffffff;
            border-radius: 4px;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .pagination > li > a {
            color: #007bff;
            background-color: transparent;
            border: none;
        }

        .pagination > .active > a {
            background-color: #007bff;
            color: #ffffff;
        }
    </style>
@stop

