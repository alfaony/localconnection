@section('title', $coverageServiceId ? 'Ubah Cakupan Layanan' : 'Buat Cakupan Layanan')

@section('content_header')
    <h1>
        <i class="fas fa-network-wired"></i>
        {{ $coverageServiceId ? 'Ubah Cakupan Layanan' : 'Buat Cakupan Layanan' }}
    </h1>
@stop

@canAccess('store', 'coverage_services')
@canAccess('update', 'coverage_services')

<div class="card">
    <div class="card-body">
        @include('components.alert')

        <form wire:submit.prevent="save" class="form-horizontal">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="province_id" class="control-label">Provinsi</label>
                        <select wire:model="province_id" 
                                id="province_id" 
                                class="form-control select2-single @error('province_id') is-invalid @enderror"
                                {{ !$provinces ? 'disabled' : '' }}
                                required>
                            <option value="">Select Province</option>
                            @if($provinces)
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                            @endforeach
                            <option value="other">Lainnya</option>
                            @endif
                        </select>
                        @if($province_id === 'other')
                            <input type="text" wire:model.defer="province_other" class="form-control mt-2" placeholder="Masukkan nama provinsi">
                        @endif

                        @error('province_id') 
                            <span class="invalid-feedback">{{ $message }}</span> 
                        @enderror
                        @error('province_other') 
                            <span class="invalid-feedback">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="city_id" class="control-label">Kota/Kabupaten</label>
                        <select wire:model="city_id" 
                                id="city_id" 
                                class="form-control select2-single @error('city_id') is-invalid @enderror"
                                {{ !$cities ? 'disabled' : '' }}
                                required>
                            <option value="">Select City</option>
                            @if($cities)
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                            @endif
                            <option value="other">Lainnya</option>
                        </select>
                        @if($city_id === 'other')
                        <input type="text" wire:model.defer="city_other" class="form-control mt-2" placeholder="Masukkan nama Kabupaten/Kota">
                        @endif

                        @error('city_id') 
                            <span class="invalid-feedback">{{ $message }}</span> 
                        @enderror

                        @error('city_other') 
                            <span class="invalid-feedback">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="district_id" class="control-label">Kecamatan</label>
                        <select wire:model="district_id" 
                                id="district_id" 
                                class="form-control select2-single @error('district_id') is-invalid @enderror"
                                {{ !$districts ? 'disabled' : '' }}
                                required>
                            <option value="">Select District</option>
                            @if($districts)
                                @foreach($districts as $district)
                                    <option value="{{ $district->id }}">{{ $district->name }}</option>
                                @endforeach
                            @endif
                                <option value="other">Lainnya</option>
                        </select>

                        @if($district_id === 'other')
                        <input type="text" wire:model.defer="district_other" class="form-control mt-2" placeholder="Masukkan nama Kecamatan">
                        @endif

                        @error('district_id') 
                            <span class="invalid-feedback">{{ $message }}</span> 
                        @enderror
                        @error('district_other') 
                            <span class="invalid-feedback">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="subdistrict_id" class="control-label">Kelurahan</label>
                        <select wire:model="subdistrict_id" 
                                id="subdistrict_id" 
                                class="form-control select2-single @error('subdistrict_id') is-invalid @enderror"
                                {{ !$subdistricts ? 'disabled' : '' }}
                                required>
                            <option value="">Select Subdistrict</option>
                            @if($subdistricts)
                                @foreach($subdistricts as $subdistrict)
                                    <option value="{{ $subdistrict->id }}">{{ $subdistrict->name }}</option>
                                @endforeach
                            @endif
                                <option value="other">Lainnya</option>
                        </select>
                        @if($subdistrict_id === 'other')
                        <input type="text" wire:model.defer="subdistrict_other" class="form-control mt-2" placeholder="Masukkan nama Kelurahan">
                        @endif

                        @error('subdistrict_id') 
                            <span class="invalid-feedback">{{ $message }}</span> 
                        @enderror

                        @error('subdistrict_other') 
                            <span class="invalid-feedback">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="ods_select" class="control-label">Optical Distribution Points (ODP)</label>
                @error('ods') 
                    <div class="alert alert-danger">{{ $message }}</div> 
                @enderror
                
                <select wire:model="ods" 
                        id="ods_select" 
                        class="form-control select2-multiple" 
                        multiple="multiple">
                    @foreach($allOds as $ods)
                        <option value="{{ $ods->id }}">{{ $ods->name }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Pilih ODP yang tersedia di area ini</small>
            </div>

            <div class="form-group border-top pt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    {{ $coverageServiceId ? 'Update' : 'Create' }}
                </button>
                <a href="{{ route('coverage-service.index') }}" class="btn btn-default">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@section('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    document.addEventListener('livewire:load', function () {
        function initSelect2() {
            // SINGLE SELECT
            $('.select2-single').each(function () {
                const select = $(this);
                const prop = select.attr('id');

                select.select2({
                    placeholder: "-- Pilih --",
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('.card-body')
                });

                select.off('change').on('change', function (e) {
                    const value = $(this).val();
                    if (@this[prop] != value) {
                        console.log(`Update Livewire ${prop} to:`, value);
                        @this.set(prop, value);
                    }
                });
            });

            // MULTIPLE SELECT
            $('#ods_select').select2({
                placeholder: "Pilih ODP",
                allowClear: true,
                width: '100%',
                closeOnSelect: false,
                dropdownParent: $('.card-body')
            }).off('change').on('change', function () {
                const value = $(this).val();
                if (JSON.stringify(@this.ods) !== JSON.stringify(value)) {
                    console.log('Update Livewire ods to:', value);
                    @this.set('ods', value);
                }
            });
        }

        initSelect2();

        Livewire.hook('message.processed', (message, component) => {
            console.log("Livewire processed:", message);
            initSelect2();

            // SET VALUE ULANG SESUAI STATE LIVEWIRE
            $('#ods_select').val(@this.ods).trigger('change');

            $('.select2-single').each(function () {
                const id = $(this).attr('id');
                if (@this[id] !== undefined) {
                    $(this).val(@this[id]).trigger('change');
                }
            });
        });
    });
    </script>
@stop
@push('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
         .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            border-radius: 4px;
            min-height: 38px;
            padding: 6px 12px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            border-color: #006fe6;
            color: white;
            padding: 0 8px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255,255,255,0.7);
            margin-right: 5px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: white;
        }
        
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
    </style>
@endpush

@endcanAccess
@endcanAccess