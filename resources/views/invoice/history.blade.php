@extends('adminlte::page')

@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h1>Invoice #{{ $invoice->number_result }}</h1>
            <h2>Activity Log</h2>
            <div class="row">
                <div class="col-md-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Number</th>
                                <th>Activity</th>
                                <th>Performed By</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1 @endphp
                            @forelse ($activities as $activity)
                                <tr>
                                    <td>
                                        {{ $no++ }}
                                    </td>
                                    <td>{{ $activity->description }}</td>
                                    <td>{{ $activity->causer ? $activity->causer->name : 'System' }}</td>
                                    <td>{{ $activity->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No activity recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }
    .container {
        padding: 10px;
        border-radius: 5px;
    }
</style>
@stop
