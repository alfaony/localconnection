
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
                    @if(empty($availablePools))
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Belum ada Address Pool untuk router ini. Silakan tambahkan pool terlebih dahulu.
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:22%">Package</th>
                                    <th style="width:22%">ROS Profile</th>
                                    <th style="width:28%">
                                        Address Pool
                                        <small class="text-muted font-weight-normal">(remote-address)</small>
                                    </th>
                                    <th style="width:28%">
                                        Local Address
                                        <small class="text-muted font-weight-normal">(gateway router)</small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $packageId => $row)
                                    <tr>
                                        <td class="font-weight-bold align-middle">{{ $row['package_name'] }}</td>
                                        <td>
                                            <input type="text"
                                                   class="form-control form-control-sm"
                                                   wire:model.defer="rows.{{ $packageId }}.ros_profile"
                                                   placeholder="e.g. PLAN_10M" />
                                        </td>
                                        <td>
                                            <select class="form-control form-control-sm"
                                                    wire:model.defer="rows.{{ $packageId }}.address_pool_id">
                                                <option value="">— Ikuti PPPoE Server router —</option>
                                                @foreach($availablePools as $pool)
                                                    <option value="{{ $pool['id'] }}">{{ $pool['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="form-control form-control-sm"
                                                   wire:model.defer="rows.{{ $packageId }}.local_address"
                                                   placeholder="e.g. 10.10.10.1" />
                                            <small class="text-muted">Kosongkan jika ikuti pool gateway</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if(!empty($pushResults))
                <div class="card-body border-top pt-3 pb-2">
                    <p class="text-muted mb-2" style="font-size:.8rem;font-weight:600">
                        <i class="fas fa-history mr-1"></i> Hasil Push Terakhir
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($pushResults as $r)
                            <span class="badge badge-{{ $r['status'] === 'ok' ? 'success' : 'danger' }}" style="font-size:.75rem">
                                <i class="fas fa-{{ $r['status'] === 'ok' ? 'check' : 'times' }} mr-1"></i>
                                {{ $r['profile'] }}
                                @if($r['status'] === 'error')
                                    — {{ $r['message'] }}
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div>
                        <button wire:click="pushToRouter" wire:loading.attr="disabled"
                                wire:confirm="Push semua profile ke MikroTik sekarang? Nilai yang sudah ada akan di-overwrite."
                                class="btn btn-warning">
                            <span wire:loading.remove wire:target="pushToRouter">
                                <i class="fas fa-upload mr-1"></i> Push ke Router
                            </span>
                            <span wire:loading wire:target="pushToRouter">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Pushing…
                            </span>
                        </button>
                        <small class="text-muted ml-2">Update profile di MikroTik sekarang (forceOverwrite)</small>
                    </div>

                    <button wire:click="save" wire:loading.attr="disabled" class="btn btn-primary">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-save mr-1"></i> Simpan Mapping
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan…
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
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
            title: event.detail.message,
        });
    });
</script>
@endpush

@section('css')
<style>
    .table th { background-color: #f8f9fa; }
    .card-title { font-weight: 600; }
    .align-middle td { vertical-align: middle !important; }
</style>
@stop
