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
                                    <td>{{ \Carbon\Carbon::parse($activity->created_at)->format('d-m-Y') }}</td>
                                    <td>
                                        @if ($activity->properties && isset($activity->properties['old']['total_stock'] ))
                                            @php
                                                $now = $activity->properties['attributes']['total_stock'];
                                                $before = $activity->properties['old']['total_stock'];
                                                
                                                $status = $now <= $before ? "Dikurang" : "Ditambah";
                                                $different = $now <= $before ? $before - $now :  $now - $before;
                                            @endphp

                                            <p>Stok Sebelumnya: {{ $activity->properties['old']['total_stock'] }} </p>
                                            <p>{{ $status }}: {{ $different }} </p>
                                            <p>Stok Saat ini: {{ $activity->properties['attributes']['total_stock'] }}</p>
                                        @else
                                            <p>Stok Saat ini: {{ $activity->properties['attributes'] ? $activity->properties['attributes']['total_stock'] : ""}}</p>
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
