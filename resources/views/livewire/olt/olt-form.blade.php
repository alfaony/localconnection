<div class="pt-4">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">{{ $oltId ? 'Edit' : 'Tambah' }} OLT</h3>
        </div>

        <form wire:submit.prevent="save">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Nama OLT <span class="text-danger">*</span></label>
                        <input wire:model.defer="name" class="form-control @error('name') is-invalid @enderror"
                               placeholder="Contoh: OLT HiOSO POP Utama">
                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label>POP <span class="text-danger">*</span></label>
                        <select wire:model.defer="popId" class="form-control @error('popId') is-invalid @enderror">
                            <option value="">Pilih POP</option>
                            @foreach($pops as $pop)
                                <option value="{{ $pop->id }}">{{ $pop->name }}</option>
                            @endforeach
                        </select>
                        @error('popId')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Vendor</label>
                        <select wire:model="vendor" class="form-control">
                            <option value="hioso">HiOSO</option>
                            <option value="generic">Generic SNMP</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Model</label>
                        <input wire:model.defer="model" class="form-control" placeholder="Contoh: HA7304">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Host/IP <span class="text-danger">*</span></label>
                        <input wire:model.defer="host" class="form-control @error('host') is-invalid @enderror"
                               placeholder="192.168.10.2">
                        @error('host')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-1">
                        <label>Port</label>
                        <input wire:model.defer="port" type="number" class="form-control @error('port') is-invalid @enderror">
                        @error('port')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="card card-secondary">
                    <div class="card-header"><h3 class="card-title">Akses SNMP Read-only</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>Versi SNMP</label>
                                <select wire:model="snmpVersion" class="form-control">
                                    <option value="2c">SNMP v2c</option>
                                    <option value="3">SNMP v3</option>
                                    <option value="1">SNMP v1</option>
                                </select>
                            </div>

                            @if($snmpVersion !== '3')
                                <div class="form-group col-md-9">
                                    <label>Community {{ $oltId ? '(kosongkan jika tidak diubah)' : '' }}</label>
                                    <input wire:model.defer="community" type="password"
                                           class="form-control @error('community') is-invalid @enderror"
                                           autocomplete="new-password" placeholder="SNMP community read-only">
                                    @error('community')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            @else
                                <div class="form-group col-md-4">
                                    <label>Username</label>
                                    <input wire:model.defer="snmpUsername"
                                           class="form-control @error('snmpUsername') is-invalid @enderror">
                                    @error('snmpUsername')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-5">
                                    <label>Security Level</label>
                                    <select wire:model="securityLevel" class="form-control">
                                        <option value="authPriv">authPriv</option>
                                        <option value="authNoPriv">authNoPriv</option>
                                        <option value="noAuthNoPriv">noAuthNoPriv</option>
                                    </select>
                                </div>
                            @endif
                        </div>

                        @if($snmpVersion === '3' && $securityLevel !== 'noAuthNoPriv')
                            <div class="row">
                                <div class="form-group col-md-2">
                                    <label>Auth Protocol</label>
                                    <select wire:model.defer="authProtocol" class="form-control">
                                        <option value="SHA">SHA</option>
                                        <option value="MD5">MD5</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Auth Password {{ $oltId ? '(opsional)' : '' }}</label>
                                    <input wire:model.defer="authPassword" type="password" autocomplete="new-password"
                                           class="form-control @error('authPassword') is-invalid @enderror">
                                    @error('authPassword')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>

                                @if($securityLevel === 'authPriv')
                                    <div class="form-group col-md-2">
                                        <label>Privacy Protocol</label>
                                        <select wire:model.defer="privProtocol" class="form-control">
                                            <option value="AES">AES</option>
                                            <option value="DES">DES</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Privacy Password {{ $oltId ? '(opsional)' : '' }}</label>
                                        <input wire:model.defer="privPassword" type="password" autocomplete="new-password"
                                               class="form-control @error('privPassword') is-invalid @enderror">
                                        @error('privPassword')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                    </div>
                                @endif
                            </div>
                        @endif

                        <button type="button" wire:click="testConnection" wire:loading.attr="disabled"
                                wire:target="testConnection" class="btn btn-info">
                            <span wire:loading.remove wire:target="testConnection">
                                <i class="fas fa-plug mr-1"></i> Tes Koneksi
                            </span>
                            <span wire:loading wire:target="testConnection">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Menghubungkan...
                            </span>
                        </button>

                        @if($connectionResult)
                            <div class="alert alert-{{ $connectionResult['success'] ? 'success' : 'danger' }} mt-3 mb-0">
                                <strong>{{ $connectionResult['message'] }}</strong>
                                @if(!empty($connectionResult['description']))
                                    <div class="small mt-1">{{ $connectionResult['description'] }}</div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">Mapping OID ONU (opsional)</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Status dasar OLT dapat dipantau tanpa bagian ini. Isi mapping setelah MIB atau hasil
                            <code>snmpwalk</code> untuk model HiOSO tersedia. Jangan menebak OID.
                        </p>
                        <textarea wire:model.defer="oidMapJson" rows="10"
                                  class="form-control text-monospace @error('oidMapJson') is-invalid @enderror"></textarea>
                        @error('oidMapJson')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        <details class="mt-2">
                            <summary>Format mapping yang didukung</summary>
<pre class="bg-light border rounded p-2 mt-2 mb-0">{
  "onu": {
    "pon_port": "OID_TABLE_PON",
    "onu_index": "OID_TABLE_INDEX",
    "serial_number": "OID_TABLE_SERIAL",
    "status": "OID_TABLE_STATUS",
    "rx_power": "OID_TABLE_RX",
    "tx_power": "OID_TABLE_TX",
    "distance_m": "OID_TABLE_DISTANCE"
  },
  "status_values": {
    "NILAI_VENDOR_ONLINE": "ONLINE",
    "NILAI_VENDOR_LOS": "LOS"
  },
  "scales": {
    "rx_power": 0.01,
    "tx_power": 0.01
  }
}</pre>
                        </details>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-3">
                        <label>Interval polling (menit)</label>
                        <input wire:model.defer="pollingInterval" type="number" min="1" max="1440"
                               class="form-control @error('pollingInterval') is-invalid @enderror">
                        @error('pollingInterval')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-9 d-flex align-items-center pt-3">
                        <div class="custom-control custom-switch">
                            <input wire:model.defer="pollingEnabled" type="checkbox"
                                   class="custom-control-input" id="pollingEnabled">
                            <label class="custom-control-label" for="pollingEnabled">Aktifkan polling otomatis</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
                <a href="{{ route('olt.index') }}" class="btn btn-default">Batal</a>
            </div>
        </form>
    </div>
</div>
