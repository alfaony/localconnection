@extends('adminlte::page')

@section('title', 'Detail Software')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Detail Software</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.softwares.index') }}">Software</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Software Info Card -->
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Informasi Software</h3>
                </div>
                <div class="card-body box-profile">
                    <div class="text-center">
                        @if($software->logo_url)
                            <img class="img-fluid" 
                                 src="{{ asset('storage/' . $software->logo_url) }}" 
                                 alt="{{ $software->nama_software }}"
                                 style="max-height: 200px; object-fit: contain;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
                                <i class="fas fa-image fa-4x text-muted"></i>
                            </div>
                        @endif
                    </div>

                    <h3 class="profile-username text-center mt-3">{{ $software->nama_software }}</h3>

                    <p class="text-muted text-center">{{ $software->tipe_paket }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Slug</b> <span class="float-right text-muted">{{ $software->slug }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Status</b> 
                            <span class="float-right">{!! $software->status_badge !!}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Total Packages</b> 
                            <span class="float-right">
                                <span class="badge badge-info">{{ $software->packages->count() }}</span>
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Active Packages</b> 
                            <span class="float-right">
                                <span class="badge badge-success">{{ $software->packages->where('status', 'active')->count() }}</span>
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Master Accounts</b> 
                            <span class="float-right">
                                <span class="badge badge-primary">{{ $software->masterAccounts->count() }}</span>
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Total Slots</b> 
                            <span class="float-right">
                                <span class="badge badge-secondary">{{ $software->masterAccounts->sum('max_slots') }}</span>
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Used Slots</b> 
                            <span class="float-right">
                                <span class="badge badge-warning">{{ $software->masterAccounts->sum('used_slots') }}</span>
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Available Slots</b> 
                            <span class="float-right">
                                <span class="badge badge-success">
                                    {{ $software->masterAccounts->sum('max_slots') - $software->masterAccounts->sum('used_slots') }}
                                </span>
                            </span>
                        </li>
                    </ul>

                    <div class="row">
                        <div class="col-6">
                            <a href="{{ route('admin.softwares.edit', $software->id) }}" class="btn btn-primary btn-block">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                        <div class="col-6">
                            <form action="{{ route('admin.softwares.destroy', $software->id) }}" 
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

            <!-- Description Card -->
            @if($software->description)
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Deskripsi</h3>
                </div>
                <div class="card-body">
                    <p>{{ $software->description }}</p>
                </div>
            </div>
            @endif

            <!-- Timestamps Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Timestamps</h3>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>Created:</strong> {{ $software->created_at->format('d M Y H:i') }}<br>
                        <strong>Updated:</strong> {{ $software->updated_at->format('d M Y H:i') }}
                    </small>
                </div>
            </div>
        </div>

        <!-- Packages & Master Accounts -->
        <div class="col-md-8">
            <!-- Packages Card -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-box"></i> Packages
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.softwares.packages.create', $software->id) }}" 
                           class="btn btn-sm btn-light">
                            <i class="fas fa-plus"></i> Add Package
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($software->packages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Package</th>
                                        <th class="text-center">Durasi</th>
                                        <th class="text-right">Harga</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($software->packages as $package)
                                        <tr>
                                            <td>
                                                <strong>{{ $package->nama_paket }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info">
                                                    {{ $package->durasi_hari }} hari
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <strong>Rp {{ number_format($package->harga, 0, ',', '.') }}</strong>
                                            </td>
                                            <td class="text-center">
                                                {!! $package->status_badge !!}
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.softwares.packages.edit', [$software->id, $package->id]) }}" 
                                                       class="btn btn-sm btn-info"
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-{{ $package->status == 'active' ? 'warning' : 'success' }} toggle-status" 
                                                            data-id="{{ $package->id }}"
                                                            data-software-id="{{ $software->id }}"
                                                            title="Toggle Status">
                                                        <i class="fas fa-{{ $package->status == 'active' ? 'ban' : 'check' }}"></i>
                                                    </button>
                                                    <form action="{{ route('admin.softwares.packages.destroy', [$software->id, $package->id]) }}" 
                                                          method="POST" 
                                                          class="delete-form"
                                                          style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-danger"
                                                                title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-box fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada package.</p>
                            <a href="{{ route('admin.softwares.packages.create', $software->id) }}" 
                               class="btn btn-success">
                                <i class="fas fa-plus"></i> Add First Package
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Master Accounts Card -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-key"></i> Master Accounts
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.master-accounts.create', ['software_id' => $software->id]) }}" 
                           class="btn btn-sm btn-light">
                            <i class="fas fa-plus"></i> Add Account
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($software->masterAccounts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Akun</th>
                                        <th class="text-center">Slot Usage</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($software->masterAccounts as $account)
                                        <tr>
                                            <td>
                                                <strong>{{ $account->nama_akun }}</strong>
                                                @if($account->email_akun)
                                                    <br><small class="text-muted">{{ $account->email_akun }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="progress" style="height: 20px;">
                                                    @php
                                                        $percentage = ($account->max_slots > 0) ? ($account->used_slots / $account->max_slots * 100) : 0;
                                                        $barColor = $percentage >= 80 ? 'danger' : ($percentage >= 60 ? 'warning' : 'success');
                                                    @endphp
                                                    <div class="progress-bar bg-{{ $barColor }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $percentage }}%"
                                                         aria-valuenow="{{ $account->used_slots }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="{{ $account->max_slots }}">
                                                        {{ $account->used_slots }}/{{ $account->max_slots }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                {!! $account->status_badge !!}
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.master-accounts.show', $account->id) }}" 
                                                       class="btn btn-sm btn-primary"
                                                       title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.master-accounts.edit', $account->id) }}" 
                                                       class="btn btn-sm btn-info"
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-key fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada master account.</p>
                            <a href="{{ route('admin.master-accounts.create', ['software_id' => $software->id]) }}" 
                               class="btn btn-warning">
                                <i class="fas fa-plus"></i> Add First Account
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Active Subscriptions Card -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i> Active Subscriptions
                    </h3>
                </div>
                <div class="card-body">
                    @php
                        $activeSubscriptions = $software->subscriptions()
                            ->where('status', 'active')
                            ->where('payment_status', 'paid')
                            ->with('user', 'package')
                            ->latest()
                            ->take(10)
                            ->get();
                    @endphp

                    @if($activeSubscriptions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Order Number</th>
                                        <th>Customer</th>
                                        <th>Package</th>
                                        <th>Expires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeSubscriptions as $subscription)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.subscriptions.show', $subscription->id) }}">
                                                    {{ $subscription->order_number }}
                                                </a>
                                            </td>
                                            <td>{{ $subscription->user->name }}</td>
                                            <td>{{ $subscription->package->nama_paket }}</td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $subscription->tanggal_expired->format('d M Y') }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($software->subscriptions()->where('status', 'active')->count() > 10)
                            <div class="text-center mt-2">
                                <a href="{{ route('admin.subscriptions.index', ['software_id' => $software->id]) }}" 
                                   class="btn btn-sm btn-outline-info">
                                    View All Subscriptions
                                </a>
                            </div>
                        @endif
                    @else
                        <p class="text-muted text-center mb-0">No active subscriptions yet.</p>
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

<script>
$(document).ready(function() {
    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
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

    // Toggle package status
    $('.toggle-status').on('click', function() {
        const packageId = $(this).data('id');
        const softwareId = $(this).data('software-id');
        const button = $(this);
        
        $.ajax({
            url: `/admin/softwares/${softwareId}/packages/${packageId}/toggle-status`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error('Failed to update status');
                }
            },
            error: function() {
                toastr.error('An error occurred');
            }
        });
    });
});
</script>
@stop
