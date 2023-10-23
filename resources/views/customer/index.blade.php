
@extends('adminlte::page')

@section('content_header')
    <h1>Customer</h1>
@stop
@php
$no = ($customer->currentPage() - 1) * $customer->perPage() + 1;
@endphp

@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Customer Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Customer Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Customer Berhasil Terhapus</div>
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
             @if(@$customerEdit)
            <form method="post" action="{{ route('customer.update',$customerEdit) }}">
            @php $totalCustomers = $nomor; @endphp
            @method('put')
            @else
            <form method="post" action="{{ route('customer.store') }}">
            @php $totalCustomers = $totalCustomer + 1; @endphp
            @endif
                @csrf
                <div class="form-group">
                    <p id="customerNo"></p>
                </div>
                <div class="form-group">
                    <label>Nama Customer:</label>
                    <input type="text" class="form-control" placeholder="PT. Toyota Astra Motor" name="name" value="{{ old('name') ?? @$customerEdit->name }}" required>
                </div>
                <div class="form-group">
                    <label>Direktur:</label>
                    <input type="text" class="form-control" placeholder="Anton" name="director" value="{{ old('director') ?? @$customerEdit->director }}" required>
                </div>
                <div class="form-group">
                    <label>Penanggung Jawab:</label>
                    <input type="text" class="form-control" placeholder="Hendra" name="pic" value="{{ old('pic') ?? @$customerEdit->pic }}" required>
                </div>
                <div class="form-group">
                    <label>Pemberi Tugas:</label>
                    <input type="text" class="form-control" placeholder="Michele" name="assignor" value="{{ old('assignor') ?? @$customerEdit->assignor }}" required>
                </div>
                <div class="form-group">
                    <label>Alamat:</label>
                    <input type="text" class="form-control" placeholder="Jl. Sunter Raya II Jakarta Utara" name="address" value="{{ old('address') ?? @$customerEdit->address }}" required>
                </div>
                <div class="form-group">
                    <label>No. Handphone:</label>
                    <input type="text" name="phone" class="form-control" placeholder="Handphone" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/^((0|62)[0-9]*)$/, '$1');" value="{{ old('phone') ?? @$customerEdit->phone }}">
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="text" class="form-control" placeholder="name@gmail.com" name="email" value="{{ old('email') ?? @$customerEdit->email }}" required>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <form action="{{ route('customer.index') }}" method="get">
                <div class="d-flex flex-row-reverse">
                    <div class="p-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                    </div>
                    <div class="p-2">
                        <input type="text" name="customer" class="form-control" placeholder="Search">
                    </div>
                </div>
            </form>
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Customer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($customer as $a)
                    <tr>
                        <td>{{ $no }}</td>
                        <td>{{ $a->name }}</td>
                        <td>
                            <form method="post" action="{{ route('customer.destroy',$a) }}">
                                @csrf
                                @method('delete')
                                <a href="{{ route('customer.edit',$a->slug).'?nomor='.$no }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
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
                    </tbody>
                </table>
                {{ $customer->withQueryString()->links('vendor.pagination.bootstrap-4') }}
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
        let nomor = "{{ $totalCustomers }}";
        document.getElementById('customerNo').innerHTML = "No Customer :"+nomor;
    });
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
