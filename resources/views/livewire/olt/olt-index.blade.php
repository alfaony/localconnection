<div>
    @include('components.alert')

    <div class="row mt-4">
        <div class="col-lg col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $stats['total'] }}</h3><p>Total OLT</p></div>
                <div class="icon"><i class="fas fa-server"></i></div>
            </div>
        </div>
        <div class="col-lg col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $stats['up'] }}</h3><p>OLT Online</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg col-6">
            <div class="small-box bg-danger">
                <div class="inner"><h3>{{ $stats['problem'] }}</h3><p>OLT Bermasalah</p></div>
                <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
            </div>
        </div>
        <div class="col-lg col-6">
            <div class="small-box bg-primary">
                <div class="inner"><h3>{{ $stats['online_onus'] }}</h3><p>ONU Online</p></div>
                <div class="icon"><i class="fas fa-network-wired"></i></div>
            </div>
        </div>
        <div class="col-lg col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $stats['los_onus'] }}</h3><p>ONU LOS</p></div>
                <div class="icon"><i class="fas fa-unlink"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Monitoring OLT</h3>
            <div class="card-tools">
                @canAccess('create', 'olts')
                <a href="{{ route('olt.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Tambah OLT
                </a>
                @endcanAccess
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <input wire:model.debounce.400ms="search" type="search" class="form-control"
                           placeholder="Cari nama, host, atau model OLT">
                </div>
                <div class="col-md-4">
                    <select wire:model="status" class="form-control">
                        <option value="">Semua status</option>
                        <option value="UP">UP</option>
                        <option value="DOWN">DOWN</option>
                        <option value="ERROR">ERROR</option>
                        <option value="UNKNOWN">UNKNOWN</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>OLT</th>
                        <th>POP</th>
                        <th>Host</th>
                        <th>Status</th>
                        <th class="text-center">ONU</th>
                        <th>Polling Terakhir</th>
                        <th style="width: 155px">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($olts as $olt)
                        <tr>
                            <td>
                                <strong>{{ $olt->name }}</strong><br>
                                <small class="text-muted">{{ strtoupper($olt->vendor) }} {{ $olt->model ?: '' }}</small>
                            </td>
                            <td>{{ $olt->pop?->name ?: '-' }}</td>
                            <td><code>{{ $olt->host }}:{{ $olt->port }}</code></td>
                            <td>
                                @php
                                    $statusClass = ['UP' => 'success', 'DOWN' => 'danger', 'ERROR' => 'danger', 'UNKNOWN' => 'secondary'][$olt->status] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $statusClass }}">{{ $olt->status }}</span>
                                @if($olt->last_error)
                                    <i class="fas fa-info-circle text-danger ml-1"
                                       title="{{ $olt->last_error }}" data-toggle="tooltip"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success">{{ $olt->online_onus_count }} online</span>
                                <span class="badge badge-warning">{{ $olt->los_onus_count }} LOS</span>
                                <div><small class="text-muted">{{ $olt->onus_count }} total</small></div>
                            </td>
                            <td>
                                {{ $olt->last_polled_at?->diffForHumans() ?: 'Belum pernah' }}
                                @if(!$olt->polling_enabled)
                                    <br><span class="badge badge-secondary">Polling nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <button wire:click="refreshStatus({{ $olt->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="refreshStatus({{ $olt->id }})"
                                        class="btn btn-info btn-sm" title="Sinkronkan sekarang">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                @canAccess('show', 'olts')
                                <a href="{{ route('olt.show', $olt->id) }}" class="btn btn-primary btn-sm" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcanAccess
                                @canAccess('edit', 'olts')
                                <a href="{{ route('olt.edit', $olt->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcanAccess
                                @canAccess('destroy', 'olts')
                                <button wire:click="delete({{ $olt->id }})"
                                        onclick="return confirm('Hapus OLT {{ addslashes($olt->name) }}?')"
                                        class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcanAccess
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada OLT.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{ $olts->links() }}
        </div>
    </div>
</div>

@push('js')
<script>
    window.addEventListener('olt-toast', event => {
        if (window.toastr) {
            toastr[event.detail.type || 'info'](event.detail.message);
        }
    });

    document.addEventListener('livewire:load', () => $('[data-toggle="tooltip"]').tooltip());
    document.addEventListener('livewire:update', () => $('[data-toggle="tooltip"]').tooltip());
</script>
@endpush
