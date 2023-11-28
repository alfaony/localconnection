@extends('adminlte::page')

@section('content_header')
    <h1>Tambah Hari Kerja Baru</h1>
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

@if(@$manager)
<form action="{{ route('manager.update',$manager) }}" method="post">
    @method('put')
@else
<form action="{{ route('manager.store') }}" method="post">
@endif
    @csrf
<div class="container">
    <div class="row mt-4">
        <div class="col-md-2">
            <label>No. Perintah Kerja:</label>
            <input type="text" class="form-control" value="{{ $nomor }}" readonly>
        </div>
        <div class="offset-md-6 col-md-4">
            <label>Tanggal:</label>
            <input type="date" name="date" class="form-control" value="{{ $dateNow }}" min="{{ $dateCreate }}" required>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <label>Nama Proyek:</label>
            <select name="project" class="form-control select2" required>
                <option disabled selected></option>
                @foreach($project as $a)
                    <option value="{{ $a->id }}" {{ @$manager->project_id == $a->id ? 'selected' : '' }}>{{ $a->title }}</option>
                @endforeach
                <!-- Anda dapat menambahkan opsi lain di sini -->
            </select>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-6">
            <label>Nama Manajer:</label>
            <input type="text" name="name" class="form-control" placeholder="Nama Menajer" value="{{ @$manager->name }}" required>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-6">
            <label>No. Handphone:</label>
            <input type="text" name="phone" class="form-control" placeholder="Handphone" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/^((0|62)[0-9]*)$/, '$1');"  value="{{ @$manager->phone }}">
        </div>
    </div>
    

    {{--
    <div class="row mt-3">
        <div class="col-md-6">
            <label>Mode Pembayaran:</label>
            <select class="form-control select2" name="payment_method" id="payment_method" required>
                <option selected disabled></option>
                @foreach($paymentMode as $index => $value)
                    <option value="{{ $index }}" {{ @$manager->payment_method == $index ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
                <!-- Anda dapat menambahkan opsi lain di sini -->
            </select>
        </div>
    </div>
    --}}

    <table class="table table-bordered mt-4" id="tabelKerja">
        <thead>
            <tr>
                <th>No</th>
                <th>Pekerja</th>
                <th>Pembayaran</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @if(@$manager)
            @php $noJob = 1; @endphp
            @foreach($manager->job as $a)
            
            <tr data-key="{{ $a->id }}">
                <td>
                    {{ $noJob++ }}
                </td>
                <td>
                    <select class="form-control select2 employeeChange" name="employee[]" id="employee_{{ $a->id }}" required>
                        @foreach($employee as $b)
                            <option value="{{ $b->id }}" data-job="{{ $a->id }}" {{ $b->id == $a->employee_id ? 'selected' : '' }}  > {{ $b->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="form-control select2 paymentMethod" name="payment_method[]" id="payment_method_{{ $a->id }}" required>
                        <option selected disabled>Pilih</option>
                        @foreach($paymentMode as $index => $value)
                        <option value="{{ $index }}" {{ @$a->payment_method == $index ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="date" id="start_date_{{ $a->id }}" name="start_date[]" class="form-control countingSalary" value="{{ $a->start_date }}" min="{{ $dateCreate }}" required>
                </td>
                <td>
                    <input type="date" id="end_date_{{ $a->id }}" data-key="{{ $a->id }}" name="end_date[]" class="form-control countingSalary" value="{{ $a->end_date }}" min="{{ $dateCreate }}" required>
                </td>
                <td id="total_show_{{ $a->id }}">
                    {{ number_format($a->total,0,',','.') }}
                </td>
                <td>
                    <input type="hidden" name="idChild[]" class="form-control" value="{{ $a->id }}" required>
                    <input type="hidden" id="work_time_{{ $a->id }}" name="work_time[]" class="form-control">
                    <input type="hidden" id="total_{{ $a->id }}" name="total[]" value="{{ $a->total }}" class="form-control">
                    @canAccess('destroyJob','managers')
                    <button type="button" data-id="{{ $a->id }}" class="btn btn-danger btnHapusData"><i class="fa fa-trash"></i></button>
                    @endcanAccess
                </td>
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>

    <div class="col-md-4 text-right">

    </div>
    @if(!@$manager)
    <div class="row">
        <div class="col-md-8">
            <button class="btn btn-primary mb-2 allowSubmit" id="btnTambahBarisManager"><i class="fa fa-plus"></i> Pekerja</button>
        </div>
        <div class="col-md-4 text-right">
            <h4 id="total_all_show"></h4>
            <input type="hidden" name="total_all" id="total_all" value="">

            <!-- <button class="btn btn-primary mt-2 allowSubmit">Ubah</button> -->

            <button class="btn btn-primary mt-2 allowSubmit">Simpan</button>

        </div>
    </div>
    @else
    <div class="row">
        <div class="col-md-8">
            <button class="btn btn-primary mb-2 allowSubmit" id="btnTambahBarisManager"><i class="fa fa-plus"></i> Pekerja</button>
        </div>
        <div class="col-md-4 text-right">
            <h4 id="total_all_show">Total : {{ @$manager->job ? 'Rp. '.number_format($manager->job->sum('total'),0,',','.') : ''}}</h4>
            <input type="hidden" name="total_all" id="total_all" value="">

            <!-- <button class="btn btn-primary mt-2 allowSubmit">Ubah</button> -->

            <button class="btn btn-primary mt-2 allowSubmit">Ubah</button>

        </div>
    </div>
    @endif
</div>
</form>
@stop
@section('js')
<!-- Menambahkan Bootstrap JS dan Popper.js -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    $(document).ready(function () 
    {
        $(".employeeChange").change(function (e) 
        { 
            e.preventDefault();
            var employeeSelected = $(this).val();
            var key = $(this).find(':selected').data('job');
            var start_date = $("#start_date_"+key).val();
            var end_date = $("#end_date_"+key).val();
            var paymentMethodValue = $('#payment_method_'+key).val();

            if(employeeSelected && key && start_date && end_date && paymentMethodValue)
            {
                countSalary(paymentMethodValue, key, employeeSelected, null, null, start_date, end_date);
            }

        });

        $('#tabelKerja').on('change keyup', '.countingSalary', function() 
        {
            // Untuk mendapatkan baris tr tempat .countingSalary berada
            var row = $(this).closest('tr');

            // Ambil nilai dari select employee
            var employeeSelected = row.find('select').val();

            // Ambil data-daily dan data-montly dari option yang dipilih di dalam select
            var dailySalary = row.find('select option:selected').data('daily');
            var monthlySalary = row.find('select option:selected').data('montly');

            // Ambil nilai dari input start_date dan end_date
            var startDate = row.find('input[name="start_date[]"]').val();
            var endDate = row.find('input[name="end_date[]"]').val();

            var key = row.find('input[name="end_date[]"]').data('key');

            var paymentMethodValue = $('#payment_method_'+key).val();

            if(employeeSelected && key && startDate && endDate && paymentMethodValue)
            {
                countSalary(paymentMethodValue, key, employeeSelected, dailySalary, monthlySalary, startDate, endDate);
            }

        });

        $('#tabelKerja').on('change keyup', '.paymentMethod', function()
        { 
            var row = $(this).closest('tr');

            // Ambil nilai dari select employee
            var employeeSelected = row.find('select').val();

            // Ambil data-daily dan data-montly dari option yang dipilih di dalam select
            var dailySalary = row.find('select option:selected').data('daily');
            var monthlySalary = row.find('select option:selected').data('montly');

            // Ambil nilai dari input start_date dan end_date
            var startDate = row.find('input[name="start_date[]"]').val();
            var endDate = row.find('input[name="end_date[]"]').val();

            var key = row.find('input[name="end_date[]"]').data('key');

            var paymentMethodValue = $('#payment_method_'+key).val();

            if(employeeSelected && key && startDate && endDate && paymentMethodValue)
            {
                countSalary(paymentMethodValue, key, employeeSelected, dailySalary, monthlySalary, startDate, endDate);
            }
        });
    });
</script>
<script>
    $(document).ready(function() 
    {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih Pekerja'
        });
    });

    $('#btnTambahBarisManager').click(function() 
    {
        var key = generateRandomString(4);
        var noBaris = $('#tabelKerja tbody tr').length + 1; // Menghitung jumlah baris untuk nomor baris selanjutnya
        var indexKeys = generateRandomString(4);
        var dataSelect = @json($employee);
        var payment = @json($paymentMode);
        
        var projectOptions = '';

        $.each(dataSelect, function(index, employee) 
        {
            projectOptions += `<option value="${employee.id}" data-daily="${employee.salary_daily}" data-montly="${employee.salary_monthly}" data-job="${key}" >${employee.name} </option>`;
        });

        var paymentOptions = '';

        $.each(payment, function(index, method) 
        {
            paymentOptions += `<option value="${index}">${method} </option>`;
        });

        var row = 
        `
            <tr data-key="${key}">
                <td>
                    ${noBaris}
                    </td>
                <td>
                    <select class="form-control select2 employeeChange" name="employee[]" id="employee_${key}" required>
                        <option selected disabled>Pilih</option>
                        ${projectOptions}
                    </select>
                </td>
                <td>
                    <select class="form-control select2 paymentMethod" name="payment_method[]" id="payment_method_${key}" required>
                        <option selected disabled>Pilih</option>
                        ${paymentOptions}
                    </select>
                </td>
                <td>
                    <input type="date" id="start_date_${key}" name="start_date[]" class="form-control" min="{{ $dateCreate }}" required>
                </td>
                <td>
                    <input type="date" id="end_date_${key}" data-key="${key}" name="end_date[]" class="form-control countingSalary" min="{{ $dateCreate }}" required>
                </td>
                <td id="total_show_${key}">
                    Rp. 0
                </td>
                <td>
                    <input type="hidden" name="idChild[]" class="form-control" value="" required>
                    <input type="hidden" id="work_time_${key}" name="work_time[]" class="form-control">
                    <input type="hidden" id="total_${key}" name="total[]" class="form-control">
                    <button type="button" class="btn btn-danger btnHapus"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
        `;

        $('#tabelKerja tbody').append(row);
        $('#employee_' + key).select2({
            width: '100%'
        });
    });

    $('#tabelKerja').on('click', '.btnHapus', function() {
        $(this).closest('tr').remove(); // Hapus baris yang berisi tombol yang diklik
        updateNomorBaris(); // Perbarui nomor baris
        hitungTotalKeseluruhan();
    });

    $('.btnHapusData').click(function() {
        var dataId = $(this).data('id');
        
        // Tampilkan konfirmasi penghapusan
        var userConfirmation = confirm("Apakah anda yakin untuk menghapus data ini?");
        
        if(userConfirmation) 
        {
            let url = "{{ route('manager.destroy.job',':id') }}";
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

    function countSalary(paymentMethod = null, key = null, employeeId = null,salaryDaily = null,salaryMontly = null,startDate = null,endDate = null)
    {
        console.log(paymentMethod, key, salaryDaily, salaryMontly, startDate, endDate);
        $.ajax({
            type: "GET",
            url: "{{ route('manager.counting') }}",
            data: {paymentMethod:paymentMethod,employeeId:employeeId,salaryDaily:salaryDaily,salaryMontly:salaryMontly,startDate:startDate,endDate:endDate},
            success: function (response) 
            {

                if(response.data)
                {
                    $("#work_time_"+key).val(response.data.duration);
                    $("#total_"+key).val(response.data.total);

                    $("#total_show_"+key).html(formatRupiah(response.data.total,'Rp. '));

                    hitungTotalKeseluruhan();
                }

            },
            error: function (jqXHR, textStatus, errorThrown) 
            {
                // Dapatkan pesan error dari response
                let errorMsg = '';
                if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                    $.each(jqXHR.responseJSON.errors, function (index, value) 
                    {
                        // console.log(index);
                        if(index == "endDate")
                        {
                            index = "end_date";
                        }

                        if(index == "startDate")
                        {
                            index = "start_date";
                        }

                        idk = index+"_"+key;
                        console.log(idk);

                        $("#"+idk).val('');

                        errorMsg += value + '<br>';
                    });
                } else {
                    errorMsg = 'Terjadi kesalahan. Silakan coba lagi.';
                }

                // Tampilkan pesan error dengan SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    html: errorMsg
                });
            }
        });        
    }
    
    function updateNomorBaris() {
        $('#tabelKerja tbody tr').each(function(index) {
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
        var total = 0;
        
        $('#tabelKerja tbody tr').each(function() 
        {
            console.log($(this).find('input[name="total[]"]'));

            var subTotal = parseFloat($(this).find('input[name="total[]"]').val() || 0);
            console.log(subTotal);
            total += subTotal;
            // Update sub total untuk baris ini
        });
        
        // Update total keseluruhan
        $('#total_all_show').text('Total :'+formatRupiah(total,'Rp. '));
        $("#total_all").val(total);
    }

// Panggil fungsi hitungTotalKeseluru

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
    .container {
        background-color: #fff;
        padding: 10px;
        border-radius: 5px;
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
</style>
@stop
