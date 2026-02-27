@extends('adminlte::page')

@section('title', 'Change Master Account')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Change Master Account</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('subscription.index') }}">Subscriptions</a></li>
                <li class="breadcrumb-item"><a href="{{ route('subscription.show', $subscription->id) }}">{{ $subscription->order_number }}</a></li>
                <li class="breadcrumb-item active">Change Account</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')
<div class="row">
    <div class="col-md-12">
        <!-- Subscription Info -->
        @include('components.alert')
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
                            {{ $subscription->user->name  ?? ""}}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Software:</strong><br>
                            {{ $subscription->software->nama ?? ""}}
                        </div>
                        <div class="col-md-6">
                            <strong>Package:</strong><br>
                            {{ $subscription->package->nama_paket ?? ""}}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Current Master Account:</strong><br>
                            @if($subscription->masterAccount)
                                <div class="alert alert-info mt-2">
                                    <i class="fas fa-key"></i>
                                    <strong>{{ $subscription->masterAccount->nama_akun }}</strong>
                                    <br>
                                    <small>
                                        Email: {{ $subscription->masterAccount->email_akun ?? '-' }}
                                        | Slot Usage: {{ $subscription->masterAccount->used_slots }}/{{ $subscription->masterAccount->max_slots }}
                                    </small>
                                </div>
                            @else
                                <span class="text-muted">No master account assigned</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Form -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exchange-alt"></i> Select New Master Account
                    </h3>
                </div>
                <form action="{{ route('subscription.update-master-account', $subscription->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> Mengubah master account akan mengubah credentials yang diterima customer. Pastikan account tujuan masih memiliki slot available.
                        </div>
    
                        <div class="form-group">
                            <label for="master_account_id">New Master Account <span class="text-danger">*</span></label>
                            <select class="form-control @error('master_account_id') is-invalid @enderror" 
                                    id="master_account_id" 
                                    name="master_account_id" 
                                    required>
                                <option value="">-- Select Master Account --</option>
                                @foreach($masterAccounts as $account)
                                    <option value="{{ $account->id }}" 
                                            {{ old('master_account_id') == $account->id ? 'selected' : '' }}
                                            data-slots="{{ $account->used_slots }}"
                                            data-max="{{ $account->max_slots }}">
                                        {{ $account->nama_akun }} 
                                        - Available: {{ $account->max_slots - $account->used_slots }}/{{ $account->max_slots }} slots
                                        @if($account->email_akun)
                                            ({{ $account->email_akun }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('master_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Hanya master account dengan slot available yang ditampilkan.
                            </small>
                        </div>

                        @if($masterAccounts->isEmpty())
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle"></i>
                                <strong>No Available Accounts!</strong> Semua master account untuk software ini sudah penuh. 
                                <a href="{{ route('master-account.create', ['software_id' => $subscription->software_id]) }}" class="alert-link">
                                    Tambahkan master account baru
                                </a> atau tingkatkan max_slots pada account yang ada.
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="reason">Reason for Change <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" 
                                      id="reason" 
                                      name="reason" 
                                      rows="3"
                                      placeholder="Alasan perubahan master account..."
                                      required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Wajib diisi untuk audit trail.
                            </small>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="notify_customer" 
                                   name="notify_customer" 
                                   value="1"
                                   {{ old('notify_customer', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify_customer">
                                Send email notification to customer with new credentials
                            </label>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning" {{ $masterAccounts->isEmpty() ? 'disabled' : '' }}>
                            <i class="fas fa-exchange-alt"></i> Change Master Account
                        </button>
                        <a href="{{ route('subscription.show', $subscription->id) }}" class="btn btn-default">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Available Accounts List -->
            @if($masterAccounts->count() > 0)
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Available Master Accounts
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Account Name</th>
                                    <th>Email</th>
                                    <th class="text-center">Slot Usage</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($masterAccounts as $account)
                                    <tr>
                                        <td><strong>{{ $account->nama_akun }}</strong></td>
                                        <td>{{ $account->email_akun ?? '-' }}</td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 20px;">
                                                @php
                                                    $percentage = ($account->max_slots > 0) ? ($account->used_slots / $account->max_slots * 100) : 0;
                                                    $barColor = $percentage >= 80 ? 'danger' : ($percentage >= 60 ? 'warning' : 'success');
                                                @endphp
                                                <div class="progress-bar bg-{{ $barColor }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $percentage }}%">
                                                    {{ $account->used_slots }}/{{ $account->max_slots }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">{!! $account->status_badge !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
@stop