@extends('adminlte::page')

@section('content_header')
    <h1>Data Pekerja</h1>
@stop
@php
$no = ($employee->currentPage() - 1) * $employee->perPage() + 1;
$totalEmployee = $totalEmployee + 1; // Get the total number of projects

@endphp
@section('content')

<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Berhasil Menambahkan Pegawai</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Pegawai Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Berhasil Menghapus Pegawai</div>
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
    <p id="employeeNo"></p>
    
    @if(@$employeeEdit)
    <form action="{{ route('employee.update',$employeeEdit) }}" method="post">
    @method('put')
    @else
    <form action="{{ route('employee.store') }}" method="post">
    @endif
        @csrf
        <label for="nama">Nama Pekerja:</label>
        <input type="text" id="name" name="name" placeholder="Budiman" value="{{ old('name') ?? @$employeeEdit->name }}" required>
        
        <label for="handphone">No. Handphone:</label>
        <input type="text" id="phone" name="phone" placeholder="08568989080" value="{{ old('phone') ?? @$employeeEdit->phone }}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/^((0|62)[0-9]*)$/, '$1');" >
        
        <label for="gajiBulanan">Gaji Bulanan:</label>
        <input type="text" class="form-control" name="salary_montly_show" id="salary_montly_show"  oninput="formatRupiahFormat(this,'salary_monthly')" required/>
        <input type="hidden" id="salary_monthly" name="salary_monthly" placeholder="Rp 100.000.000" value="{{ old('salary_monthly') ?? @$employeeEdit->salary_monthly }}">
        
        <label for="gajiHarian">Gaji Harian:</label>

        <input type="text" class="form-control" name="salary_daily_show" id="salary_daily_show"  oninput="formatRupiahFormat(this,'salary_daily')" required/>
        <input type="hidden" id="salary_daily" name="salary_daily" placeholder="Rp 30.000.000"  value="{{ old('salary_daily') ?? @$employeeEdit->salary_daily }}">
    
        <button type="submit" class="btn btn-primary buttonForm">Simpan</button>
    </form>
    
    <hr>
    
    <h3>Daftar Pekerja</h3>
    
    <form action="{{ route('employee.index') }}" method="get">
        <div class="d-flex flex-row-reverse">
            <div class="p-2">
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
            </div>
            <div class="p-2">
                <input type="text" name="employee" class="form-control" placeholder="Search">
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <tr>
            <th>No Pekerja</th>
            <th>Nama Pekerja</th>
            <th>Aksi</th>
        </tr>
        @forelse($employee as $a)
        <tr>
            <td>
                {{ $no++ }}
            </td>
            <td>
                {{ $a->name }}
            </td>
            <td>
                <form method="post" action="{{ route('employee.destroy',$a) }}">
                    @csrf
                    @method('delete')
                    <a href="{{ route('employee.edit',$a->slug) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                    <button onclick="return window.confirm('{{ __('Apakah Anda Yakin ? ') }}')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3">
                Data Kosong
            </td>
        </tr>
        @endforelse
    </table>
    {{ $employee->withQueryString()->links('vendor.pagination.bootstrap-4') }}
</div>
@stop

@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script>
    $(document).ready(function () 
    {
        let nomor = "{{ $totalEmployee }}";
        document.getElementById('employeeNo').innerHTML = "No Pegawai :"+nomor;


        let salary_daily = document.getElementById("salary_daily").value;
        if (salary_daily) 
        {
            document.getElementById("salary_daily_show").value = salary_daily;
            formatRupiahFormat(document.getElementById("salary_daily_show"),"salary_daily"); // Format default value
        }

        let salary_monthly = document.getElementById("salary_monthly").value;
        if (salary_monthly) 
        {
            document.getElementById("salary_montly_show").value = salary_monthly;
            formatRupiahFormat(document.getElementById("salary_montly_show"),"salary_monthly"); // Format default value
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
        body {
            font-family: Arial, sans-serif;
            /* padding: 20px; */
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            padding: 10px;
            border-radius: 5px;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            box-sizing: border-box;
            border: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .buttonForm {
            /* padding: 10px 20px; */
            margin-top: 10px;
        }
    </style>
@stop

