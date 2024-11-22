@extends('adminlte::page')

@section('title', 'Show Pass Checkings')

@section('content_header')
    <h1>Pass Checkings {{ $passChecking->name }}</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Pass Checking Details</h5>
        <hr>

        <div class="mb-3">
            <strong>Agenda:</strong>
            <p>{{ $passChecking->name ?? 'N/A' }}</p>
        </div>

        <div class="mb-3">
            <strong>Date:</strong>
            <p>{{ $passChecking->date ? \Carbon\Carbon::parse($passChecking->date)->format('d-m-Y') : '-' }}</p>
        </div>

        <div class="mb-3">
            <strong>Time:</strong>
            <p>
                Start: {{ $passChecking->start_time ? \Carbon\Carbon::parse($passChecking->start_time)->format('H:i') : '-' }} <br>
                End: {{ $passChecking->end_time ? \Carbon\Carbon::parse($passChecking->end_time)->format('H:i') : '-' }}
            </p>
        </div>

        <div class="mb-3">
            <strong>Pictures:</strong>
            <div class="d-flex flex-wrap gap-2">
                @if($passChecking->pictures && count($passChecking->pictures) > 0)
                    @foreach($passChecking->pictures as $picture)
                        <img src="{{ $picture }}" alt="Picture" class="img-thumbnail" width="200">
                    @endforeach
                @else
                    <p>No pictures available.</p>
                @endif
            </div>
        </div>

        <hr>
        <div class="d-flex justify-content-between">
            <a href="{{ route('pass-checking.index') }}" class="btn btn-secondary">Back</a>
            <div>
                <a href="{{ route('pass-checking.edit', $passChecking->id) }}" class="btn btn-warning">
                    <i class="fa fa-edit"></i> Edit
                </a>
                @if($passChecking->isDeleted())
                <form action="{{ route('pass-checking.destroy', $passChecking->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" onclick="return confirm('Are you sure?')">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
