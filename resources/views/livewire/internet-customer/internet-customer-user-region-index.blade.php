@section('title', 'Filter Region Pelanggan Internet')

@section('content_header')
    <div class="mb-0">
        <h5 class="mb-0">Filter Region — Pelanggan Internet</h5>
        <small class="text-muted">Atur region yang boleh dilihat oleh tiap user (Finance / Marketing / Teknisi)</small>
    </div>
@stop

<div>
    @include('components.alert')

    {{-- ── Form Tambah Region ─────────────────────────────────────────────── --}}
    @if($showForm)
    @canAccess('store','internet_customer_user_regions')
    <div class="card shadow-sm border-left-primary mb-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0"><i class="fas fa-plus mr-1"></i> Tambah Filter Region</h6>
            <button type="button" class="close text-white" wire:click="cancel"><span>&times;</span></button>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- User --}}
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="font-weight-semibold">User <span class="text-danger">*</span></label>
                        <div wire:ignore>
                            <select id="sel2-user"
                                    class="form-control form-control-sm @error('user_id') is-invalid @enderror"
                                    style="width:100%">
                                <option value="">-- Pilih User --</option>
                                @foreach($selectableUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role->name ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                        @error('user_id')<span class="text-danger small">{{ $message }}</span>@enderror
                        <small class="text-muted">Hanya user Finance / Marketing / Teknisi</small>
                    </div>
                </div>

                {{-- Provinsi --}}
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="font-weight-semibold">Provinsi</label>
                        <div wire:ignore>
                            <select id="sel2-province"
                                    class="form-control form-control-sm @error('province_id') is-invalid @enderror"
                                    style="width:100%">
                                <option value="">-- Semua Provinsi --</option>
                                @foreach($provinces as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('province_id')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Kota --}}
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="font-weight-semibold">Kota / Kabupaten</label>
                        <div wire:ignore>
                            <select id="sel2-city"
                                    class="form-control form-control-sm @error('city_id') is-invalid @enderror"
                                    style="width:100%">
                                <option value="">-- Semua Kota --</option>
                            </select>
                        </div>
                        @error('city_id')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Kecamatan --}}
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="font-weight-semibold">Kecamatan</label>
                        <div wire:ignore>
                            <select id="sel2-district"
                                    class="form-control form-control-sm @error('district_id') is-invalid @enderror"
                                    style="width:100%">
                                <option value="">-- Semua Kecamatan --</option>
                            </select>
                        </div>
                        @error('district_id')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Kelurahan --}}
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="font-weight-semibold">Kelurahan</label>
                        <div wire:ignore>
                            <select id="sel2-subdistrict"
                                    class="form-control form-control-sm @error('subdistrict_id') is-invalid @enderror"
                                    style="width:100%">
                                <option value="">-- Semua Kelurahan --</option>
                            </select>
                        </div>
                        @error('subdistrict_id')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="col-md-1 d-flex align-items-end">
                    <div class="form-group mb-2 w-100">
                        <button type="button"
                                class="btn btn-primary btn-sm btn-block"
                                wire:click="store"
                                wire:loading.attr="disabled"
                                wire:target="store">
                            <span wire:loading wire:target="store">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                            </span>
                            Simpan
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-1">
                <small class="text-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    Kosongkan level di bawah provinsi untuk filter seluruh provinsi. Semakin detail diisi, semakin sempit filternya.
                </small>
            </div>
        </div>
    </div>
    @endcanAccess
    @endif

    {{-- ── Card Tabel ──────────────────────────────────────────────────────── --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 font-weight-bold text-primary">
                <i class="fas fa-map-marked-alt mr-1"></i> Daftar Filter Region User
            </h6>
            @if(!$showForm)
            @canAccess('store','internet_customer_user_regions')
            <button type="button" class="btn btn-primary btn-sm ml-auto" wire:click="create">
                <i class="fas fa-plus mr-1"></i> Tambah
            </button>
            @endcanAccess
            @endif
        </div>

        <div class="card-body p-2">
            {{-- Search --}}
            <div class="row mb-2">
                <div class="col-md-4">
                    <input type="text"
                           wire:model.debounce.400ms="search"
                           class="form-control form-control-sm"
                           placeholder="Cari nama user...">
                </div>
                <div class="col-md-2 ml-auto">
                    <select wire:model="perPage" class="form-control form-control-sm">
                        <option value="10">10 / hal</option>
                        <option value="25">25 / hal</option>
                        <option value="50">50 / hal</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:5%">#</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Region</th>
                            <th style="width:10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($regions as $region)
                        <tr>
                            <td>{{ $regions->firstItem() + $loop->index }}</td>
                            <td>{{ $region->user->name ?? '-' }}</td>
                            <td>
                                <span class="badge badge-secondary">
                                    {{ $region->user->role->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @if($region->subdistrict_id)
                                    <span class="badge badge-info mr-1">Kelurahan</span>
                                    {{ $region->subdistrict->name ?? '-' }},
                                    {{ $region->district->name ?? '-' }},
                                    {{ $region->city->name ?? '-' }},
                                    {{ $region->province->name ?? '-' }}
                                @elseif($region->district_id)
                                    <span class="badge badge-primary mr-1">Kecamatan</span>
                                    {{ $region->district->name ?? '-' }},
                                    {{ $region->city->name ?? '-' }},
                                    {{ $region->province->name ?? '-' }}
                                @elseif($region->city_id)
                                    <span class="badge badge-success mr-1">Kota</span>
                                    {{ $region->city->name ?? '-' }},
                                    {{ $region->province->name ?? '-' }}
                                @elseif($region->province_id)
                                    <span class="badge badge-warning mr-1">Provinsi</span>
                                    {{ $region->province->name ?? '-' }}
                                @else
                                    <span class="badge badge-light text-dark">All Region</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-danger btn-xs"
                                        wire:click="confirmDelete('{{ $region->id }}')"
                                        title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Belum ada filter region yang dikonfigurasi.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-2">
                {{ $regions->links() }}
            </div>
        </div>
    </div>

    {{-- ── Modal Konfirmasi Hapus ───────────────────────────────────────────── --}}
    @if($showDeleteModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.4)">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">Konfirmasi Hapus</h6>
                    <button type="button" class="close" wire:click="cancelDelete"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Yakin ingin menghapus filter region ini?</p>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="cancelDelete">Batal</button>
                    <button type="button"
                            class="btn btn-danger btn-sm"
                            wire:click="delete"
                            wire:loading.attr="disabled">
                        <span wire:loading wire:target="delete"><i class="fas fa-spinner fa-spin mr-1"></i></span>
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
    .table th, .table td { vertical-align: middle; }
    .border-left-primary { border-left: 4px solid #007bff !important; }
    .select2-container { width: 100% !important; }
    .select2-container--default .select2-selection--single {
        height: 31px;
        border-color: #ced4da;
        border-radius: 4px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 29px;
        font-size: 0.875rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 29px;
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    function initSelect2(id, placeholder) {
        var $el = $(id);
        if (!$el.length) return;
        if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
        $el.select2({
            placeholder: placeholder,
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() { return 'Tidak ditemukan'; },
                searching: function() { return 'Mencari...'; },
            },
        });
    }

    function initAllSelects() {
        initSelect2('#sel2-user',        '-- Pilih User --');
        initSelect2('#sel2-province',    '-- Semua Provinsi --');
        initSelect2('#sel2-city',        '-- Semua Kota --');
        initSelect2('#sel2-district',    '-- Semua Kecamatan --');
        initSelect2('#sel2-subdistrict', '-- Semua Kelurahan --');

        $('#sel2-user').off('change.lw').on('change.lw', function() {
            @this.set('user_id', $(this).val() || null);
        });
        $('#sel2-province').off('change.lw').on('change.lw', function() {
            @this.set('province_id', $(this).val() ? parseInt($(this).val()) : null);
        });
        $('#sel2-city').off('change.lw').on('change.lw', function() {
            @this.set('city_id', $(this).val() ? parseInt($(this).val()) : null);
        });
        $('#sel2-district').off('change.lw').on('change.lw', function() {
            @this.set('district_id', $(this).val() ? parseInt($(this).val()) : null);
        });
        $('#sel2-subdistrict').off('change.lw').on('change.lw', function() {
            @this.set('subdistrict_id', $(this).val() ? parseInt($(this).val()) : null);
        });
    }

    function rebuildOptions(id, items, placeholder) {
        var $el = $(id);
        if (!$el.length) return;
        if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');

        $el.empty().append('<option value="">' + placeholder + '</option>');
        $.each(items, function(i, item) {
            $el.append('<option value="' + item.id + '">' + item.name + '</option>');
        });

        initSelect2(id, placeholder);

        // Re-attach the correct Livewire field based on the element ID
        var idToField = {
            '#sel2-city': 'city_id',
            '#sel2-district': 'district_id',
            '#sel2-subdistrict': 'subdistrict_id',
        };
        var field = idToField[id];
        if (field) {
            $el.off('change.lw').on('change.lw', function() {
                @this.set(field, $(this).val() ? parseInt($(this).val()) : null);
            });
        }
    }

    document.addEventListener('livewire:load', function () {
        // Init Select2 saat form pertama dibuka
        window.addEventListener('region-form-opened', function () {
            setTimeout(initAllSelects, 100);
        });

        // Update opsi kota ketika provinsi berubah
        window.addEventListener('region-cities-updated', function (e) {
            rebuildOptions('#sel2-city', e.detail.cities, '-- Semua Kota --');
            $('#sel2-city').val('').trigger('change.select2');
            rebuildOptions('#sel2-district', [], '-- Semua Kecamatan --');
            $('#sel2-district').val('').trigger('change.select2');
            rebuildOptions('#sel2-subdistrict', [], '-- Semua Kelurahan --');
            $('#sel2-subdistrict').val('').trigger('change.select2');
        });

        // Update opsi kecamatan ketika kota berubah
        window.addEventListener('region-districts-updated', function (e) {
            rebuildOptions('#sel2-district', e.detail.districts, '-- Semua Kecamatan --');
            $('#sel2-district').val('').trigger('change.select2');
            rebuildOptions('#sel2-subdistrict', [], '-- Semua Kelurahan --');
            $('#sel2-subdistrict').val('').trigger('change.select2');
        });

        // Update opsi kelurahan ketika kecamatan berubah
        window.addEventListener('region-subdistricts-updated', function (e) {
            rebuildOptions('#sel2-subdistrict', e.detail.subdistricts, '-- Semua Kelurahan --');
            $('#sel2-subdistrict').val('').trigger('change.select2');
        });
    });
</script>
@stop
