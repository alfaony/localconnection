@extends('adminlte::page')

@section('content')
<div class="col-md-12 p-3">
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

<form action="{{ route('letter-submission.store') }}" method="POST"  enctype="multipart/form-data">
    @csrf
    <div class="card">
        <div class="card-header">
            <h3>Profile</h3>
        </div>
        <div class="card-body">
            <!-- Nama Lengkap -->
            <div class="col-md-12 mb-3">
                <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Masukkan Nama Lengkap" value="{{ Auth::user()->name }}" required>
            </div>

            <!-- Alamat -->
            <div class="col-md-12 mb-3">
                <label for="alamat">Alamat <span class="text-danger">*</span></label>
                <input type="text" name="address" class="form-control" placeholder="Masukkan Alamat Lengkap" value="{{ Auth::user()->address }}" required>
            </div>

            <!-- Nomor KTP -->
            <div class="col-md-12 mb-3">
                <label for="ktp">Nomor KTP <span class="text-danger">*</span></label>
                <input type="number" name="id_card" class="form-control" placeholder="Masukkan Nomor KTP" value="{{ Auth::user()->id_card }}"  required>
            </div>

            <!-- Upload KTP -->
            <div class="col-md-12 mb-3">
                <label for="id_card_image">Upload KTP</label>
                <input type="file" name="id_card_image" id="id_card_image" class="form-control" accept="image/*">
            </div>

            <!-- Formulir -->
            <div class="col-md-12 mb-3">
                <label for="surat">Surat <span class="text-danger">*</span></label>
                <select class="form-control select2" name="letter_type_id" id="letter_type_id" required>
                    <option value="" selected disabled>Pilih Surat</option>
                    @foreach($letterTypes as $letterType)
                        <option value="{{ $letterType->id }}" {{ @$letterSubmission->letter_type_id == $letterType->id ? 'selected' : '' }} data-template="{{ $letterType->template }}">{{ $letterType->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tanda Tangan -->
            <div class="col-md-12 mb-3">
                <label for="signature">Tanda Tangan <span class="text-danger">*</span></label>
                <div class="signature-container">
                    <canvas id="signature-pad" class="signature-pad" width=400 height=200></canvas>
                </div>
                <button type="button" id="clear-signature" class="btn btn-warning mt-2">Hapus Tanda Tangan</button>
                <input type="hidden" name="signature_image" id="signature_image">
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3>Formulir</h3>
        </div>
        <div class="card-body">
            <div class="letter-template card" id="sk_magang_template" style="display:none;">
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        <h2>
                            Surat Keterangan Magang
                        </h2>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="masa_kerja">Masa Kerja <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                            <span class="input-group-text">hingga</span>
                            <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="deskripsi_tugas">Deskripsi Tugas <span class="text-danger">*</span></label>
                        <input class="thriveEditor form-control" id="description_description_task" data-ids="description_task" name="description_task" />
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="deskripsi_magang">Deskripsi Magang <span class="text-danger">*</span></label>
                        <input class="thriveEditor form-control" id="description_description_intern" data-ids="description_intern" name="description_intern" />
                    </div>
                </div>
            </div>

            <!-- Perjanjian Kerja Template -->
            <div class="letter-template" id="perjanjian_kerja_template" style="display:none;">
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-center"><strong>PERJANJIAN KERJA</strong></h5>
                        <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
        
                        <p>Pada Hari Senin, XX Agustus 2024 bertempat di Jakarta, telah ditanda tangani perjanjian kerja sama antara:</p>
        
                        <!-- Table to display company and employee information -->
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td><strong>Nama</strong></td>
                                    <td>: {{ $company['name'] ?? "" }} </td>
                                </tr>
                                <tr>
                                    <td><strong>Penanggung Jawab</strong></td>
                                    <td>: {{ $company['director'] ?? "" }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat</strong></td>
                                    <td>: {{ $company['address'] ?? "" }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <p>Bertindak atas perusahaan yang mempekerjakan, selanjutnya disebut PIHAK PERTAMA.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Nama</strong></td>
                                    <td>: {{ Auth::user()->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>No KTP</strong></td>
                                    <td>: {{ Auth::user()->id_card }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat</strong></td>
                                    <td>: {{ Auth::user()->address }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <!-- Bagian Surat Keputusan -->
                    <div class="card-header">
                        <div class="col-md-12 mb-3">
                            <h5 class="mb-2 text-center"><strong>SURAT KEPUTUSAN MANAGEMENT</strong></h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Nama Lengkap</label>
                            <input type="string" class="form-control" value="{{ Auth::user()->name }}" readonly>
                        </div>
                        <!-- Jabatan -->
                        <div class="col-md-12 mb-3">
                            <label for="jabatan">Jabatan</label>
                            <select class="form-control selectOrCreate2" name="position_id" id="position_id">
                                <option value="" selected disabled>Pilih </option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->name }}" >{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="monthly_salary">Gaji Bulanan</label>
                            <input type="text" class="form-control"  id="amount_show" placeholder="Rp 30.000.000" oninput="formatRupiahFormat(this,'amount')"/>
                            <input type="hidden" id="amount" name="salary" name="name"  value="{{ old('salary') }}">
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Tanggal Perhitungan Gaji</label>
                            <input type="date" name="salary_date" class="form-control" placeholder="Masukkan tanggal perhitungan gaji" value="{{ old('salary_date') }}" >
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="working_hours">Jam Kerja</label>
                            <input type="text" name="working_hours" class="form-control" placeholder="Masukkan jam kerja" value="{{ old('working_hours') }}">
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="work_location">Penempatan</label>
                            <input type="text" name="work_location" class="form-control" placeholder="Masukkan penempatan kerja"  value="{{ old('work_location') }}">
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="job_responsibilities">Tanggung Jawab Pekerjaan</label>
                            <input class="thriveEditor form-control" id="description_job_responsibilities" data-ids="job_responsibilities" name="job_responsibilities" />
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="npwp_number">No. NPWP</label>
                            <input type="number" name="npwp_number" class="form-control" placeholder="Masukkan nomor NPWP" value="{{ old('npwp_number') }}" >
                        </div>
                    </div>
                </div>
            </div>

            <!-- SK Jabatan Template -->
            <div class="form-row letter-template" id="sk_jabatan_template" style="display:none;">
                <!-- You can add fields related to "SK Jabatan" here -->
            </div>

            <!-- SK Pengantar Kerja Template -->
            <div class="form-row letter-template" id="sk_pengantar_kerja_template" style="display:none;">
                <!-- You can add fields related to "SK Pengantar Kerja" here -->
            </div>

            <!-- SK Bekerja Resign Template -->
            <div class="form-row letter-template" id="sk_bekerja_resign_template" style="display:none;">
                <!-- You can add fields related to "SK Bekerja (Resign)" here -->
            </div>
        </div>

        <div class="d-flex justify-content-end p-3">
            <button type="button" id="submit-button" class="btn btn-primary">Simpan</button>
            <button type="submit" id="hidden-submit-button" class="btn btn-primary" style="display:none"></button>
        </div>
    </div>
</form>

@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('submit-button').addEventListener('click', function () {
        // Tampilkan alert SweetAlert2
        Swal.fire({
            title: 'Apakah data yang diajukan sudah sesuai?',
            text: "Pastikan semua data sudah benar sebelum mengirim!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, sudah sesuai',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika pengguna mengonfirmasi, submit form melalui tombol submit tersembunyi
                document.getElementById('hidden-submit-button').click();
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Setup Signature Pad
        var canvas = document.getElementById('signature-pad');
        var signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(0, 0, 0, 0)', // Transparent background
        });

        // Clear the signature
        $('#clear-signature').click(function() {
            signaturePad.clear();
        });

        // Handle form submission and ensure signature image is passed
        $('form').on('submit', function(e) {
            if (!signaturePad.isEmpty()) {
                // Convert signature to base64 and set it in the hidden input field
                var signatureDataUrl = signaturePad.toDataURL(); // Get image as base64
                $('#signature_image').val(signatureDataUrl); // Set hidden input value
            } 
        });
    });
</script>


<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: 'Pilih',
            allowClear: true
        });

        // Handle letter type selection
        $('#letter_type_id').on('change', function() {
            var selectedTemplate = $(this).find('option:selected').data('template');

            // Hide all letter templates
            $('.letter-template').hide();

            // Show the selected template
            if (selectedTemplate) {
                $('#' + selectedTemplate).show();
            }

            $('.selectOrCreate2').select2({
                placeholder: 'Pilih',
                allowClear: true,
                tags: true
            });
        });

        // Trigger change event if a letter type is already selected (for edit mode)
        $('#letter_type_id').trigger('change');
        
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
            input.value = '';
            numStr = 0;
        } else {
            // Menghapus angka 0 di depan jika input diawali dengan 0
            rupiah = rupiah.replace(/^0+/, '');
            input.value = 'Rp '+rupiah;
        }

        // Update 'salary' input with non-formatted number
        document.getElementById(inputNonFormat).value = parseInt(numStr);
    }
</script>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .select2-selection__rendered {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single {
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
    .signature-container {
        border: 1px solid #ced4da;
        border-radius: 5px;
        width: 400px;
        height: 200px;
    }

    .signature-pad {
        width: 100%;
        height: 100%;
    }
</style>
@endsection