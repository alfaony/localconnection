@extends('adminlte::page')

@section('content')
<div class="container mt-3">
    <div class="card">
        <div class="card-body">
            <div class="col-md-12">
                @if(Session::get('deletePurchase'))
                <div class="alert alert-success mt-3">Berhasil Menghapus Pembelian</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            @if(@$workOrder)
            <form method="post" action="{{ route('work-order.update',@$workOrder) }}" enctype="multipart/form-data">
            @method('put')
            @else
            <form method="post" action="{{ route('work-order.store') }}" enctype="multipart/form-data">
            @endif
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <h2>Surat Perintah Kerja</h2>
                        <div class="mt-5">No SPK: {{ $nomorWorkOrder }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label for="date" class="col-sm-8 col-form-label text-right">Tanggal:</label>
                            <div class="col-sm-4">
                                <input type="date" name="date" class="form-control" id="date" value="{{ old('date') ?? @$workOrder->date }}" required>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <label class="col-sm-8 col-form-label text-right">Finance:</label>
                            <div class="col-sm-4">
                                <span class="form-control-plaintext">{{ $userCreate ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3 mt-3">
                    <label class="col-sm-2 col-form-label">Pilih No. Quote</label>
                    <div class="col-sm-10">
                        {{-- 
                        @if(@$workOrder)
                        <select name="quote" class="form-control select2" required>
                            <option value="" disabled selected>Quote</option>
                            @foreach($quote as $a)
                            <option value="{{ $a->id }}" data-customer="{{ $a->customer ? $a->customer->name : '' }}" {{ @$workOrder->quote_id == $a->id ? 'selected' : '' }}>{{ $a->number_result  ?? ''}}</option>
                            @endforeach
                            <!-- Anda bisa menambahkan option lainnya di sini -->
                        </select>
                        @else
                        <select name="quote" class="form-control select2 quoteSuggestion" required>
                            <option value="" disabled selected>Quote</option>
                            @foreach($quote as $a)
                            <option value="{{ $a->id }}" data-customer="{{ $a->customer ? $a->customer->name : '' }}" {{ @$workOrder->quote_id == $a->id ? 'selected' : '' }}>{{ $a->number_result  ?? ''}}</option>
                            @endforeach
                            <!-- Anda bisa menambahkan option lainnya di sini -->
                        </select>
                        @endif
                        --}}
                        @if(@$workOrder)
                        <input type="hidden" name="quote_id" id="quote_id" value="{{ @$workOrder->quote_id }}">
                        <select name="quote" id="selectQuote" class="form-control selectQuote" required>
                            
                            <!-- Anda bisa menambahkan option lainnya di sini -->
                        </select>
                        @else
                        <select name="quote" id="selectQuote" class="form-control quoteSuggestion" required>
                            
                        </select>
                        @endif
                    </div>
                </div>
        
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Customer</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="customer" id="customer" placeholder="Pilih Nomor Quote" required readonly>
                    </div>
                </div>
        
                <table class="table table-bordered" id="tableWorkOrder">
                    <thead>
                        <tr class="d-flex">
                            <th class="col-1" >No</th>
                            <th class="col-4">Produk / Jasa</th>
                            <th class="col-3">Description</th>
                            <th class="col-1">Qty</th>
                            <th class="col-2">Budget</th>
                            <th class="col-1">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(@$workOrder)
                        @php $nomorBaris = 1; @endphp
                        @foreach($workOrder->workOrderProduct->sortBy('sort') as $a)
                        <tr class="d-flex" data-key="{{ $a->id }}">
                            <td class="col-1">
                                {{ $nomorBaris++ }}
                                </td>
                            <td class="col-4">
                                <select class="form-control productChange select2" name="product[]" id="product_{{ $a->id }}" required>
                                    <option value="" selected disabled>Pilih</option>
                                    @foreach($product as $b)
                                    <option value="{{ $b->id }}" data-key="{{ $a->id }}" {{ $a->product_id == $b->id ? 'selected' : '' }} >{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="col-3">
                                <input type="text" class="thriveEditor" data-ids="{{ $a->id }}" id="description_{{ $a->id }}" name="description[]" id="description_{{ $a->id }}" placeholder="Description" value="{{  @$a->description }}" required>
                            </td>
                            <td class="col-1">
                                <input type="hidden" id="price_{{ $a->id }}" name="price[]" data-key="{{ $a->id }}" min="1" class="form-control" placeholder="Quantity" value="{{ @$a->price_buy }}" required>
                                <input type="number" id="qty_{{ $a->id }}" name="qty[]" data-key="{{ $a->id }}" min="1" class="form-control qtyChange" placeholder="Quantity" value="{{ @$a->qty }}" required>
                            </td>
                            <td class="col-2" id="sub_total_show_{{ $a->id }}">
                                {{ 'Rp. '.number_format($a->sub_total,0,',','.') }}
                            </td>
                            <td class="col-1">
                                <input type="hidden" class="form-control" placeholder="Total" id="" name="ids[]" value="{{ $a->id }}">
                                <input type="hidden" class="form-control" placeholder="Total" id="sub_total_{{ $a->id }}" name="sub_total[]" value="{{ $a->sub_total }}">
                                <button class="btn btn-danger btnHapusData" data-id="{{ $a->id }}"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
        
                <div class="row mt-3">
                    <div class="col-md-8">
                        <button class="btn btn-primary mb-2 allowSubmit" id="btnTambahBarisWorkOrder"><i class="fa fa-plus"></i> Product</button>
                    </div>
                    <div class="col-2 offset-10">
                        <div class="d-flex justify-content-between mb-2">
                            <div>Total:</div>
                            <div id="sub_total_result">Rp 0</div>
                        </div>
                    </div>
                </div>
        
                <div class="mb-3">
                    <label for="fileUpload">Mohon Upload Quote yang sudah di tanda-tangani:</label>
                    @if(@$workOrder)
                    @if($workOrder->quote_file) 
                        <div class="mb-2">
                            <a href="{{ Storage::url($workOrder->quote_file) }}" class="btn btn-sm btn-primary" download><i class="fa fa-file-pdf"></i> Download</a>
                        </div>
                    @endif
                    <input type="file" name="quote_file" class="form-control-file" id="fileUpload" accept=".pdf">
                    @else
                    <input type="file" name="quote_file" class="form-control-file" id="fileUpload" accept=".pdf" required>
                    @endif
                </div>
                
                @if(@$workOrder)
                <button type="button" id="submit" class="btn btn-primary">Ubah</button>
                @else
                <button type="button" id="submit" class="btn btn-primary">Simpan</button>
                @endif
                <button type="submit" id="btnSubmit" style="display:none;"></button>
            </form>
        </div>
    </div>
</div>
@stop
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
    $(document).ready(function () 
    {
        $("#submit").click(function (e) 
        { 
            e.preventDefault();
            let submited = true;
            var input = document.querySelectorAll('input.thriveEditor');
            input.forEach(function (textarea) 
            {
                console.log(textarea);
                val = textarea.value;
                if(!val)
                {
                    Swal.fire({
                        title: 'Warning!',
                        text: 'Deskripsi Harus Diisi.',
                        icon: 'warning',
                        showConfirmButton: false,
                        timer: 1000
                    });

                    submited = false;
                }
            });

            if(submited)
            {
                $("#btnSubmit").click();
            }
            
        });
        
        $(".quoteSuggestion").change(function (e) 
        { 
            e.preventDefault();
            var id = $(this).find(':selected').val();
            if(id)
            {
                let url = "{{ route('work-order.suggestionQuote',':id') }}";
                url = url.replace(":id",id);

                $.ajax({
                    type: "GET",
                    url: url,
                    success: function (response) 
                    {
                        if(response.product)
                        {
                            $('#tableWorkOrder tbody').empty();
                            
                            $.each(response.product, function (index, value) 
                            { 
                                addForm(value.product_id,value.price_buy,value.qty,value.description)    
                            });
                        }  
                    }
                });
            }
            
        });
        $(".select2").on("change", updateCustomerField);

        $(".minNol").on("change", function () 
        {
            let nol = $(this).val();
            if(nol <= 0)
            {
                $(this).val(0);
            }
        });
    
        $('#tableWorkOrder').on('change', '.productChange', function (e) { 
            e.preventDefault();
            console.log("bekerja dengan baikk");

            var key = $(this).find(':selected').data('key');

            var productSelected = $(this).val();
            var qty = $("#qty_"+key).val();

            productPrice(productSelected, key, function(price) 
            {
                // This code runs after the price is updated
                $("#price_"+key).val(price).change();

                if(productSelected && key && qty && price) 
                {
                    countProduct(productSelected, key, qty, price);
                }
            });

        });

        $('#tableWorkOrder').on('change', '.qtyChange', function (e) { 
            e.preventDefault();

            var key = $(this).data('key');

            var productSelected = $("#product_"+key).find(':selected').val();
            var qty = $("#qty_"+key).val();
            var price = $("#price_"+key).val();
            
            if(qty <= 0)
            {
                $("#qty_"+key).val(1);
            }

            if(productSelected && key && qty && price)
            {
                countProduct(productSelected, key, qty, price);
            }
        });
    });
</script>
<!-- Load -->
<script>
    $(document).ready(function() 
    {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih Nomor Quote'
        });

        $('#selectQuote').select2({
            placeholder: 'Pilih Nomor Quote',
            ajax: 
            {
                url: "{{ route('quote.select2') }}",
                dataType: 'json',
                data: function(params) {
                    return {
                        number_result: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(quote) {
                            console.log(quote.customer.name);
                            return {
                                id: quote.id,
                                text: quote.number_result,
                                data: 
                                {
                                    customer: quote.customer ? quote.customer.name : ''
                                }
                            };
                        })
                    };
                }
            }
        });

        
        $('#selectQuote').on('select2:select', function(e) {
            // Ambil data-customer dari opsi yang dipilih
            // console.log(e);
            var customerName = e.params.data.data.customer;
            // Menampilkan nilai tersebut di elemen dengan id "customer"
            $("#customer").val(customerName);
        });

        var selectedValueQuote = "{{ @$workOrder->quote_id }}";
        if(selectedValueQuote)
        {
            title = "{{ @$workOrder->quote->number_result }}";
            customerName = "{{ @$workOrder->quote->customer->name }}";
            // Create an option element with the selected value
            var newOption = new Option(title, selectedValueQuote, true, true);
    
            // Append the option to the select2 element and trigger change
            $('#selectQuote').append(newOption).trigger('change');
            $("#customer").val(customerName);
        }

        calculation();
        // change
        

        $('#btnTambahBarisWorkOrder').click(function() {            
            var key = generateRandomString(4);
            var noBaris = $('#tableWorkOrder tbody tr').length + 1; // Menghitung jumlah baris untuk nomor baris selanjutnya
            var dataSelect = @json($product);
            
            var projectOptions = '';

            $.each(dataSelect, function(index, product) 
            {
                projectOptions += `<option value="${product.id}" data-key="${key}">${product.name} </option>`;
            });

            const row = `
                <tr class="d-flex" data-key="${key}">
                    <td class="col-1">
                        ${noBaris}
                        </td>
                    <td class="col-4">
                        <select class="form-control productChange" name="product[]" id="product_${key}" required>
                            <option value="" selected disabled>Pilih</option>
                            ${projectOptions}
                        </select>
                    </td>
                    <td class="col-3">
                        <input type="text" class="thriveEditor" data-ids="${key}" name="description[]" id="description_${key}" value=""  placeholder="Description" required>
                    </td>
                    <td class="col-1">
                        <input type="hidden" id="price_${key}" name="price[]" data-key="${key}" min="1" class="form-control" value="" required>
                        <input type="number" id="qty_${key}" name="qty[]" data-key="${key}" min="1" class="form-control qtyChange" placeholder="Quantity" value="1" required>
                    </td>
                    <td class="col-2" id="sub_total_show_${key}">
                        Rp 0
                    </td>
                    <td class="col-1">
                        <input type="hidden" class="form-control" placeholder="Total" id="" name="ids[]">
                        <input type="hidden" class="form-control" placeholder="Total" id="sub_total_${key}" name="sub_total[]">
                        <button class="btn btn-danger btnHapus"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            `;
            
            $('#tableWorkOrder tbody').append(row);
            
            $('#product_' + key).select2({
                width: '100%'
            });

            generateThriveEditor(key);
        });

        
        $('#tableWorkOrder').on('click','.btnHapus', function() 
        {
            $(this).closest('tr').remove();
            calculation();
            updateNomorBaris();
        });

        $('.btnHapusData').click(function() {
            var dataId = $(this).data('id');
            
            // Tampilkan konfirmasi penghapusan
            var userConfirmation = confirm("Apakah anda yakin untuk menghapus data ini?");
            
            if(userConfirmation) 
            {
                let url = "{{ route('work-order.destroy.product',':id') }}";
                url = url.replace(':id',dataId);
                // $(this).closest('tr').remove(); // Hapus baris yang berisi tombol yang diklik
                // updateNomorBaris(); // Perbarui nomor baris
                // Jika user mengonfirmasi, lakukan request AJAX untuk menghapus data
                $.ajax({
                    type: "POST", // atau "DELETE" sesuai dengan metode yang Anda gunakan
                    url: url, // Gantikan dengan endpoint Anda
                    data: 
                    {
                        id: dataId,
                        _token: "{{ csrf_token() }}", // Untuk Laravel, tambahkan CSRF token
                        _method: "DELETE" // Untuk Laravel, tambahkan CSRF token
                    },
                    success: function(response) 
                    {
                        Swal.fire(
                            {
                            title: 'Berhasil!',
                            text: 'Berhasil Menghapus Data',
                            icon: 'success',
                            timer: 1500, // 3 detik
                            timerProgressBar: true,
                            showConfirmButton: false,
                            showConfirmButton: false, // Menghilangkan tombol OK/Confirm
                        });
                    },
                    error: function(jqXHR, textStatus, errorThrown) 
                    {
                        alert("Terjadi kesalahan saat menghapus data");
                    }
                });

                $(this).closest('tr').remove(); // Hapus baris yang berisi tombol yang diklik
                updateNomorBaris(); // Perbarui nomor baris
                calculation();
            }
        });
    });

    function productPrice(product,workOrderProductId,callback)
    {
        $.ajax({
            type: "GET",
            url: "{{ route('work-order.productPrice') }}",
            data: {product:product,workOrderProductId:workOrderProductId},
            success: function (response) 
            {
                var price = response.data;
                callback(price); // Pass the price to the callback function
            },
        });
    }

    function addForm(defaultProductId = null, price = null, defaultQty = 1,defaultDescription = null) 
    {
        var key = generateRandomString(4);
        var noBaris = $('#tableWorkOrder tbody tr').length + 1;
        var dataSelect = @json($product);
        
        var projectOptions = '';

        $.each(dataSelect, function(index, product) {
            var isSelected = product.id == defaultProductId ? 'selected' : '';
            projectOptions += `<option value="${product.id}" data-key="${key}" ${isSelected}>${product.name} </option>`;
        });

        const row = `
            <tr class="d-flex" data-key="${key}">
                <td class="col-1">${noBaris}</td>
                <td class="col-4">
                    <select class="form-control productChange" name="product[]" id="product_${key}" required>
                        <option value="" ${!defaultProductId ? 'selected' : ''} disabled>Pilih</option>
                        ${projectOptions}
                    </select>
                </td>
                <td class="col-3">
                    <input type="text" class="thriveEditor" data-ids="${key}" name="description[]" id="description_${key}" value="${defaultDescription}"  placeholder="Description" required>
                </td>
                <td class="col-1">
                    <input type="hidden" id="price_${key}" name="price[]" data-key="${key}" min="1" class="form-control" value="" required>
                    <input type="number" id="qty_${key}" name="qty[]" data-key="${key}" min="1" class="form-control qtyChange" placeholder="Quantity" value="${defaultQty}" required>
                </td>
                <td class="col-2" id="sub_total_show_${key}">Rp 0</td>
                <td class="col-1">
                    <input type="hidden" class="form-control" placeholder="Total" id="" name="ids[]">
                    <input type="hidden" class="form-control" placeholder="Total" id="sub_total_${key}" name="sub_total[]">
                    <button class="btn btn-danger btnHapus"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
        `;

        $('#tableWorkOrder tbody').append(row);

        $('#product_' + key).select2({
            width: '100%'
        });

        generateThriveEditor(key,defaultDescription);
        
        $('#product_' + key).trigger('change');
        // Anda bisa menambahkan event listener untuk `productChange` dan `qtyChange` di sini jika Anda ingin memicu perubahan lain setelah row ditambahkan
    }

    function calculation()
    {
        console.log("----CALCULATION WORKING-----");

        var tax = parseInt($("#tax").val()) || 0;
        var service_fee = parseInt($("#service_fee").val()) || 0;

        var discount = parseInt($("#discount").val()) || 0;
        var charges = parseInt($("#charges").val()) || 0;

        
        var ppn_title = tax <= 0 ? 'PPN: 0%' : 'PPN: '+tax+'%';
        var sub_total = 0;
        $('#tableWorkOrder tbody tr').each(function() 
        {
            // console.log($(this).find('input[name="sub_total[]"]'));
            var subTotal = parseFloat($(this).find('input[name="sub_total[]"]').val() || 0);
            console.log(subTotal);
            sub_total += subTotal;
        });


        $("#sub_total_result").html(formatRupiah(sub_total,'Rp. '));
    }

    function updateNomorBaris() {
        $('#tableWorkOrder tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    function generateRandomString(length) 
    {
        var result = '';
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
    }

    function formatRupiahFormat(input, inputNonFormat) 
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
            numStr = '';
        } else {
            // Menghapus angka 0 di depan jika input diawali dengan 0
            rupiah = rupiah.replace(/^0+/, '');
            input.value = rupiah;
        }

        // Update 'salary' input with non-formatted number
        let parsedValue = parseInt(numStr);
        document.getElementById(inputNonFormat).value = isNaN(parsedValue) ? 0 : parsedValue;
    }

    function formatRupiah(angka, prefix)
    {
        var number_string = angka.toString().replace('/[^,\d]/g', '').toString(),
        split   		= number_string.split(','),
        sisa     		= split[0].length % 3,
        rupiah     		= split[0].substr(0, sisa),
        ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);

        // tambahkan titik jika yang di input sudah menjadi angka ribuan
        if(ribuan){
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }

    function countProduct(productId,key,qty,price)
    {        
        $.ajax({
            type: "GET",
            url: "{{ route('work-order.productCounting') }}",
            data: {product:productId,qty:qty,price:price},
            success: function (response) 
            {
                if(response.data)
                {
                    console.log(response.data );

                    $("#sub_total_"+key).val(response.data);
                    $("#sub_total_show_"+key).html(formatRupiah(response.data || 0,'Rp. '));
                }

                calculation();
            },
        });
    }

    function updateCustomerField() 
    {
        // Mendapatkan nilai dari atribut data-customer
        var customerName = $("#selectQuote").find("option:selected").data("customer");
        
        // Menampilkan nilai tersebut di elemen dengan id "customer"
        $("#customer").val(customerName);
    }
</script>
@stop
@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
   body 
   {
        font-family: Arial, sans-serif;
        /* padding: 20px; */
        background-color: #f4f4f4;
    }
    .select2-selection__rendered 
    {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single 
    {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }
    .ql-container 
    {
        min-height: 150px;
        height: auto;
    }
</style>
@stop

