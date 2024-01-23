@extends('adminlte::page')

@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h1>Product: {{ $product->name }}</h1>
            <h2>Activity Log</h2>
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
                                            <ul>
                                                @foreach ($activity->properties['attributes'] as $key => $value)
                                                    @if ($key == 'price_sell' || $key == 'price_buy')
                                                    <li>
                                                    @switch($key)
                                                        @case('price_sell')
                                                        <strong>Harga Jual :</strong>
                                                            @break
                                                        @case('price_buy')

                                                        <strong>Harga Beli :</strong>
                                                            @break
                                                    @endswitch
                                                            {{-- Format harga jika atribut adalah 'price_sell' atau 'price_buy' --}}
                                                            {{ 'Rp. '.number_format($value, 2) }}
                                                        </li>
                                                        @endif
                                                @endforeach
                                            </ul>
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
    </div>
@stop
@section('css')
<style>
        body {
            font-family: Arial, sans-serif;
            /* padding: 20px; */
            background-color: #f4f4f4;
        }
        .container {
            padding: 10px;
            border-radius: 5px;
        }
</style>
@stop