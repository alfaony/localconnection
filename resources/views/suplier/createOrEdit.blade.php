@extends('adminlte::page')

@section('content_header')
    <h1>Tambah Pembelian Baru</h1>
@stop


@section('content')
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

@if(@$suplier)
<form action="{{ route('suplier.update',$suplier) }}" method="post" enctype="multipart/form-data">
@method('put')
@else
<form action="{{ route('suplier.store') }}" method="post" enctype="multipart/form-data">
@endif
    @csrf
<div class="container">
    <div class="row mt-4">
        <div class="col-md-6">
            <label>No Pembelian:</label>
            <input type="text" class="form-control" value="{{ $nomor }}" readonly>
            
            <label class="mt-3">Nama Proyek:</label>
            <select class="form-control" name="project" id="selectProject" required>
                <option value="" selected disabled>Silahkan Pilih</option>
                @forelse($project as $a)
                <option value="{{ $a->id }}" {{ @$suplier->project_id == $a->id ? 'selected' : '' }}>{{ $a->title }}</option>
                @empty
                <option disabled>Kosong</option>
                @endforelse
                <!-- Tambahkan opsi lain sesuai kebutuhan -->
            </select>
            
            <label class="mt-3">Nama Supplier:</label>
            <input type="text" class="form-control" id="suplier" name="name" placeholder="Suplier" value="{{ old('name') ??  @$suplier->name }}" required>
            
            <label class="mt-3">No. Handphone:</label>
            <input type="text" id="phone" name="phone" class="form-control" placeholder="phone" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/^((0|62)[0-9]*)$/, '$1');" value="{{ old('phone') ?? @$suplier->phone }}">

            <label class="mt-3">Lampiran File</label>
            @if(@$suplier->file)
            <a href="{{ Storage::url('suplier/' . $suplier->file) }}" class="btn btn-sm btn-primary" download title="{{ $suplier->file }}"><i class="fa fa-file-pdf"></i></a>
            @endif
            <input type="file" name="file" class="form-control" placeholder="File" name="" id="">
            
        </div>
        <div class="col-md-6">
            <label>Tanggal:</label>
            <input type="date" name="date" class="form-control" value="{{ old('date') ?? @$suplier->date }}" min="{{ $dateCreate }}" required>
        </div>
    </div>
    
    <table class="table table-bordered mt-4" id="tabelPembelian">
        <thead>
            <tr>
                <th>No</th>
                <th>Product</th>
                <th>Deskripsi</th>
                <th width="15%">Harga Satuan</th>
                <th width="10%">Jumlah</th>
                <th width="15%">Total</th>
                <th width="5%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @if(@$suplier)
            @php $noChild = 1; @endphp
            @foreach($suplier->purchase as $a)
            <tr data-keyss="{{$a->id}}">
                <td>{{ $noChild++ }}</td>
                <td class="col-3">
                    <select class="form-control productChange selectConfig" name="product[]" id="product_{{ $a->id }}" required>
                        <option value="" selected disabled>Pilih</option>
                        @foreach($product as $b)
                        <option value="{{ $b->id }}" data-key="{{ $a->id }}"  {{ $a->product_id == $b->id ? 'selected' : '' }} >{{ $b->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" class="thriveEditor" data-ids="{{ $a->id }}" name="description[]" id="description_{{ $a->id }}" placeholder="Deskripsi" value="{{ $a->description }}" required>
                </td>
                <td id="price_show_{{ $a->id }}">
                    Rp. {{ number_format($a->price,0,',','.') }}
                </td>
                <td> 
                    <input type="hidden" name="price[]" class="form-control" data-keyss="{{ $a->id }}" id="price_{{ $a->id }}" value="{{ $a->price }}" required>
                    <input type="number" name="qty[]" class="form-control qty" data-keyss="{{$a->id}}" id="qty_{{$a->id}}" placeholder="Jumlah" value="{{ $a->qty }}" required>
                </td>
                <td id="sub_total_show_{{$a->id}}">{{'Rp. '.number_format($a->sub_total_price,0,',','.') }} </td>
                <td>
                    <input type="hidden" name="idChild[]" value="{{ $a->id }}">
                    <input type="hidden" name="sub_total[]" class="form-control" id="sub_total_{{$a->id}}" placeholder="Harga Satuan" value="{{ $a->sub_total_price }}">
                    <button type="button" data-id="{{ $a->id }}" class="btn btn-sm btn-danger btnHapusData"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>
    <button class="btn btn-primary mb-2" id="btnTambahBaris"><i class="fa fa-plus"></i> Pembelian</button>
    
    <div class="row">
        <div class="col-md-8"></div>
        <div class="col-md-4 text-right">
            <h4>Total: <span id="totalKeseluruhan">{{ 'Rp. '.number_format(@$suplier->total_price,0,',','.') ?? 'Rp 0' }}</span></h4>
            <input type="hidden" name="total" id="total" value="0">

            @if(@$suplier)
                <button  id="submit" type="button" class="btn btn-primary">Ubah</button>
            @else
                <button id="submit" type="button" class="btn btn-primary">Simpan</button>
            @endif
                <button type="submit" id="btnSubmit" style="display:none;"></button>

        </div>
    </div>

</div>
</form>

@stop
@section('js')
<!-- Menambahkan Bootstrap JS dan Popper.js -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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

        $('#tabelPembelian').on('change', '.productChange', function (e) 
        { 
            e.preventDefault();
            console.log("bekerja dengan baikk");

            var key = $(this).find(':selected').data('key');

            var productSelected = $(this).val();
            var qty = $("#qty_"+key).val();

            productPrice(productSelected, key, function(price) 
            {
                // This code runs after the price is updated
                $("#price_"+key).val(price).change();
                $("#price_show_"+key).html(formatRupiah(price,'Rp. '));

                subTotal = price * qty;
            
                $("#sub_total_show_"+key).html(formatRupiah(subTotal,'Rp. '));
                $("#sub_total_"+key).val(subTotal);

                hitungTotalKeseluruhan();
            });

        });
        
        // $("#tabelPembelian").on("change keyup", ".price", function (e) 
        // {
        //     e.preventDefault();
            
        //     key = $(this).data('keyss');

        //     // die;
        //     price = $('#price_'+key).val();
        //     qty = $('#qty_'+key).val();

            // subTotal = price * qty;
            
            // $("#sub_total_show_"+key).html(formatRupiah(subTotal,'Rp. '));
            // $("#sub_total_"+key).val(subTotal);
        //     hitungTotalKeseluruhan();

        // }); 
        
        $("#tabelPembelian").on("change keyup", ".qty", function (e) 
        {
            console.log("heree");
            e.preventDefault();
            key = $(this).data('keyss');
            
            // die;
            price = $('#price_'+key).val();
            qty = $(this).val();

            if(qty <= 0)
            {
                qty = 0;
                $("#qty_"+key).val(0);
            }

            subTotal = price * qty;
            console.log(subTotal);

            $("#sub_total_show_"+key).html(formatRupiah(subTotal,'Rp. '));
            $("#sub_total_"+key).val(subTotal);
            hitungTotalKeseluruhan();
        }); 
    });
</script>
<script>
    $('#selectProject').select2({
        placeholder: 'Pilih Proyek'
    });

    $('.selectConfig').select2({
        width: '100%',
    });

    var noBaris = $('#tabelPembelian tbody tr').length + 1;
    var indexKeys = 

    $('#btnTambahBaris').click(function() {
        var noBaris = $('#tabelPembelian tbody tr').length + 1; // Menghitung jumlah baris untuk nomor baris selanjutnya
        var indexKeys = generateRandomString(4);
        var dataSelect = @json($product);
            
        var projectOptions = '';

        $.each(dataSelect, function(index, product) 
        {
            projectOptions += `<option value="${product.id}" data-key="${indexKeys}">${product.name} </option>`;
        });

        var row = `
        <tr>
            <td>${noBaris}</td>
            <td class="col-3">
                <select class="form-control productChange" name="product[]" id="product_${indexKeys}" required>
                    <option value="" selected disabled>Pilih</option>
                    ${projectOptions}
                </select>
            </td>
            <td><input type="text" class="form-control thriveEditor" name="description[]" id="description_${indexKeys}" placeholder="Deskripsi" required></td>
            <td id="price_show_${indexKeys}">
                Rp. 0
            </td>
            <td> 
                <input type="hidden" name="price[]" class="form-control" data-keyss=${indexKeys} id="price_${indexKeys}" value="" required>
                <input type="number" name="qty[]" class="form-control qty" data-keyss=${indexKeys} id="qty_${indexKeys}" placeholder="Jumlah" value="1" required>
            </td>
            <td id="sub_total_show_${indexKeys}">Rp 0</td>
            <td>
                <input type="hidden" name="idChild[]" value="">
                <input type="hidden" name="sub_total[]" class="form-control" id="sub_total_${indexKeys}" placeholder="Harga Satuan">
                <button class="btn btn-danger btn-sm btnHapus"><i class="fa fa-trash"></i></button>
            </td>
        </tr>
    `;
    
        $('#tabelPembelian tbody').append(row);

        $('#product_' + indexKeys).select2({
            width: '100%'
        });

        generateThriveEditor(indexKeys);
    });

    $('#tabelPembelian').on('click', '.btnHapus', function() {
        $(this).closest('tr').remove(); // Hapus baris yang berisi tombol yang diklik
        updateNomorBaris(); // Perbarui nomor baris
    });
    
    $('.btnHapusData').click(function() 
    {
        var dataId = $(this).data('id');
        
        // Tampilkan konfirmasi penghapusan
        var userConfirmation = confirm("Apakah anda yakin untuk menghapus data ini?");
        
        if(userConfirmation) 
        {
            let url = "{{ route('suplier.destroy.purchase',':id') }}";
            url = url.replace(':id',dataId);

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
            Swal.fire({
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
            hitungTotalKeseluruhan();
        }
    });

    function productPrice(product,suplierProductId,callback)
    {
        $.ajax({
            type: "GET",
            url: "{{ route('suplier.productPrice') }}",
            data: {product:product,suplierProductId:suplierProductId},
            success: function (response) 
            {
                var price = response.data;
                callback(price); // Pass the price to the callback function
            },
        });
    }

    function updateNomorBaris() {
        $('#tabelPembelian tbody tr').each(function(index) {
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
            input.value = 'Rp '+rupiah;
        }

        // Update 'salary' input with non-formatted number
        document.getElementById(inputNonFormat).value = parseInt(numStr);
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

    function hitungTotalKeseluruhan() 
    {
        console.log("work");
        var total = 0;
        
        $('#tabelPembelian tbody tr').each(function() 
        {
            var subTotal = parseFloat($(this).find('input[name="sub_total[]"]').val() || 0);
            total += subTotal;
            // Update sub total untuk baris ini
        });
        
        console.log();
        // Update total keseluruhan
        $('#totalKeseluruhan').text(formatRupiah(total,'Rp. '));
        $("#total").val(total);
    }


</script>

@stop
@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<style>
   body 
   {
        font-family: Arial, sans-serif;
        /* padding: 20px; */
        background-color: #f4f4f4;
    }
    .container {
        background-color: #fff;
        padding: 10px;
        border-radius: 5px;
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
