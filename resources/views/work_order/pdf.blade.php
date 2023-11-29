@extends('adminlte::page')

@section('content')
<div class="container mt-5">
    <div class="col-md-12">
        @if(Session::get('store'))
        <div class="alert alert-success mt-3">Surat Perintah Kerja Berhasil Ditambahkan</div>
        @endif
        @if(Session::get('update'))
        <div class="alert alert-success mt-3">Surat Perintah Kerja Berhasil Diperbarui</div>
        @endif
    </div>
    <div class="card" id="printThis">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <h2>{{ $company['name'] ?? ''}}</h2>
                    <p>{{ $company['address'] }}</p>
                </div>
                <div class="col-6 text-right">
                    <h2>Surat Perintah Kerja</h2>
                    <p>No SPK # {{ $nomorWorkOrder }}</p>
                    <p>SPK Date: {{ $workOrder->date }}</p>
                    <!-- <p>P.O#: 23/12/2019</p> -->
                </div>
            </div>
        
            <div class="row mt-5">
                <div class="col-12">
                    <h4>To</h4>
                    <p>{{ $workOrder->quote ? $workOrder->quote->customer->name : '' }}</p>
                    <p>{{ $workOrder->quote ? $workOrder->quote->customer->address : '' }}</p>
                </div>
            </div>
        
            <table class="table mt-5" id="tableWorkOrder">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Product / Service</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Satuan Penghitung</th>
                        <th>Budget</th>
                    </tr>
                </thead>
                <tbody>
                    @if(@$workOrder)
                    @php $nomorBaris = 1; @endphp
                    @foreach($workOrder->workOrderProduct->sortBy('sort') as $a)
                    <tr data-key="{{ $a->id }}">
                        <td>
                            {{ $nomorBaris++ }}
                            </td>
                        <td>
                            {{ $a->product ? $a->product->name : '' }}
                        </td>
                        <td>
                            {!! $a->description ?? '' !!}
                            <input type="hidden" name="description[]" id="description_{{ $a->id }}" class="form-control" placeholder="Description" value="{{  @$a->description }}" required>
                        </td>
                        <td>
                            {{ $a->qty ?? '' }}
                            <input type="hidden" id="qty_{{ $a->id }}" name="qty[]" data-key="{{ $a->id }}" min="1" class="form-control qtyChange" placeholder="Quantity" value="{{ @$a->qty }}" required>
                        </td>
                        <td>
                            {{ $a->product ? $a->product->method_count : '' }}
                        </td>
                        <td id="sub_total_show_{{ $a->id }}">
                            {{ 'Rp. '.number_format($a->sub_total,0,',','.') }}
                            <input type="hidden" class="form-control" placeholder="Total" id="" name="ids[]" value="{{ $a->id }}">
                            <input type="hidden" class="form-control" placeholder="Total" id="sub_total_{{ $a->id }}" name="sub_total[]" value="{{ $a->sub_total }}">
                        </td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
            <div class="row">
            <div class="col-md-3 offset-md-9 mt-4">
                <div class="d-flex justify-content-between">
                    <div class="text-right">TOTAL:</div>
                    <div class="strongText" id="sub_total_result">
                        Rp. 0
                    </div>
                </div>
            </div>
            <div class="col-2 offset-10 text-left mt-5">
                <h7>Pihak Penjual</h7>
            </div>
            <div class="col-2 offset-10 text-left mt-3">
                <img src="{{ asset('logo/paraf.png') }}" alt="Signature" style="with:auto; height:150px">
            </div>
        </div>
    </div>
</div>
<div class="col-md-12 text-center mt-3"> <!-- Penambahan class text-center dan mt-3 -->
    <a href="{{ route('work-order.edit',$workOrder->slug) }}" class="btn btn-primary"><i class="fa fa-edit"></i>Edit</a>
    <button type="button" id="downloadWorkOrder" class="btn btn-success"><i class="fa fa-file-pdf"></i> {{__('Download')}}</button>
</div>
@stop
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    $(document).ready(function () 
    {
        $("#downloadWorkOrder").click(function (e) 
        { 
            e.preventDefault();
            prinsts();
            
        });

    
        $('#tableWorkOrder').on('change', '.productChange', function (e) { 
            e.preventDefault();
            console.log("bekerja dengan baikk");

            var key = $(this).find(':selected').data('key');

            var productSelected = $(this).val();
            var qty = $("#qty_"+key).val();

            if(productSelected && key && qty)
            {
                countProduct(productSelected, key, qty);
            }

        });

        $('#tableWorkOrder').on('change', '.qtyChange', function (e) { 
            e.preventDefault();

            var key = $(this).data('key');

            var productSelected = $("#product_"+key).find(':selected').val();
            var qty = $("#qty_"+key).val();

            if(qty <= 0)
            {
                $("#qty_"+key).val(1);
            }

            if(productSelected && key && qty)
            {
                countProduct(productSelected, key, qty);
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
            placeholder: 'Pilih Pekerja'
        });

        updateCustomerField();
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
                <tr data-key="${key}">
                    <td>
                        ${noBaris}
                        </td>
                    <td>
                        <select class="form-control productChange" name="product[]" id="product_${key}" required>
                            <option value="" selected disabled>Pilih</option>
                            ${projectOptions}
                        </select>
                    </td>
                    <td>
                        <input type="text" name="description[]" id="description_${key}" class="form-control" placeholder="Description" required>
                        </td>
                    <td>
                        <input type="number" id="qty_${key}" name="qty[]" data-key="${key}" min="1" class="form-control qtyChange" placeholder="Quantity" value="1" required>
                    </td>
                    <td id="sub_total_show_${key}">
                        Rp 0
                    </td>
                    <td>
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
        });

        
        $('#tableWorkOrder').on('click','.btnHapus', function() 
        {
            $(this).closest('tr').remove();
            updateNomorBaris();
        });

        $('.btnHapusData').click(function() {
            var dataId = $(this).data('id');
            
            // Tampilkan konfirmasi penghapusan
            var userConfirmation = confirm("Apakah anda yakin untuk menghapus data ini?");
            
            if(userConfirmation) 
            {
                let url = "{{ route('quote.destroy.product',':id') }}";
                url = url.replace(':id',dataId);
                // $(this).closest('tr').remove(); // Hapus baris yang berisi tombol yang diklik
                // updateNomorBaris(); // Perbarui nomor baris
                // Jika user mengonfirmasi, lakukan request AJAX untuk menghapus data
                // $.ajax({
                //     type: "POST", // atau "DELETE" sesuai dengan metode yang Anda gunakan
                //     url: url, // Gantikan dengan endpoint Anda
                //     data: 
                //     {
                //         id: dataId,
                //         _token: "{{ csrf_token() }}", // Untuk Laravel, tambahkan CSRF token
                //         _method: "DELETE" // Untuk Laravel, tambahkan CSRF token
                //     },
                //     success: function(response) 
                //     {
                //         Swal.fire(
                //             {
                //             title: 'Berhasil!',
                //             text: 'Berhasil Menghapus Data',
                //             icon: 'success',
                //             timer: 1500, // 3 detik
                //             timerProgressBar: true,
                //             showConfirmButton: false,
                //             showConfirmButton: false, // Menghilangkan tombol OK/Confirm
                //         });
                //     },
                //     error: function(jqXHR, textStatus, errorThrown) 
                //     {
                //         alert("Terjadi kesalahan saat menghapus data");
                //     }
                // });

                $(this).closest('tr').remove(); // Hapus baris yang berisi tombol yang diklik
                updateNomorBaris(); // Perbarui nomor baris
                calculation();
            }
        });
    });

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

    function countProduct(productId,key,qty)
    {        
        $.ajax({
            type: "GET",
            url: "{{ route('work-order.productCounting') }}",
            data: {product:productId,qty:qty},
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
        var customerName = $(".select2").find("option:selected").data("customer");
        
        // Menampilkan nilai tersebut di elemen dengan id "customer"
        $("#customer").val(customerName);
    }

    function prinsts() 
    {
        let name = "{{ $nomorWorkOrder }}"+"_Surat_Perintah_Kerja";
        let printContents = document.getElementById("printThis").innerHTML;
        let originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.addEventListener("beforeprint", (event) => {
            document.title = name;
        });

        window.print();
        document.body.innerHTML = originalContents;
    }
</script>
@stop
@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

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
    p {
        margin-top: 1px;
        margin-bottom: 1px;
    }
    .strongText 
    {
      font-weight: bold;  /* Membuat teks menjadi tebal */
      color: #000000;    /* Warna teks, ganti dengan warna yang diinginkan */
    }
</style>
@stop

