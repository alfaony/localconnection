<div>
    <div class="row">
        @include('components.alert')
    </div>

    <div class="card card-primary card-outline mt-5">
        <div class="card-header">
            <h3 class="card-title">Reconcile Customer Static/IPoE</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Scan ARP table router untuk menemukan device yang sedang terkoneksi,
                lalu hubungkan ke customer yang sudah ada atau buat customer baru.
            </p>

            <div class="row mb-3">
                <div class="col-md-5">
                    <select wire:model="routerId" class="form-control">
                        <option value="">- Pilih Router -</option>
                        @foreach ($routers as $router)
                            <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->host }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button wire:click="scan" wire:loading.attr="disabled" class="btn btn-primary">
                        <span wire:loading.remove wire:target="scan"><i class="fas fa-search mr-1"></i> Scan ARP Table</span>
                        <span wire:loading wire:target="scan"><i class="fas fa-spinner fa-spin mr-1"></i> Scanning...</span>
                    </button>
                </div>
            </div>

            @if ($scanError)
                <div class="alert alert-danger">{{ $scanError }}</div>
            @endif

            @if (!empty($entries))
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>IP Address</th>
                            <th>MAC Address</th>
                            <th>Interface</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entries as $entry)
                        <tr>
                            <td>{{ $entry['ip'] }}</td>
                            <td>{{ $entry['mac'] ?? '-' }}</td>
                            <td>{{ $entry['interface'] ?? '-' }}</td>
                            <td>
                                @if ($entry['matched_customer_id'])
                                    <span class="badge bg-success">Terhubung</span>
                                    —
                                    <a href="{{ route('internet-customer.show', $entry['matched_customer_id']) }}">
                                        {{ $entry['matched_customer_name'] }}
                                    </a>
                                    <small class="text-muted">({{ $entry['matched_customer_status'] }})</small>
                                @else
                                    <span class="badge bg-warning">Belum terhubung</span>
                                @endif
                            </td>
                            <td>
                                @if (!$entry['matched_customer_id'])
                                    <button
                                        wire:click="openLinkModal('{{ $entry['ip'] }}', '{{ $entry['mac'] }}')"
                                        class="btn btn-info btn-sm mb-1">
                                        <i class="fas fa-link mr-1"></i> Hubungkan ke Customer
                                    </button>
                                    <button
                                        wire:click="openCreateModal('{{ $entry['ip'] }}', '{{ $entry['mac'] }}')"
                                        class="btn btn-success btn-sm mb-1">
                                        <i class="fas fa-user-plus mr-1"></i> Buat Customer Baru
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @elseif ($routerId && !$scanning)
                <p class="text-muted">Klik "Scan ARP Table" untuk mulai.</p>
            @endif
        </div>
    </div>

    {{-- Modal: Hubungkan ke Customer Existing --}}
    <div class="modal fade" id="linkModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hubungkan ke Customer</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>IP: <strong>{{ $linkingIp }}</strong> — MAC: <strong>{{ $linkingMac ?? '-' }}</strong></p>

                    <div class="form-group">
                        <label>Pilih Customer</label>
                        <select wire:model="selectedCustomerId" class="form-control">
                            <option value="">- Pilih Customer -</option>
                            @foreach ($this->candidateCustomers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                        @error('selectedCustomerId') <span class="text-danger">{{ $message }}</span> @enderror
                        <small class="form-text text-muted">
                            Cuma nampilin customer dengan tipe Static/IPoE yang belum punya IP address.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" wire:click="confirmLink" class="btn btn-primary">Hubungkan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Buat Customer Baru --}}
    <div class="modal fade" id="createModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Customer Baru</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>IP: <strong>{{ $creatingIp }}</strong> — MAC: <strong>{{ $creatingMac ?? '-' }}</strong></p>

                    <div class="form-group">
                        <label>Nama Customer (sementara)</label>
                        <input type="text" wire:model="newCustomerName" class="form-control">
                        @error('newCustomerName') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Paket Internet (Static/IPoE)</label>
                        <select wire:model="newPackageId" class="form-control">
                            <option value="">- Pilih Paket -</option>
                            @foreach ($this->ipoePackages as $pkg)
                                <option value="{{ $pkg->id }}">{{ $pkg->name }} — Rp{{ number_format($pkg->price) }}</option>
                            @endforeach
                        </select>
                        @error('newPackageId') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="alert alert-info">
                        Customer akan dibuat dengan status <strong>PENDING</strong>. Kamu akan diarahkan ke
                        halaman edit untuk lengkapi data (alamat, KTP, dll) sebelum diaktifkan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" wire:click="confirmCreate" class="btn btn-success">Buat Customer</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('livewire:load', function () {
        Livewire.on('open-link-modal', () => $('#linkModal').modal('show'));
        Livewire.on('close-link-modal', () => $('#linkModal').modal('hide'));
        Livewire.on('open-create-modal', () => $('#createModal').modal('show'));
    });
</script>
@endpush
