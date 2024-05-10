
@extends('adminlte::page')

@section('content_header')
    <h1>List Akses</h1>
@stop

@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Akses Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Akses Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Akses Berhasil Terhapus</div>
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
        @canAccess('store','assets')
        <div class="card-body">            
            <form action="{{ isset($asset) ? route('asset.update', $asset->slug) : route('asset.store') }}" method="POST">
                @csrf
                @if(isset($asset))
                    @method('PUT')
                @endif
                <div class="form-group">
                    <label for="name">Akses</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $asset->name ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label for="equipment_id">Jenis Akses</label>
                    <select class="form-control" id="asset_type_id" name="asset_type_id">
                        @foreach ($assetTypes as $assetType)
                            <option value="{{ $assetType->id }}" {{ (isset($asset) && $asset->asset_type_id == $assetType->id) ? 'selected' : '' }}>
                                {{ ucfirst($assetType->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">{{ isset($asset) ? 'Ubah' : 'Simpan' }}</button>
                </form>
            </div>
        </div>
        @endcanAccess

        <div class="card-body">
            <form action="{{ route('asset.index') }}" method="get">
                <div class="d-flex flex-row-reverse">
                    <div class="p-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                    </div>
                    <div class="p-2">
                        <input type="text" name="asset" class="form-control" placeholder="Search">
                    </div>
                    <div class="p-2">
                    @php
                        $order = request('order', 'desc');
                    @endphp
                        <select name="order" class="form-control">
                            <option value="asc" {{ $order == 'asc' ? 'selected' : '' }} >A - Z Created By</option>
                            <option value="desc" {{ $order == 'desc' ? 'selected' : '' }}>Z - A Created By</option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="table-responsive-md">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Akses</th>
                            <th>Pic</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assets as $asset)
                        <tr>
                            <td>{{ $asset->name }}</td>
                            <td>
                                @if($asset->latestAssetAssign)
                                    @if(isset($asset->latestAssetAssign->returned_date))
                                        {{ $asset->latestAssetAssign->userReceived ? $asset->latestAssetAssign->userReceived->name : ''}}
                                    @else
                                        {{ $asset->latestAssetAssign->user ? $asset->latestAssetAssign->user->name : ''}}
                                    @endif
                                @else
                                    {{ $asset->user->name }}
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('asset.destroy', $asset->slug) }}" method="POST" style="display: inline-block;">
                                    @canAccess('update','assets')
                                    <a href="{{ route('asset.show', $asset->slug) }}" class="btn btn-primary"><i class="fa fa-eye"></i></a>
                                    @endcanAccess
                                    @canAccess('update','assets')
                                    <a href="{{ route('asset.edit', $asset->slug) }}" class="btn btn-info"><i class="fa fa-edit"></i></a>
                                    @endcanAccess
                                    @csrf
                                    @method('DELETE')
                                    @canAccess('destroy','assets')
                                    <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                    @endcanAccess
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $assets->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
</div>
@stop
@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
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

