<div class="pt-4">
    @include('components.alert')

    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                {{ $olt->name }}
                <small class="text-muted ml-2">{{ strtoupper($olt->vendor) }} {{ $olt->model }}</small>
            </h3>
            <div class="card-tools">
                <button wire:click="synchronize" wire:loading.attr="disabled" wire:target="synchronize"
                        class="btn btn-info btn-sm">
                    <i class="fas fa-sync-alt mr-1"></i> Sinkronkan
                </button>
                @canAccess('edit', 'olts')
                <a href="{{ route('olt.edit', $olt->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit mr-1"></i> Konfigurasi
                </a>
                @endcanAccess
                <a href="{{ route('olt.index') }}" class="btn btn-default btn-sm">Kembali</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Status</strong><br>
                    <span class="badge badge-{{ $olt->status === 'UP' ? 'success' : ($olt->status === 'UNKNOWN' ? 'secondary' : 'danger') }}">
                        {{ $olt->status }}
                    </span>
                </div>
                <div class="col-md-3"><strong>Host</strong><br><code>{{ $olt->host }}:{{ $olt->port }}</code></div>
                <div class="col-md-3"><strong>POP</strong><br>{{ $olt->pop?->name ?: '-' }}</div>
                <div class="col-md-3"><strong>Polling terakhir</strong><br>{{ $olt->last_polled_at?->diffForHumans() ?: 'Belum pernah' }}</div>
            </div>
            @if($olt->system_description)
                <hr><small class="text-muted">{{ $olt->system_description }}</small>
            @endif
            @if($olt->last_error)
                <div class="alert alert-danger mt-3 mb-0">{{ $olt->last_error }}</div>
            @endif
            @if(!$olt->oid_map || !data_get($olt->oid_map, 'onu.status'))
                <div class="alert alert-warning mt-3 mb-0">
                    Health-check OLT sudah siap. Sinkronisasi ONU menunggu mapping OID/MIB untuk model HiOSO ini.
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        @foreach([
            ['Total ONU', $stats['total'], 'info', 'network-wired'],
            ['Online', $stats['online'], 'success', 'check-circle'],
            ['Offline', $stats['offline'], 'secondary', 'power-off'],
            ['LOS', $stats['los'], 'warning', 'unlink'],
        ] as [$label, $value, $color, $icon])
            <div class="col-md-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-{{ $color }}"><i class="fas fa-{{ $icon }}"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ $label }}</span>
                        <span class="info-box-number">{{ $value }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Daftar ONU/ONT</h3></div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <input wire:model.debounce.400ms="search" class="form-control"
                           placeholder="Cari serial, nama, PON, atau pelanggan">
                </div>
                <div class="col-md-4">
                    <select wire:model="status" class="form-control">
                        <option value="">Semua status</option>
                        <option value="ONLINE">ONLINE</option>
                        <option value="OFFLINE">OFFLINE</option>
                        <option value="LOS">LOS</option>
                        <option value="UNKNOWN">UNKNOWN</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover">
                    <thead>
                    <tr>
                        <th>PON/ONU</th>
                        <th>Serial Number</th>
                        <th>Nama/Pelanggan</th>
                        <th>Status</th>
                        <th>RX</th>
                        <th>TX</th>
                        <th>Jarak</th>
                        <th>Terakhir terlihat</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($onus as $onu)
                        <tr>
                            <td><code>{{ $onu->pon_port }}/{{ $onu->onu_index }}</code></td>
                            <td>{{ $onu->serial_number ?: '-' }}</td>
                            <td>
                                {{ $onu->name ?: '-' }}
                                @if($onu->customer)
                                    <br><small class="text-muted">{{ $onu->customer->code }} — {{ $onu->customer->name }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $onuClass = ['ONLINE' => 'success', 'OFFLINE' => 'secondary', 'LOS' => 'warning'][$onu->status] ?? 'light';
                                @endphp
                                <span class="badge badge-{{ $onuClass }}">{{ $onu->status }}</span>
                            </td>
                            <td>{{ $onu->rx_power !== null ? number_format($onu->rx_power, 2).' dBm' : '-' }}</td>
                            <td>{{ $onu->tx_power !== null ? number_format($onu->tx_power, 2).' dBm' : '-' }}</td>
                            <td>{{ $onu->distance_m !== null ? number_format($onu->distance_m).' m' : '-' }}</td>
                            <td>{{ $onu->last_seen_at?->diffForHumans() ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Data ONU belum tersedia.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $onus->links() }}
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
</script>
@endpush
