<div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('router.index') }}">Router</a></li>
            <li class="breadcrumb-item active" aria-current="page">Router Dashboard</li>
        </ol>
    </nav>
    
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800 router-title"><i class="bi bi-router me-2"></i>Router Inventory</h1>
                    <div class="d-flex">
                        <button wire:click="resyncLight" class="btn btn-secondary btn-action me-2 mb-2 mr-1">
                            <i class="bi bi-arrow-repeat me-1"></i> Resync Now
                        </button>
                        <button wire:click="resyncProfiles" class="btn btn-primary btn-action me-2 mb-2 mr-1">
                            <i class="bi bi-search me-1"></i> Scan Profiles
                        </button>
                        <button wire:click="resyncSecretsSessions" class="btn btn-success btn-action mb-2 mr-1">
                            <i class="bi bi-arrow-left-right me-1"></i> Sync Secrets + Sessions
                        </button>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Interfaces</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($interfaces) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-hdd-stack fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Address Pools</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($pools) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-collection fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">PPPoE Servers</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($pppoes) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-wifi fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Router</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $router->host }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-devices fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-hdd-stack me-2"></i>Interfaces</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>VLAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($interfaces as $i)
                                    <tr>
                                        <td>{{ $i->name }}</td>
                                        <td><span class="badge bg-secondary">{{ $i->role }}</span></td>
                                        <td>{{ $i->vlan_id }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-collection me-2"></i>Address Pools</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>CIDR / Ranges</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pools as $p)
                                    <tr>
                                        <td>{{ $p->name }}</td>
                                        <td><code>{{ $p->cidr }}</code></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-wifi me-2"></i>PPPoE Servers</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Service</th>
                                        <th>Interface</th>
                                        <th>Pool</th>
                                        <th>Only One</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pppoes as $s)
                                    <tr>
                                        <td>{{ $s->service_name }}</td>
                                        <td>{{ optional($s->interface)->name }}</td>
                                        <td>{{ optional($s->addressPool)->name }}</td>
                                        <td>
                                            @if($s->only_one)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
<script>
     window.addEventListener('toast', (event) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
        
        Toast.fire({
            icon: event.detail.type,
            title: event.detail.message
        });
    });
</script>
@endpush
@push('css')
<style>
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e3e6f0;
    }
    .table-responsive {
        border-radius: 0.35rem;
    }
    .btn-action {
        min-width: 120px;
    }
    .router-title {
        color: #4e73df;
    }
    .stat-card {
        border-left: 4px solid #4e73df;
    }
</style>
@endpush
