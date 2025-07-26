<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline mt-5">
            <div class="card-header">
                <h3 class="card-title">Buat Data Center</h3>
            </div>
            <form wire:submit.prevent="save">
                <div class="card-body">
                    <div class="form-group">
                        <label>Data Center *</label>
                        <input type="text" class="form-control" wire:model="name" placeholder="Nama Data Center" required>
                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
    
                    <div class="row">
                        {{-- 
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Kapasitas (MB)*</label>
                                <input type="number" class="form-control" wire:model="capacity_mb" placeholder="Kapasitas (MB)" required>
                                @error('capacity_mb') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price" class="form-label">Biaya Per Bulan <span class="text-danger">*</span></label>
                                <input type="hidden" wire:model="cost_per_month" id="price_hidden">
                                <input type="text" class="form-control" id="internet_cost_input" wire:ignore placeholder="Harga normal">
                                @error('price') <span class="text-danger small">{{ $message }}</span> @enderror
                                @error('cost_per_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Tagihan*</label>
                                <input type="date" class="form-control" wire:model="tanggal_tagihan" placeholder="Tanggal Tagihan" required>
                                @error('tanggal_tagihan') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Jalur Masuk</h3>
                            <button type="button" class="btn btn-sm btn-primary float-right" wire:click="addEntry" {{ count($this->entries) >= 5 ? 'disabled' : '' }} >
                                <i class="fas fa-plus"></i> Add Entry
                            </button>
                        </div>
                        <div class="card-body">
                            @foreach($entries as $index => $entry)
                            <div class="row mb-2">
                                <div class="col-md-5">
                                    <input type="text" class="form-control" placeholder="Nama Entry" 
                                            wire:model="entries.{{ $index }}.name">
                                    @error('entries.'.$index.'.name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-5">
                                    <input type="number" class="form-control" placeholder="Kapasitas (MB)" 
                                            wire:model="entries.{{ $index }}.capacity_mb">
                                    @error('entries.'.$index.'.capacity_mb') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger" 
                                            wire:click="removeEntry({{ $index }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
    
                    <div class="form-group">
                        <label>Hak Akses</label>
                        <select class="form-control select2" multiple wire:model="selectedUsers">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <a href="{{ route('data-center.index') }}" class="btn btn-default">Batalkan</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/imask"></script>
<script>
    function initSelect2() {
        $('.select2').select2();
        $('.select2').on('change', function (e) {
            let data = $(this).val();
            @this.set('selectedUsers', data);
        });
    }

    document.addEventListener("livewire:load", function () {
        initSelect2();

        const priceInput = document.getElementById('internet_cost_input');
        const priceHidden = document.getElementById('price_hidden');
        let priceMask = null;

        if (priceInput && priceHidden) {
            priceMask = IMask(priceInput, {
                mask: Number,
                scale: 0,
                thousandsSeparator: '.',
                padFractionalZeros: false,
                normalizeZeros: true,
                radix: ',',
                mapToRadix: ['.']
            });

            // Set nilai awal dari hidden input ke field yang diformat
            if (priceHidden.value) {
                priceMask.value = priceHidden.value;
            }

            // Sync ke Livewire saat input berubah
            priceMask.on('accept', () => {
                priceHidden.value = priceMask.unmaskedValue;
                priceHidden.dispatchEvent(new Event('input'));
            });
        }

        Livewire.hook('message.processed', function () {
            initSelect2();
        });
    });
</script>
@endpush

@push('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single 
    {
        height: 38px !important;
        padding: 5px 10px !important;
    }
    .select2-selection__choice
    {
        background-color: #007bff !important;
        border: 1px solid #007bff !important;
    }

    .select2-selection__choice__remove
    {
        color: #fe0700 !important;
        border: 1px solid #007bff !important;
    }
</style>
@endpush