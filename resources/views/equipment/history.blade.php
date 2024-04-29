@extends('adminlte::page')

@section('content_header')
    <h1>Detail Perlengkapan: {{ $equipment->name }}</h1>
@stop

@section('content')
<div class="container my-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Informasi Perlengkapan</h5>
        </div>
        <div class="card-body bg-light">
            <h6 class="card-title"><strong>Kode:</strong> {{ $equipment->code }}</h6>
            <p class="card-text"><strong>Perlengkapan:</strong> {{ $equipment->name }}</p>
            <p class="card-text"><strong>Stok Tersedia:</strong> {{ $equipment->total_stock }}</p>
        </div>

        <div class="row">
                <div class="col-md-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Performed By</th>
                                <th>Created At</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activities as $activity)
                                <tr>
                                    <td>{{ $activity->description }}</td>
                                    <td>{{ $activity->causer ? $activity->causer->name : 'System' }}</td>
                                    <td>{{ $activity->created_at }}</td>
                                    <td>
                                        @if ($activity->properties)
                                            @foreach ($activity->properties['attributes'] as $key => $value)
                                                @if ($key == 'total_stock')
                                                    Total Stok : {{ $value }}
                                                    @endif
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">No activity recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
</div>
@endsection
