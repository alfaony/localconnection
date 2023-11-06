@extends('adminlte::page')

@section('content')
<div class="containe mt-3">
    <div class="col-md-12">
        @if(Session::get('store'))
        <div class="alert alert-success mt-3">Berhasil Menubah Perusahaan</div>
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
    <div class="card">
        <div class="card-body">
            <h1>Setting Perusahaan</h1>
            <form method="post" action="{{ route('setting-company.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="name">Nama Perusahaan</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', isset($data['name']) ? $data['name'] : '') }}">
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', isset($data['address']) ? $data['address'] : '') }}">
                </div>

                <div class="form-group">
                    <label for="no_npwp">No. NPWP</label>
                    <input type="text" name="npwp_number" class="form-control" value="{{ old('npwp_number', isset($data['npwp_number']) ? $data['npwp_number'] : '') }}">
                </div>

                <div class="form-group">
                    <label for="direktur">Direktur</label>
                    <input type="text" name="director" class="form-control" value="{{ old('director', isset($data['director']) ? $data['director'] : '') }}">
                </div>

                <div class="form-group">
                    <label for="mata_uang_dasar">Mata Uang Dasar</label>
                    <input type="text" name="currency" class="form-control" value="{{ old('currency', isset($data['currency']) ? $data['currency']  : '') }}">
                </div>

                <div class="form-group">
                    <label for="nilai_tukar_1_usd">Nilai Tukar 1 USD</label>
                    <input type="text" class="form-control"  id="currency_usd_show" oninput="formatRupiahFormat(this,'currency_usd')" />
                    <input type="hidden" name="currency_usd" id="currency_usd" class="form-control" value="{{ old('currency_usd', isset($data['currency_usd']) ? $data['currency_usd'] : '') }}">
                </div>

                <div class="form-group">
                    <label for="file_nib">Upload File NIB</label>
                    @if($data['nib_file']) 
                        <div class="mb-2">
                            <a href="{{ Storage::url($data['nib_file']) }}"  class="btn btn-sm btn-primary"  download><i class="fa fa-file-pdf"></i> Download</a>
                        </div>
                    @endif
                    <input type="file" name="nib_file" class="form-control-file" accept=".pdf" >
                </div>

                <div class="form-group">
                    <label for="file_akta">Upload File Akta</label>
                    @if($data['acta_file']) 
                        <div class="mb-2">
                            <a href="{{ Storage::url($data['acta_file']) }}"  class="btn btn-sm btn-primary" download><i class="fa fa-file-pdf"></i> Download</a>
                        </div>
                    @endif
                    <input type="file" name="acta_file" class="form-control-file" accept=".pdf" >
                </div>

                <div class="form-group">
                    <label for="file_npwp">Upload File NPWP</label>
                    @if($data['npwp_file']) 
                        <div class="mb-2">
                            <a href="{{ Storage::url($data['npwp_file']) }}"  class="btn btn-sm btn-primary" download><i class="fa fa-file-pdf"></i> Download</a>
                        </div>
                    @endif
                    <input type="file" name="npwp_file" class="form-control-file" accept=".pdf" >
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>

        </div>
    </div>
</div>

@endsection
@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function () 
    {
        $('.select2').select2({
            width: '100%',
        });
    });

    $(document).ready(function () 
    {

        let currency_usd = document.getElementById("currency_usd").value;
        if (currency_usd) 
        {
            document.getElementById("currency_usd_show").value = currency_usd;
            formatRupiahFormat(document.getElementById("currency_usd_show"),"currency_usd"); // Format default value
        }

    });
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
            input.value = '0';
            numStr = 0;
        } else {
            // Menghapus angka 0 di depan jika input diawali dengan 0
            rupiah = rupiah.replace(/^0+/, '');
            input.value = 'Rp. '+rupiah;
        }

        // Update 'salary' input with non-formatted number
        document.getElementById(inputNonFormat).value = parseInt(numStr);
    }
</script>
@stop
