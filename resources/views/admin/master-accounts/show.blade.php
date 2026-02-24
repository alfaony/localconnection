@extends('adminlte::page')

@section('title', 'Detail Master Account')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Detail Master Account</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master-account.index') }}">Master Accounts</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Account Info Card -->
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Informasi Akun</h3>
                </div>
                <div class="card-body box-profile">
                    <div class="text-center mb-3">
                        @if($masterAccount->software->logo_url)
                            <img class="img-fluid" 
                                 src="{{ asset('storage/' . $masterAccount->software->logo_url) }}" 
                                 alt="{{ $masterAccount->software->nama_software }}"
                                 style="max-height: 150px; object-fit: contain;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 150px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                    </div>

                    <h3 class="profile-username text-center">{{ $masterAccount->nama_akun }}</h3>

                    <p class="text-muted text-center">
                        <a href="{{ route('software.show', $masterAccount->software->id) }}">
                            {{ $masterAccount->software->nama_software }}
                        </a>
                    </p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Software</b> 
                            <span class="float-right">{{ $masterAccount->software->nama_software }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Status</b> 
                            <span class="float-right">{!! $masterAccount->status_badge !!}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Max Slots</b> 
                            <span class="float-right">
                                <span class="badge badge-primary">{{ $masterAccount->max_slots }}</span>
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Used Slots</b> 
                            <span class="float-right">
                                <span class="badge badge-warning">{{ $masterAccount->used_slots }}</span>
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Available Slots</b> 
                            <span class="float-right">
                                <span class="badge badge-success">
                                    {{ $masterAccount->max_slots - $masterAccount->used_slots }}
                                </span>
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Slot Usage</b>
                            <div class="mt-2">
                                <div class="progress" style="height: 25px;">
                                    @php
                                        $percentage = ($masterAccount->max_slots > 0) ? ($masterAccount->used_slots / $masterAccount->max_slots * 100) : 0;
                                        $barColor = $percentage >= 80 ? 'danger' : ($percentage >= 60 ? 'warning' : 'success');
                                    @endphp
                                    <div class="progress-bar bg-{{ $barColor }}" 
                                         role="progressbar" 
                                         style="width: {{ $percentage }}%"
                                         aria-valuenow="{{ $masterAccount->used_slots }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="{{ $masterAccount->max_slots }}">
                                        {{ number_format($percentage, 0) }}%
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <div class="row">
                        <div class="col-6">
                            <a href="{{ route('master-account.edit', $masterAccount->id) }}" 
                               class="btn btn-primary btn-block">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                        <div class="col-6">
                            <form action="{{ route('master-account.destroy', $masterAccount->id) }}" 
                                  method="POST" 
                                  class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timestamps Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Timestamps</h3>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>Created:</strong> {{ $masterAccount->created_at->format('d M Y H:i') }}<br>
                        <strong>Updated:</strong> {{ $masterAccount->updated_at->format('d M Y H:i') }}
                    </small>
                </div>
            </div>
        </div>

        <!-- Credentials & Customers -->
        <div class="col-md-8">
            <!-- Credentials Card -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-key"></i> Credentials Access
                    </h3>
                </div>
                <div class="card-body">
                    @if($masterAccount->email_akun)
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Email Akun:</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           value="{{ $masterAccount->email_akun }}" 
                                           readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary copy-btn" 
                                                type="button"
                                                data-text="{{ $masterAccount->email_akun }}">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($masterAccount->password_akun)
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Password:</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control password-field" 
                                           value="{{ $masterAccount->password_akun ?? ''}}" 
                                           readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary toggle-password" 
                                                type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary copy-btn" 
                                                type="button"
                                                data-text="{{ $masterAccount->password_akun ?? ''}}">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($masterAccount->pin_code)
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">PIN Code:</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           value="{{ $masterAccount->pin_code }}" 
                                           readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary copy-btn" 
                                                type="button"
                                                data-text="{{ $masterAccount->pin_code }}">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($masterAccount->link_invite)
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Link Invite:</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           value="{{ $masterAccount->link_invite }}" 
                                           readonly>
                                    <div class="input-group-append">
                                        <a href="{{ $masterAccount->link_invite }}" 
                                           target="_blank"
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-external-link-alt"></i> Open
                                        </a>
                                        <button class="btn btn-outline-secondary copy-btn" 
                                                type="button"
                                                data-text="{{ $masterAccount->link_invite }}">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($masterAccount->file_attachment)
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">File Attachment:</label>
                            <div class="col-sm-9">
                                <a href="{{ asset('storage/' . $masterAccount->file_attachment) }}" 
                                   target="_blank"
                                   class="btn btn-info">
                                    <i class="fas fa-download"></i> Download File
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($masterAccount->instruksi_akses)
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Instruksi Akses:</label>
                            <div class="col-sm-9">
                                <div class="card">
                                    <div class="card-body">
                                        {!! $masterAccount->instruksi_akses !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(!$masterAccount->email_akun && !$masterAccount->password_akun && !$masterAccount->pin_code && !$masterAccount->link_invite && !$masterAccount->file_attachment && !$masterAccount->instruksi_akses)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            No credentials information available for this master account.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Active Customers Card -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i> Active Customers 
                        <span class="badge badge-light">{{ $masterAccount->subscriptions()->where('status', 'active')->count() }}</span>
                    </h3>
                </div>
                <div class="card-body p-0">
                    @php
                        $activeCustomers = $masterAccount->subscriptions()
                            ->where('status', 'active')
                            ->where('payment_status', 'paid')
                            ->with('user', 'package')
                            ->latest()
                            ->get();
                    @endphp

                    @if($activeCustomers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Order Number</th>
                                        <th>Customer</th>
                                        <th>Package</th>
                                        <th>Start Date</th>
                                        <th>Expires</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeCustomers as $subscription)
                                        <tr>
                                            <td>
                                                <a href="{{ route('subscription.show', $subscription->id) }}">
                                                    {{ $subscription->order_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <strong>{{ $subscription->user->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $subscription->user->email }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $subscription->package->nama_paket }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ \Carbon\Carbon::parse($subscription->tanggal_mulai)->format('d M Y') }}</small>
                                            </td>
                                            <td>
                                                <small>{{ \Carbon\Carbon::parse($subscription->tanggal_expired)->format('d M Y') }}</small>
                                                @if($subscription->isExpiringSoon(7))
                                                    <br><span class="badge badge-warning">Expiring Soon</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('subscription.show', $subscription->id) }}" 
                                                   class="btn btn-sm btn-info"
                                                   title="View Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No active customers yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- All Subscriptions Card -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> All Subscriptions History
                    </h3>
                </div>
                <div class="card-body p-0">
                    @php
                        $allSubscriptions = $masterAccount->subscriptions()
                            ->with('user', 'package')
                            ->latest()
                            ->get();
                    @endphp

                    @if($allSubscriptions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Order Number</th>
                                        <th>Customer</th>
                                        <th>Package</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Payment</th>
                                        <th>Expired</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allSubscriptions as $subscription)
                                        <tr>
                                            <td>
                                                <a href="{{ route('subscription.show', $subscription->id) }}">
                                                    <small>{{ $subscription->order_number }}</small>
                                                </a>
                                            </td>
                                            <td><small>{{ $subscription->user->name }}</small></td>
                                            <td><small>{{ $subscription->package->nama_paket }}</small></td>
                                            <td class="text-center">{!! $subscription->status_badge !!}</td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $subscription->payment_status == 'paid' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($subscription->payment_status) }}
                                                </span>
                                            </td>
                                            <td><small>{{ carbon\carbon::parse($subscription->tanggal_expired)->format('d M Y') }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No subscription history.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.box-profile {
    padding: 20px;
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function() {
    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This will also affect all active subscriptions!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Toggle password visibility
    $('.toggle-password').on('click', function() {
        const input = $(this).closest('.input-group').find('.password-field');
        const icon = $(this).find('i');
        
        if(input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Copy to clipboard
    $('.copy-btn').on('click', function() {
        const text = $(this).data('text');
        
        navigator.clipboard.writeText(text).then(function() {
            toastr.success('Copied to clipboard!');
        }, function() {
            toastr.error('Failed to copy');
        });
    });
});
</script>
@stop
