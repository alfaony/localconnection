
<div>
    <nav aria-label="breadcrumb mt-2">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('router.index') }}">Router</a></li>
            <li class="breadcrumb-item active" aria-current="page">Mapping Package → PPP Profile</li>
        </ol>
    </nav>
    <div class="row mt-1">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Mapping Package → PPP Profile</h3>
                    <div class="card-tools">
                        <span class="badge badge-light">Router: {{ $router->host }}</span>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 35%">Package</th>
                                    <th>ROS Profile</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $packageId => $row)
                                    <tr>
                                        <td class="font-weight-bold">{{ $row['package_name'] }}</td>
                                        <td>
                                            <input type="text" 
                                                   class="form-control" 
                                                   wire:model.defer="rows.{{ $packageId }}.ros_profile"
                                                   placeholder="e.g., PLAN_10M" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer text-right">
                    <button wire:click="save" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Mapping
                    </button>
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
@section('css')
    <style>
        .table th {
            background-color: #f8f9fa;
        }
        .card-title {
            font-weight: 600;
        }
    </style>
@stop