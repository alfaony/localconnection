@extends('adminlte::page')

@section('title', 'Edit Expiry Date')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Edit Expiry Date</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('subscription.index') }}">Subscriptions</a></li>
                <li class="breadcrumb-item"><a href="{{ route('subscription.show', $subscription->id) }}">{{ $subscription->order_number }}</a></li>
                <li class="breadcrumb-item active">Edit Expiry</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
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
                            <strong>Order Number:</strong><br>
                            {{ $subscription->order_number }}
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
                            {{ $subscription->software->nama ?? ""}}
                        </div>
                        <div class="col-md-4">
                            <strong>Package:</strong><br>
                            {{ $subscription->package->nama_paket ?? ""}}
                        </div>
                        <div class="col-md-4">
                            <strong>Status:</strong><br>
                            {!! $subscription->status_badge !!}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Current Start Date:</strong><br>
                            <span class="text-primary">{{ \Carbon\Carbon::parse($subscription->tanggal_mulai)->format('d M Y') }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Current Expiry Date:</strong><br>
                            <span class="text-danger">{{ \Carbon\Carbon::parse($subscription->tanggal_expired)->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt"></i> Update Expiry Date
                    </h3>
                </div>
                <form action="{{ route('subscription.update-expiry', $subscription->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Mengubah tanggal expired akan memperpanjang atau mempersingkat masa aktif subscription.
                        </div>

                        <div class="form-group">
                            <label for="tanggal_expired">New Expiry Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('tanggal_expired') is-invalid @enderror" 
                                   id="tanggal_expired" 
                                   name="tanggal_expired" 
                                   value="{{ old('tanggal_expired', $subscription->tanggal_expired) }}"
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            @error('tanggal_expired')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Pilih tanggal baru untuk expired subscription ini.
                            </small>
                        </div>

                        <!-- Quick Actions -->
                        <div class="form-group">
                            <label>Quick Extend</label>
                            <div class="btn-group btn-group-sm d-block" role="group">
                                <button type="button" class="btn btn-outline-primary extend-btn" data-days="7">
                                    +7 Days
                                </button>
                                <button type="button" class="btn btn-outline-primary extend-btn" data-days="14">
                                    +14 Days
                                </button>
                                <button type="button" class="btn btn-outline-primary extend-btn" data-days="30">
                                    +1 Month
                                </button>
                                <button type="button" class="btn btn-outline-primary extend-btn" data-days="90">
                                    +3 Months
                                </button>
                                <button type="button" class="btn btn-outline-primary extend-btn" data-days="180">
                                    +6 Months
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes (Optional)</label>
                            <textarea class="form-control" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3"
                                      placeholder="Alasan perubahan tanggal expired...">{{ old('notes') }}</textarea>
                            <small class="form-text text-muted">
                                Catatan internal untuk perubahan ini.
                            </small>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Update Expiry Date
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

@section('js')
<script>
$(document).ready(function() {
    // Quick extend buttons
    $('.extend-btn').on('click', function() {
        const days = $(this).data('days');
        const currentDate = new Date('{{ $subscription->tanggal_expired }}');
        const newDate = new Date(currentDate.setDate(currentDate.getDate() + days));
        
        const year = newDate.getFullYear();
        const month = String(newDate.getMonth() + 1).padStart(2, '0');
        const day = String(newDate.getDate()).padStart(2, '0');
        
        $('#tanggal_expired').val(`${year}-${month}-${day}`);
    });
});
</script>
@stop