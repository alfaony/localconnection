@extends('adminlte::page')

@section('title', isset($itemRequest) ? 'Edit Permintaan Barang' : 'Buat Permintaan Barang')

@section('content_header')
    <h1 class="m-0 text-dark">{{ isset($itemRequest) ? 'Edit Permintaan Barang' : 'Buat Permintaan Barang' }}</h1>
@stop

@section('content')
    @include('components.alert')
    <div class="card">
        <div class="card-body">
            <form action="{{ isset($itemRequest) ? route('item-request.update', $itemRequest) : route('item-request.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($itemRequest))
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label for="supplier_category_id"><i class="fas fa-folder mr-1"></i>Kategori</label>
                    <select name="supplier_category_id" id="supplier_category_id" class="form-control select2" style="width: 100%;" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('supplier_category_id', $itemRequest->supplier_category_id ?? '') == $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="type"><i class="fas fa-list mr-1"></i>Type Supplier</label>
                    <select name="type" id="supplier_type_id" class="form-control select2" style="width: 100%;" required>
                        <option value="">-- Pilih Tipe --</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected(old('type', $itemRequest->type ?? '') == $type->id)>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="product_supplier_id">Pilih Product Supplier</label>
                    <select id="product_supplier_id" name="product_supplier_id[]" class="form-control select2" multiple>
                    </select>
                </div>

                <div class="form-group">
                    <label for="item_name"><i class="fas fa-tag mr-1"></i>Nama Barang</label>
                    <input type="text" name="item_name" id="item_name" 
                           class="form-control @error('item_name') is-invalid @enderror"
                           value="{{ old('item_name', $itemRequest->item_name ?? '') }}" 
                           placeholder="Masukkan nama barang" required>
                    @error('item_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left mr-1"></i>Deskripsi</label>
                    <input class="thriveEditor form-control" id="description_description" data-ids="description" name="description" value="{{ old('description', @$itemRequest->description ?? '') }}" placeholder="yang akan dicetak di perjanjian"/>

                    @error('description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="estimated_price"><i class="fas fa-dollar-sign mr-1"></i>Estimasi Harga</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rp</span>
                        </div>
 
                        <input type="text" class="form-control @error('estimated_price') is-invalid @enderror" id="estimated_price_show" placeholder="30.000.000" oninput="formatRupiahFormat(this,'estimated_price')" required/>
                        <input type="hidden" id="estimated_price" name="estimated_price"   value="{{ old('estimated_price', $itemRequest->estimated_price ?? '') }}" >
                        @error('estimated_price')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="form-group">
                    <label for="qty"><i class="fas fa-list-ol mr-1"></i>Jumlah</label>
                    <input type="number" class="form-control @error('qty') is-invalid @enderror" id="qty" name="qty" value="{{ old('qty', @$itemRequest->qty ?? '') }}" placeholder="1" required/>
                    @error('qty')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="sprinter_ids"><i class="fas fa-user-friends mr-1"></i>Sprinter</label>
                    <select name="assigned_pic_id" id="sprinter_ids" class="form-control select2" style="width: 100%;">
                        @foreach($sprinters->pluck('name', 'id') as $id => $name)
                            <option value="{{ $id }}" @if(isset($itemRequest) && $itemRequest->assigned_pic_id == $id) selected @endif>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mt-3">
                    <label for="picture">Foto Pendukung (opsional)</label>
                    @if (!empty($itemRequest?->picture))
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $itemRequest->picture) }}" class="img-thumbnail"
                                style="max-width: 250px;">
                        </div>
                    @endif
                    <input type="file" name="picture" id="picture" class="form-control" accept="image/*">
                </div>
                @if (isset($shareWa) && $shareWa) 
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="shareWa" name="shareWa" {{ isset($itemRequest) ? '' : (isset($shareWa) && $shareWa ? 'checked' : '') }}>
                        <label class="custom-control-label" for="shareWa">Blast ke Whatsapp Vendor</label>
                    </div>
                </div>
                @endif

                <div class="form-group text-right">
                    @if($existsSprinter)
                    @canAccess('store', 'item_requests')
                    @canAccess('update', 'item_requests')
                    <button type="submit" class="btn btn-{{ isset($itemRequest) ? 'primary' : 'success' }}" 
                        onclick="return confirm('Yakin Data Request Telah Sesuai ?')">
                        <i class="fas fa-save mr-1"></i>{{ isset($itemRequest) ? ' Perbarui' : ' Simpan' }}
                    </button>
                    @endcanAccess
                    @endcanAccess
                    <a href="{{ route('item-request.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i>Batal
                    </a>
                    @else
                    <div class="alert alert-warning text-center" role="alert">
                        <i class="fas fa-info-circle mr-1"></i> Belum ada sprinter di perusahaan ini, silahkan tambahkan sprinter terlebih dahulu
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
@stop


@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="{{ asset('js/thriveEditor.js') }}"></script>
    <script>
        function loadProductSuppliers() {
            const supplierCategoryId = $('#supplier_category_id').val();
            const supplierTypeId = $('#supplier_type_id').val();

            if (supplierCategoryId && supplierTypeId) {
                $.ajax({
                    url: '{{ route("item-request.fetch-suppliers") }}',
                    method: 'POST',
                    data: {
                        supplier_category_id: supplierCategoryId,
                        supplier_type_id: supplierTypeId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        if(res.success) {
                            const $select = $('#product_supplier_id');
                            const selectedId = '{{ old("product_supplier_id", isset($itemRequest) ? $itemRequest->product_supplier_id : '') }}';
                            $select.empty();
                            if (res.data && res.data.length > 0) {
                                res.data.forEach(item => {
                                    const isSelected = item.id == selectedId ? 'selected' : '';
                                    $select.append(`<option value="${item.id}" ${isSelected}>${item.store_name + ' - ' + item.owner_name}</option>`);
                                });
                            }

                            $select.select2({
                                width: 'resolve',
                            });

                        } else {
                            alert("Product Supplier Tidak Ditemukan");
                        }
                    },
                    error: function () {
                        alert('Gagal memuat product supplier');
                    }
                });
            }
        }

        $('#supplier_category_id, #supplier_type_id').on('change', loadProductSuppliers);
    </script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                placeholder: '-- Pilih --',
                allowClear: true
            });
        });


        let discount = document.getElementById("estimated_price").value;
        if (discount) 
        {
            document.getElementById("estimated_price_show").value = discount;
            formatRupiahFormat(document.getElementById("estimated_price_show"),"estimated_price"); // Format default value
        }

        function formatRupiahFormat(input = null, inputNonFormat = null) 
        {
            let numStr = input.value.toString().replace(/[^,\d]/g, '');
            let split = numStr.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;

            if (numStr === "" || parseInt(numStr) === 0) {
                input.value = '';
                numStr = 0;
            } else {
                // Menghapus angka 0 di depan jika input diawali dengan 0
                rupiah = rupiah.replace(/^0+/, '');
                input.value = rupiah;
            }

            // Update 'salary' input with non-formatted number
            document.getElementById(inputNonFormat).value = parseInt(numStr);
        }
    </script>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .thriveEditor {
            height: 100px;
        }
        .select2-selection__choice
        {
            background-color: #007bff !important;
            border: 1px solid #007bff !important;
        }

        
        {
            color: #fe0700 !important;
            border: 1px solid #007bff !important;
        }
        .select2-selection__rendered {
            line-height: 31px !important;
        }
        .select2-container .select2-selection--single {
            height: 35px !important;
        }
        .select2-selection__arrow {
            height: 34px !important;
        }
    </style>
@stop