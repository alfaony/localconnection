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

                <div class="form-group text-right">
                    <button type="submit" class="btn btn-{{ isset($itemRequest) ? 'primary' : 'success' }}">
                        <i class="fas fa-save mr-1"></i>{{ isset($itemRequest) ? ' Update' : ' Simpan' }}
                    </button>
                    <a href="{{ route('item-request.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i>Batal
                    </a>
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
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                placeholder: '-- Pilih Kategori --',
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

        .select2-selection__choice__remove
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