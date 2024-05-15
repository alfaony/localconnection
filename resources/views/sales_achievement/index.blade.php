@extends('adminlte::page')

@section('content')
<div class="container p-3 mt-3">
    <h2 class="mb-4">Pencapaian Penjualan Bulanan</h2>
    <div class="mb-4">
         @canAccess('store','sales_achievements')
        <a href="{{ route('sales_achievement.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Pencapaian</a>
        @endcanAccess
    </div>

    @canAccess('index','sales_achievements')
    <form method="GET" action="{{ route('sales_achievement.index') }}" class="mb-3">
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
                <th class="col-1">Periode</th>
                <th class="col-2">Penjapaian</th>
                <th class="col-1">Total Presentasi</th>
                <th class="col-1">Total Penawaran</th>
                <th class="col-1">Poin</th>
                <th class="col-2">Status</th>
                <th class="col-1">Pengajuan</th>
                <th class="col-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($achievements as $achievement)
                <tr>
                    <td>{{ $achievement->period_show }}</td>
                    <td>Rp {{ number_format($achievement->sales_amount, 2) }}</td>
                    <td>{{ $achievement->total_presentations }}</td>
                    <td>{{ $achievement->total_offers_issued }}</td>
                    <td>{{ $achievement->points ?? "-"}}</td>
                    <td>
                        @switch($achievement->status)
                            @case('in review')
                                <i class="fa fa-eye" style="color: green;"></i> In Review
                                @break
                            @case('complete')
                                <i class="fa fa-check" style="color: green;"></i> Complete
                                @break
                        @endswitch
                    </td>
                    <td>
                        {{ $achievement->user->name ?? "" }}
                    </td>
                    <td>
                        @canAccess('shhow','sales_achievements')
                        <a href="{{ route('sales_achievement.show', $achievement->slug) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
                        @endcanAccess
                        @if(!$achievement->approved)
                        @canAccess('edit','sales_achievements')
                        <a href="{{ route('sales_achievement.edit', $achievement->slug) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                        @endcanAccess
                        @canAccess('destroy','sales_achievements')
                        <form action="{{ route('sales_achievement.destroy', $achievement->slug) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                        </form>
                        @endcanAccess
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No Sales Achievements Found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
        {{ $achievements->withQueryString()->links('vendor.pagination.bootstrap-4') }}
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
