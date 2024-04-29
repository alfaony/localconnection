@extends('adminlte::page')

@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Pemimjaman Berhasil</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Pengembalian Berhasil</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Peminjaman Berhasil Terhapus</div>
    @endif


</div>
<div class="container pt-4">
    <div class="card">
        <div class="card-header">
            Detail Aset: {{ $asset->name }}
        </div>
        <div class="card-body">
            <h5 class="card-title">{{ $asset->name }}</h5>
            <p class="card-text"><strong>Status: </strong>{{ $asset->status }}</p>
            <p class="card-text"><strong>Pic: </strong>@if($asset->latestAssetAssign)
                                                            @if(isset($asset->latestAssetAssign->returned_date))
                                                                {{ $asset->latestAssetAssign->userReceived->name }}
                                                            @else
                                                                {{ $asset->latestAssetAssign->user->name }}
                                                            @endif
                                                        @else
                                                            {{ $asset->user->name }}
                                                        @endif</p>

            @if($asset->latestAssetAssign && (!isset($asset->latestAssetAssign->returned_date)))
            @php
             $assetAssign = $asset->latestAssetAssign;
            @endphp
            @canAccess('update','asset_assigns')
            <div class="border-top pt-3">
                <h5>Form Pengembalian</h5>
                <form action="{{ route('asset-assign.update',$assetAssign->slug) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group mb-3">
                        <label for="picked_up_date">Tanggal Pengembalian:</label>
                        <input type="date" class="form-control" id="returned_date" name="returned_date" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Pengembalian</button>
                </form>
            </div>
            @endcanAccess
            @else
            @canAccess('store','asset_assigns')
            <div class="border-top pt-3">
                <h5>Form Peminjaman</h5>
                <form action="{{ route('asset-assign.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                    <div class="form-group mb-3">
                        <label for="assigned_to_user_id">Peminjam:</label>
                        <select class="form-control" id="assigned_to_user_id" name="assigned_to_user_id" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="picked_up_date">Tanggal Pinjam:</label>
                        <input type="date" class="form-control" id="picked_up_date" name="picked_up_date" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Pinjam</button>
                </form>
            </div>
            @endcanAccess
            @endif

            <div class="border-top pt-3 mt-5">
                <h5>Riwayat Peminjaman</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Peminjam</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($assetAssigns as $assign)
                    <tr>
                        <td>{{ $assign->user->name }}</td>
                        <td>{{ $assign->picked_up_date }}</td>
                        <td>{{ $assign->returned_date ?? 'Belum dikembalikan' }}</td>
                        <td>
                        @canAccess('destroy','asset_assigns')
                        <form action="{{ route('asset-assign.destroy', $assign->slug) }}" method="POST" style="display: inline-block;">
                            @canAccess('destroy','task_assigns')
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                            @endcanAccess
                        </form>
                        @endcanAccess
                    </td>
                    </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
