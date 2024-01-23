@extends('adminlte::page')

@section('content_header')
    <h1>Company</h1>
@stop
@php
$no = ($company->currentPage() - 1) * $company->perPage() + 1;

@endphp
@section('content')

<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Berhasil Menambahkan Perusahaan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Berhasil Mengubah Perusahaan</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Berhasil Menghapus Perusahaan</div>
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
    
    @canAccess('store','companies')
    @if(@$companyEdit)
    <form action="{{ route('company.update',$companyEdit) }}" method="post">
    @method('put')
    @else
    <form action="{{ route('company.store') }}" method="post">
    @endif
        @csrf
        @if(@$companyEdit)
        <div class="card">
            <div class="card-header">
                Company
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="name">Nama Perusahaan</label>
                    <input type="text" name="company_name" class="form-control" value="{{ old('company_name') ?? @$companyEdit->name }}" required>
                    @error('company_name')
                    <span class="text-danger text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-header">
                Company
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="name">Nama Perusahaan</label>
                    <input type="text" name="company_name" class="form-control" value="{{ old('name', isset($data['name']) ? $data['name'] : '') }}" required>
                    @error('company_name')
                    <span class="text-danger text-sm">{{ $message }}</span>
                    @enderror
                </div>
        
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', isset($data['address']) ? $data['address'] : '') }}">
                    @error('address')
                    <span class="text-danger text-sm">{{ $message }}</span>
                    @enderror
                </div>
        
                <div class="form-group">
                    <label for="no_npwp">No. NPWP</label>
                    <input type="text" name="npwp_number" class="form-control" value="{{ old('npwp_number', isset($data['npwp_number']) ? $data['npwp_number'] : '') }}">
                    @error('npwp_number')
                    <span class="text-danger text-sm">{{ $message }}</span>
                    @enderror
                </div>
        
                <div class="form-group">
                    <label for="direktur">Direktur</label>
                    <input type="text" name="director" class="form-control" value="{{ old('director', isset($data['director']) ? $data['director'] : '') }}" required>
                    @error('director')
                    <span class="text-danger text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="mata_uang_dasar">Template Perjanjian</label>
                    <select name="template_perjanjian" class="form-control">
                        @foreach($agreementTemplate as $index => $value)
                        <option value="{{ $index }}" >{{ $index }}</option>
                        @endforeach
                    </select>
                    @error('template_perjanjian')
                    <span class="text-danger text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                User Admin
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="name">Nama:</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Anwar" value="{{ old('name') ?? @$userEdit->name }}" required>
                    @error('name')
                    <span class="text-danger text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Budiman@gmail.com" value="{{ old('email') ?? @$userEdit->email }}" required>
                    @error('email')
                    <span class="text-danger text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" placeholder="08568989080" value="{{ old('phone') ?? @$userEdit->phone }}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/^((0|62)[0-9]*)$/, '$1');" >
                    @error('phone')
                    <span class="text-danger text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="direktur">Password</label>
                    <input type="password" name="password" class="form-control" autocomplete="off" required>
                    @error('password')
                    <span class="text-danger text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password:</label>
                    <input type="password" id="password_confirmation" class="form-control" name="password_confirmation" placeholder="**********" autocomplete="off" required>
                    @error('password_confirmation')
                    <span class="text-danger text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
        @endif
    
        <button type="submit" class="btn btn-primary buttonForm">Simpan</button>
    </form>
    
    <hr>
    
    <h3>Daftar Company</h3>

    {{-- 
    <form action="{{ route('company.index') }}" method="get">
        <div class="d-flex flex-row-reverse">
            <div class="p-2">
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
            </div>
            <div class="p-2">
                <input type="text" name="company" class="form-control" placeholder="Search">
            </div>
        </div>
    </form>
    --}}
    @endcanAccess

    <table class="table table-bordered">
        <tr>
            <th>No</th>
            <th>Company</th>
            <th>Aksi</th>
        </tr>
        @forelse($company as $a)
        <tr>
            <td>
                {{ $no++ }}
            </td>
            <td>
                {{ $a->name }}
            </td>
            <td>
                <form method="post" action="{{ route('company.destroy',$a) }}">
                    @csrf
                    @method('delete')
                    @canAccess('edit','companies')
                    <a href="{{ route('company.edit',$a->slug) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                    @endcanAccess
                    @canAccess('destroy','companies')
                    <button onclick="return window.confirm('{{ __('Apakah Anda Yakin ? ') }}')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                    @endcanAccess
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
    {{ $company->withQueryString()->links('vendor.pagination.bootstrap-4') }}
</div>
@stop

@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
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

