@extends('adminlte::page')

@section('title', 'Edit Order Number')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Edit Order Number</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('subscription.index') }}">Subscriptions</a></li>
                <li class="breadcrumb-item"><a href="{{ route('subscription.show', $subscription->id) }}">{{ $subscription->order_number }}</a></li>
                <li class="breadcrumb-item active">Edit Order Number</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Subscription Info -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Subscription Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Order Number Saat Ini:</strong><br>
                            <span class="text-primary font-weight-bold">{{ $subscription->order_number }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Customer:</strong><br>
                            {{ $subscription->user->name }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Software:</strong><br>
                            {{ $subscription->masterAccount->software->nama ?? '-' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Package:</strong><br>
                            {{ $subscription->package->nama_paket ?? '-' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Status:</strong><br>
                            <span class="badge badge-{{ $subscription->status_badge }}">
                                {{ ucfirst($subscription->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-hashtag"></i> Update Order Number
                    </h3>
                </div>
                <form action="{{ route('subscription.update-order-number', $subscription->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Catatan:</strong> Order number harus unik — tidak boleh sama dengan subscription lain di perusahaan ini.
                        </div>

                        <div class="form-group">
                            <label for="order_number">Order Number <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('order_number') is-invalid @enderror"
                                   id="order_number"
                                   name="order_number"
                                   value="{{ old('order_number', $subscription->order_number) }}"
                                   placeholder="Contoh: INV/2026/XI/12345"
                                   required>
                            @error('order_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Nomor referensi order / invoice dari marketplace atau manual.
                            </small>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Update Order Number
                        </button>
                        <a href="{{ route('subscription.show', $subscription->id) }}" class="btn btn-default">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
