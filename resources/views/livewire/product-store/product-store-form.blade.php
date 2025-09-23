<div class="row">
    <div class="col-md-12 mt-3">
        @include('components.alert')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Produk Toko</h3>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="barcode" class="font-weight-bold">Kode Barang</label>
                                    <div class="input-group">
                                        <input type="text" wire:model="barcode" id="barcode" class="form-control" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="fas fa-barcode"></i>
                                            </span>
                                        </div>
                                    </div>
                                    @error('barcode') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
    
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="categoryProductStore" class="font-weight-bold">Kategori</label>
                                    <select wire:model="category_product_store_id" id="categoryProductStore" class="form-control select2" data-placeholder="Pilih Kategori">
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_product_store_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="brandProductStore" class="font-weight-bold">Merk</label>
                                    <select wire:model="brand_product_store_id" id="brandProductStore" class="form-control select2" data-placeholder="Pilih Merk">
                                        <option value="">Pilih Merk</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('brand_product_store_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
    
                        <div class="form-group">
                            <label for="name" class="font-weight-bold">Nama Produk</label>
                            <div class="input-group">
                                <input type="text" wire:model="name" id="name" class="form-control" placeholder="Masukan nama produk">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-tag"></i>
                                    </span>
                                </div>
                            </div>
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
    
                        <div class="form-group">
                            <label for="variant" class="font-weight-bold">Varian</label>
                            <input type="text" wire:model="variant" id="variant" class="form-control" placeholder="Masukan varian produk">
                            @error('variant') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
    
                        <div class="form-group">
                            <label for="specification" class="font-weight-bold">Spesifikasi</label>
                            <textarea wire:model="specification" id="specification" rows="3" class="form-control" placeholder="Masukan spesifikasi produk"></textarea>
                            @error('specification') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
    
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="length" class="font-weight-bold">Panjang (cm)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" wire:model="length" id="length" class="form-control" placeholder="0.00">
                                        <div class="input-group-append">
                                            <span class="input-group-text">cm</span>
                                        </div>
                                    </div>
                                    @error('length') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="width" class="font-weight-bold">Lebar (cm)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" wire:model="width" id="width" class="form-control" placeholder="0.00">
                                        <div class="input-group-append">
                                            <span class="input-group-text">cm</span>
                                        </div>
                                    </div>
                                    @error('width') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="height" class="font-weight-bold">Tinggi (cm)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" wire:model="height" id="height" class="form-control" placeholder="0.00">
                                        <div class="input-group-append">
                                            <span class="input-group-text">cm</span>
                                        </div>
                                    </div>
                                    @error('height') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
    
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="weight" class="font-weight-bold">Berat (gram)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" wire:model="weight" id="weight" class="form-control" placeholder="0.00">
                                        <div class="input-group-append">
                                            <span class="input-group-text">g</span>
                                        </div>
                                    </div>
                                    @error('weight') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="selling_price" class="font-weight-bold">Harga Jual</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">Rp</span>
                                        </div>

                                        <input type="hidden" wire:model="selling_price" id="price_hidden">
                                        <input type="text" class="form-control" id="internet_cost_input" wire:ignore placeholder="Harga normal">
                                    </div>
                                    @error('selling_price') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="cancel">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary" wire:click="$set('createAgain', false)">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                        <button type="submit" class="btn btn-success" wire:click="$set('createAgain', true)">
                            <i class="fas fa-plus-circle mr-1"></i> Simpan & Buat Lagi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/imask"></script>
<script>
    function initSelect2Bindings() {
        $('#categoryProductStore').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%'
        }).off('change').on('change', function () {
            @this.set('category_product_store_id', $(this).val());
        });

        $('#brandProductStore').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%'
        }).off('change').on('change', function () {
            @this.set('brand_product_store_id', $(this).val());
        });
    }

    document.addEventListener('livewire:load', function () {
        initSelect2Bindings();

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

        Livewire.hook('message.processed', (message, component) => {
            initSelect2Bindings(); // Re-init setiap Livewire update
        });


        Livewire.on('editProduct', () => {
            setTimeout(() => {
                $('#categoryProductStore').val(@this.get('category_product_store_id')).trigger('change');
                $('#brandProductStore').val(@this.get('brand_product_store_id')).trigger('change');
            }, 100);
        });

        Livewire.on('createProduct', () => {
            setTimeout(() => {
                $('#categoryProductStore').val('').trigger('change');
                $('#brandProductStore').val('').trigger('change');
            }, 100);
        });
    });
</script>
@endsection
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css">
<style>
    .select2-container--default .select2-selection--single {
        border: 1px solid #aaa;
        border-radius: 4px;
        padding: 2px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 10px;
    }
    .select2-container--default .select2-selection--single .select2-selection__choice {
        background-color: #ddd;
        border: none;
        color: inherit;
    }
</style>
@endsection
