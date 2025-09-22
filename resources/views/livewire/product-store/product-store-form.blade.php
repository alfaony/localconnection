<div>
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
                        <select wire:model="category_product_store_id" id="categoryProductStore" class="form-control select2">
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
                        <select wire:model="brand_product_store_id" id="brandProductStore" class="form-control select2">
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
                    <input type="text" wire:model="name" id="name" class="form-control" placeholder="Masukkan nama produk">
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
                            <input type="text" wire:model="selling_price" id="selling_price" class="form-control" placeholder="0">
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

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js"></script>
    <script>
        document.addEventListener('livewire:load', function () {
            // Inisialisasi Select2
            $('.select2').select2({
                placeholder: 'Select an option',
                allowClear: true,
                width: '100%'
            });

            // Update Livewire ketika Select2 berubah
            $('#categoryProductStore').on('change', function (e) {
                @this.set('category_product_store_id', $(this).val());
            });

            $('#brandProductStore').on('change', function (e) {
                @this.set('brand_product_store_id', $(this).val());
            });

            // Inisialisasi format Rupiah untuk input harga
            var priceInput = document.getElementById('selling_price');
            if (priceInput) {
                var cleave = new Cleave(priceInput, {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand',
                    numeralDecimalMark: ',',
                    delimiter: '.',
                    numeralPositiveOnly: true
                });

                // Update Livewire ketika harga berubah
                priceInput.addEventListener('input', function(e) {
                    let rawValue = cleave.getRawValue();
                    @this.set('selling_price', rawValue);
                });

                // Set nilai awal jika ada
                @if(isset($selling_price) && $selling_price)
                    cleave.setRawValue(@this.get('selling_price'));
                @endif
            }

            // Update Select2 ketika komponen Livewire diperbarui
            Livewire.on('editProduct', (productId) => {
                setTimeout(() => {
                    $('#categoryProductStore').val(@this.get('category_product_store_id')).trigger('change');
                    $('#brandProductStore').val(@this.get('brand_product_store_id')).trigger('change');
                    
                    // Update format harga
                    if (priceInput && @this.get('selling_price')) {
                        cleave.setRawValue(@this.get('selling_price'));
                    }
                }, 100);
            });

            Livewire.on('createProduct', () => {
                setTimeout(() => {
                    $('#categoryProductStore').val('').trigger('change');
                    $('#brandProductStore').val('').trigger('change');
                    
                    // Reset format harga
                    if (priceInput) {
                        cleave.setRawValue('');
                    }
                }, 100);
            });
        });
    </script>
    @endpush
</div>