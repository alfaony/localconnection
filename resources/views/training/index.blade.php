@extends('adminlte::page')

@section('content')
<div class="container p-3 mt-3">
    <h2 class="mb-4">Daftar Pelatihan</h2>
    @canAccess('store','trainings')
    <a href="{{ route('training.create') }}" class="btn btn-primary mb-4"><i class="fa fa-plus"></i> Pelatihan</a>
    @endcanAccess
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @canAccess('index','trainings')
    <form method="GET" action="{{ route('training.index') }}" class="mb-3">
        <div class="row g-1 align-items-end">
            <div class="col-12 col-md-2">
                <select class="form-control" id="task" name="status">
                    <option selected disabled>-- Status --</option>
                    @foreach($status as $key => $values)
                    <option value="{{ $key }}">{{ $values }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-auto mt-2">
                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Search</button>
            </div>
        </div>
    </form>
    @endcanAccess

    <div class="table-responsive-md">
        <table class="table table-striped">
        <thead>
            <tr>
                <th>Nama Pelatihan</th>
                <th>Tanggal Pelatihan</th>
                <th>Status</th>
                <th>Pengajuan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($trainings as $training)
                <tr>
                    <td>{{ $training->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($training->certification_date)->format('d-m-Y') }}
                    <td>
                        @switch($training->status)
                            @case('in review')
                                <i class="fa fa-eye" style="color: green;"></i> In Review
                                @break
                            @case('complete')
                                <i class="fa fa-check" style="color: green;"></i> Complete
                                @break
                        @endswitch
                    </td>
                    <td>{{ $training->user->name ?? "" }}</td>
                    <td>
                        @canAccess('show','trainings')
                        <a href="{{ route('training.show', $training->slug) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
                        @endcanAccess
                        @if(!$training->point)
                        @canAccess('edit','trainings')
                        <a href="{{ route('training.edit', $training->slug) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                        @endcanAccess
                        @canAccess('destroy','trainings')
                        <form action="{{ route('training.destroy', $training->slug) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"><i class="fa fa-trash"></i></button>
                        </form>
                        @endcanAccess
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $trainings->withQueryString()->links('vendor.pagination.bootstrap-4') }}

</div>
</div>
@endsection
@section('css')
    <style>
        body
        {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            border-radius: 5px;
        }
    </style>
@stop
