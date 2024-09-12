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
        </div>
    </div>

    <div class="card mb-3" id="card_form_template" style="display:none;"> 
        <div class="card-header">
            <h3>Formulir</h3>
        </div>
        <div class="card-body" id="form_template">
            
        </div>

    </div>

    <div class="card">

        <div class="card-body row">
            <!-- Upload KTP -->
            <div class="col-md-6 mb-3">
                <label for="id_card_image">Upload KTP</label>
                @if(Auth::user()->id_card_image)
                <div class="mt-1 mb-2">
                    <img src="{{ Storage::url(Auth::user()->id_card_image) }}" alt="Tanda Tangan" class="img-fluid" style="max-width: 200px; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                </div>
                @else
                <input type="file" name="id_card_image" id="id_card_image" class="form-control" accept="image/*" required>
                @endif
            </div>

            <!-- Tanda Tangan -->
            <div class="col-md-6 mb-3">
                <label for="signature">Tanda Tangan <span class="text-danger">*</span></label>
                <div class="signature-container">
                    <canvas id="signature-pad" class="signature-pad" width=400 height=200></canvas>
                </div>
                <button type="button" id="clear-signature" class="btn btn-warning mt-2">Hapus Tanda Tangan</button>
                <input type="hidden" name="signature_image" id="signature_image">
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end p-3">
        <button type="submit" id="submit-button" class="btn btn-primary">Simpan</button>
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
    $(document).ready(function() {
         // Initialize Select2
         $('.select2').select2({
            placeholder: 'Pilih',
            allowClear: true
        });

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
            if (signaturePad.isEmpty()) {
                // Prevent form submission
                e.preventDefault();
                // Display an alert using SweetAlert2
                Swal.fire({
                    icon: 'error',
                    title: 'Tanda Tangan Diperlukan',
                    text: 'Harap tanda tangan sebelum mengajukan surat!',
                });
            } else {
                // Convert signature to base64 and set it in the hidden input field
                var signatureDataUrl = signaturePad.toDataURL(); // Get image as base64
                $('#signature_image').val(signatureDataUrl); // Set hidden input value
            }
        });
    });
</script>


<script>
    $(document).ready(function() {
        // Handle letter type selection
        $('#letter_type_id').on('change', function() {
            var selectedTemplate = $(this).find('option:selected').data('template');
            console.log(selectedTemplate);
            
            let form = ``;
            $("#card_form_template").show();
            $("#form_template").empty();
            switch (selectedTemplate) 
            {
                case 'sk_magang_template':
                    form = `
                    <div class="letter-template">
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
                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <h2>
                                        Surat Keterangan Magang
                                    </h2>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="salary_date">Nama Lengkap</label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                                </div>
                                <!-- Jabatan -->
                                <div class="col-md-12 mb-3">
                                    <label for="jabatan">Jabatan <span class="text-danger">*</span></label>
                                    <select class="form-control selectOrCreate2" name="position_new_id" id="position_id" required>
                                        <option value="" selected disabled>Pilih </option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->name }}" >{{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                
                                <div class="col-md-12 mb-3">
                                    <label for="monthly_salary">Kompensasi Magang <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control"  id="amount_show" placeholder="Rp 30.000.000" oninput="formatRupiahFormat(this,'amount')" required/>
                                    <input type="hidden" id="amount" name="salary" name="name"  value="{{ old('salary') }}">
                                </div>
                
                                <div class="col-md-12 mb-3">
                                    <label for="salary_date">Tanggal Pembayaran Kompensasi Magang <span class="text-danger">*</span></label>
                                    <input type="text" name="salary_date" class="form-control" placeholder="Masukkan tanggal perhitungan gaji" value="{{ old('salary_date') }}" required>
                                </div>
                
                                <div class="col-md-12 mb-3">
                                    <label for="working_hours">Jam Kerja <span class="text-danger">*</span></label>
                                    <input type="text" name="working_hours" class="form-control" placeholder="Masukkan jam kerja" value="{{ old('working_hours') }}" required>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="working_hours">Jam Kerja <span class="text-danger">*</span></label>
                                    <input type="text" name="working_hours" class="form-control" placeholder="Masukkan jam kerja" value="{{ old('working_hours') }}" required>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="masa_kerja">Masa Kerja <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                                        <span class="input-group-text">hingga</span>
                                        <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="deskripsi_tugas">Deskripsi Tugas <span class="text-danger">*</span></label>
                                    <input class="thriveEditor form-control" id="description_description_task" data-ids="description_task" name="description_task" required/>
                                </div>
                            </div>
                        </div>
                    </div>
                    `
                    $("#form_template").html(form);

                    generateThriveEditor("description_task");

                    $('.selectOrCreate2').select2({
                        placeholder: 'Pilih',
                        allowClear: true,
                        // tags: true
                    });
                    break;
                case 'perjanjian_kerja_template':
                    form = `
                    <!-- Perjanjian Kerja Template -->
                    <div class="letter-template" id="perjanjian_kerja_template" >
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
                                    <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                                </div>
                                <!-- Jabatan -->
                                <div class="col-md-12 mb-3">
                                    <label for="jabatan">Jabatan <span class="text-danger">*</span></label>
                                    <select class="form-control selectOrCreate2" name="position_new_id" id="position_id" required>
                                        <option value="" selected disabled>Pilih </option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->name }}" >{{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                
                                <div class="col-md-12 mb-3">
                                    <label for="monthly_salary">Gaji Bulanan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control"  id="amount_show" placeholder="Rp 30.000.000" oninput="formatRupiahFormat(this,'amount')" required/>
                                    <input type="hidden" id="amount" name="salary" name="name"  value="{{ old('salary') }}">
                                </div>
                
                                <div class="col-md-12 mb-3">
                                    <label for="salary_date">Tanggal Perhitungan Gaji <span class="text-danger">*</span></label>
                                    <input type="text" name="salary_date" class="form-control" placeholder="Masukkan tanggal perhitungan gaji" value="{{ old('salary_date') }}" required>
                                </div>
                
                                <div class="col-md-12 mb-3">
                                    <label for="working_hours">Jam Kerja <span class="text-danger">*</span></label>
                                    <input type="text" name="working_hours" class="form-control" placeholder="Masukkan jam kerja" value="{{ old('working_hours') }}" required>
                                </div>
                
                                <div class="col-md-12 mb-3">
                                    <label for="work_location">Penempatan <span class="text-danger">*</span></label>
                                    <input type="text" name="work_location" class="form-control" placeholder="Masukkan penempatan kerja"  value="{{ old('work_location') }}" required>
                                </div>
                
                                <div class="col-md-12 mb-3">
                                    <label for="job_responsibilities">Tanggung Jawab Pekerjaan <span class="text-danger">*</span></label>
                                    <input class="thriveEditor form-control" id="description_job_responsibilities" data-ids="job_responsibilities" name="job_responsibilities" required />
                                </div>
                            </div>
                        </div>
                    </div>
                    `
                    $("#form_template").html(form);

                    // Install
                    generateThriveEditor("job_responsibilities");

                    $('.selectOrCreate2').select2({
                        placeholder: 'Pilih',
                        allowClear: true,
                        tags: true
                    });

                    break;
                case 'sk_jabatan_template':
                    form = `
                        <div class="letter-template">
                            <div class="card">
                                <div class="card-body">
                                    <!-- Nama PT (Picklist) -->
                                    <div class="col-md-12 mb-3">
                                        <label for="company_name">Perihal <span class="text-danger">*</span></label>
                                        <input type="text" name="perihal" value="{{ old('perihal') ?? '' }}" class="form-control" required>
                                    </div>
                                    <!-- Nama PT (Picklist) -->
                                    <div class="col-md-12 mb-3">
                                        <label for="company_name">Nama PT <span class="text-danger">*</span></label>
                                        <input type="text" value="{{ $company['name'] ?? '' }}" class="form-control" readonly>
                                    </div>

                                    <!-- Nama Lengkap -->
                                    <div class="col-md-12 mb-3">
                                        <label for="full_name">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" value="{{ Auth::user()->name }}" placeholder="Masukkan Nama Lengkap" readonly>
                                    </div>

                                    @if(Auth::user()->last_position)
                                    <!-- Jabatan -->
                                    <div class="col-md-12 mb-3">
                                        <label for="jabatan">Jabatan <span class="text-danger">*</span></label>
                                        <select class="form-control" name="position_id" id="position_id" readonly>
                                            <option value="" selected disabled>Pilih </option>
                                            @foreach($lastPositon as $position)
                                                <option value="{{ $position->name }}" {{ Auth::user()->last_position->position_id == $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif

                                    <!-- Gaji Bulanan -->
                                    <div class="col-md-12 mb-3">
                                        <label for="monthly_salary">Gaji Bulanan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control"  id="amount_show" placeholder="Rp 30.000.000" oninput="formatRupiahFormat(this,'amount')" required/>
                                        <input type="hidden" id="amount" name="salary" name="name"  value="{{ old('salary') }}">
                                    </div>
                    
                                    <div class="col-md-12 mb-3">
                                        <label for="salary_date">Tanggal Perhitungan Gaji <span class="text-danger">*</span></label>
                                        <input type="text" name="salary_date" class="form-control" placeholder="Masukkan tanggal perhitungan gaji" value="{{ old('salary_date') }}" required>
                                    </div>

                                    <!-- Jam Kerja -->
                                    <div class="col-md-12 mb-3">
                                        <label for="working_hours">Jam Kerja <span class="text-danger">*</span></label>
                                        <input type="text" name="working_hours" class="form-control" placeholder="Masukkan Jam Kerja" required>
                                    </div>

                                    <!-- Penempatan -->
                                    <div class="col-md-12 mb-3">
                                        <label for="work_location">Penempatan <span class="text-danger">*</span></label>
                                        <input type="text" name="work_location" class="form-control" placeholder="Masukkan Penempatan Kerja" required>
                                    </div>

                                    <!-- Tanggung Jawab Pekerjaan (Text Area) -->
                                    <div class="col-md-12 mb-3">
                                        <label for="job_responsibilities">Tanggung Jawab Pekerjaan <span class="text-danger">*</span></label>
                                        <input class="thriveEditor form-control" id="description_job_responsibilities" data-ids="job_responsibilities" name="job_responsibilities" required />
                                    </div>
                                </div>
                            </div>
                        </div>  
                    `;

                    $("#form_template").html(form);


                    // Install
                    generateThriveEditor("job_responsibilities");

                    $('.selectOrCreate2').select2({
                        placeholder: 'Pilih',
                        allowClear: true,
                        tags: true
                    });
                    break;
                case 'sk_tugas_template':
                    form = `
                        <div class="letter-template card">
                            <div class="card-body">
                                <div class="col-md-12 mb-3">
                                    <label for="company_name">Perihal <span class="text-danger">*</span></label>
                                    <input type="text" name="perihal" value="{{ old('perihal') ?? '' }}" class="form-control" required>
                                </div>
                                <!-- Nama PT (Picklist) -->
                                <div class="col-md-12 mb-3">
                                    <label for="company_name">Nama PT <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ $company['name'] ?? '' }}" class="form-control" readonly>
                                </div>

                                <!-- Nama Lengkap -->
                                <div class="col-md-12 mb-3">
                                    <label for="full_name">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" value="{{ Auth::user()->name }}" placeholder="Masukkan Nama Lengkap" readonly>
                                </div>
                                
                                @if(isset(Auth::user()->last_position))
                                <!-- Jabatan Terakhir-->
                                <div class="col-md-12 mb-3">
                                    <label for="jabatan">Jabatan Terakhir <span class="text-danger">*</span></label>
                                    <select class="form-control" name="position_old_id" id="position_old_id" required>
                                        <option value="" selected disabled>Pilih </option>
                                        @foreach($lastPositon as $positionlast)
                                            <option value="{{ $positionlast->name }}" {{ $positionlast->id == Auth::user()->last_position->position_id ? 'selected' : '' }}>{{ $positionlast->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <!-- Jabatan Terbaru (Picklist) -->
                                <div class="col-md-12 mb-3">
                                    <label for="jabatan">Jabatan Terbaru<span class="text-danger">*</span></label>
                                    <select class="form-control selectOrCreate2" name="position_new_id" id="position_id" required>
                                        <option value="" selected disabled>Pilih </option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->name }}" >{{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Gaji Bulanan -->
                                <div class="col-md-12 mb-3">
                                    <label for="monthly_salary">Gaji Bulanan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control"  id="amount_show" placeholder="Rp 30.000.000" oninput="formatRupiahFormat(this,'amount')" required/>
                                    <input type="hidden" id="amount" name="salary" name="name"  value="{{ old('salary') }}">
                                </div>
                
                                <div class="col-md-12 mb-3">
                                    <label for="salary_date">Tanggal Perhitungan Gaji <span class="text-danger">*</span></label>
                                    <input type="text" name="salary_date" class="form-control" placeholder="Masukkan tanggal perhitungan gaji" value="{{ old('salary_date') }}" required>
                                </div>

                                <!-- Jam Kerja -->
                                <div class="col-md-12 mb-3">
                                    <label for="working_hours">Jam Kerja <span class="text-danger">*</span></label>
                                    <input type="text" name="working_hours" class="form-control" placeholder="Masukkan Jam Kerja" required>
                                </div>

                                <!-- Penempatan -->
                                <div class="col-md-12 mb-3">
                                    <label for="work_location">Penempatan <span class="text-danger">*</span></label>
                                    <input type="text" name="work_location" class="form-control" placeholder="Masukkan Penempatan Kerja" required>
                                </div>

                                <!-- Tanggung Jawab Pekerjaan (Text Area) -->
                                <div class="col-md-12 mb-3">
                                    <label for="job_responsibilities">Tanggung Jawab Pekerjaan <span class="text-danger">*</span></label>
                                    <input class="thriveEditor form-control" id="description_job_responsibilities" data-ids="job_responsibilities" name="job_responsibilities" required />
                                </div>
                            </div>
                        </div>
                    `
                    $("#form_template").html(form);


                    // Install
                    generateThriveEditor("job_responsibilities");

                    $('.selectOrCreate2').select2({
                        placeholder: 'Pilih',
                        allowClear: true,
                        tags: true
                    });

                    break;
                case 'sk_bekerja_resign_template':
                    form = `
                        <div class="card scrollable-div" id="printThis">
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <h3><strong>SURAT PENGUNDURAN DIRI</strong></h3>
                                </div>
                                
                                <p>Kepada Yth,<br>
                                HRD/Director<br>
                                {{ $company['name'] }}</p>
                        
                                <p>Saya yang bertanda tangan di bawah ini :</p>
                        
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <td style="width: 150px;"><strong>Nama</strong></td>
                                                <td>: {{ Auth::user()->name ?? "" }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>NIK</strong></td>
                                                <td>: {{ Auth::user()->id_card ?? "" }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Jabatan</strong></td>
                                                <td>: {{ Auth::user()->last_position_now ? Auth::user()->last_position_now->position->name : "" }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Perusahaan</strong></td>
                                                <td>
                                                    {{ $company['address'] }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                        
                                <p>Menyatakan dengan sesungguhnya bahwa mulai tanggal {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }} saya mengajukan permohonan untuk mengundurkan diri sebagai karyawan {{ $company['name'] ?? "" }}</p>
                        
                                <p>Ucapan terima kasih yang sebesar-besarnya saya sampaikan atas kesempatan yang diberikan untuk bekerja di {{ $company['name'] ?? "" }}</p>
                        
                                <p>Melalui surat ini saya memohon maaf kepada segenap manajemen dan karyawan {{ $company['name'] ?? "" }} jika terdapat kesalahan yang saya perbuat selama bekerja. Besar harapan saya {{ $company['name'] ?? "" }} akan terus berkembang dan maju.</p>
                            </div>
                        </div>
                    `;

                    $("#form_template").html(form);
                    break;
                case 'sk_perjanjian':
                    $("#card_form_template").hide();
                    break;
                case 'sk_pengantar_kerja_template':
                    form = `
                    <!-- sk_pengantar_kerja_template -->
                    <div class="letter-template" >
                        <div class="card">
                            <div class="card-body">
                                <div class="col-12 justify-content-center align-items-center">
                                    <h6 class="text-center"><strong>SURAT KETERANGAN KERJA</strong></h6>
                                    <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
                                </div>

                                <div class="col-12 justify-content-center align-items-center mt-4 mb-4">
                                    <p>Saya yang bertandatangan di bawah ini :</p>
                                </div>

                                <div class="col-12 mt-2">
                                    <!-- Table to display company and employee information -->
                                    <table class="table table-borderless detail-table">
                                        <tbody>
                                            <tr>
                                                <td>Nama</td>
                                                <td>: {{ $company['name'] ?? "" }}</td>
                                            </tr>
                                            <tr>
                                                <td>Penanggung Jawab</td>
                                                <td>: {{ $company['director'] ?? "" }}</td>
                                            </tr>
                                            <tr>
                                                <td>Alamat</td>
                                                <td>: {{ $company['address'] ?? "" }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    Dengan ini menerangkan bahwa :
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Nama</td>
                                                <td>: {{ Auth::user()->name }}</td>
                                            </tr>
                                            <tr>
                                                <td>No KTP</td>
                                                <td>: {{ Auth::user()->id_card }}</td>
                                            </tr>
                                            <tr>
                                                <td>Alamat</td>
                                                <td>: {{ Auth::user()->address }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <p>Bertindak atas nama pribadi, sebagai pekerja / staff yang dipekerjakan, selanjutnya
                                                        disebut sebagai PIHAK KEDUA.</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="col-12">
                                    <p class="text-justify"> 
                                        Telah bekerja di perusahaan kami, {{ $company['name'] ?? "" }}, sejak tgl {{ Auth::user()->first_position ? \Carbon\Carbon::parse(Auth::user()->first_position->start_date)->locale('id')->translatedFormat('d F Y') : "" }} 
                                        s/d {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }} dengan posisi sebagai {{ Auth::user()->last_position_now ? Auth::user()->last_position_now->position->name : '' }}. Selama bekerja di perusahaan kami, yang bersangkutan telah bekerja dengan baik sesuai SOP perusahaan dan tidak pernah terlibat dalam tindakan yang dapat merugikan perusahaan.
                                    </p>
                                </div>
                                @if(isset(Auth::user()->last_position))
                                <!-- Jabatan Terakhir-->
                                <div class="col-md-12 mb-3">
                                    <label for="jabatan">Jabatan Terakhir <span class="text-danger">*</span></label>
                                    <select class="form-control" name="position_old_id" id="position_old_id" required>
                                        <option value="" selected disabled>Pilih </option>
                                        @foreach($lastPositon as $positionlast)
                                            <option value="{{ $positionlast->name }}" {{ $positionlast->id == Auth::user()->last_position->position_id ? 'selected' : '' }}>{{ $positionlast->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    `
                    $("#form_template").html(form);
                    break;

                    
                default:
                    break;
            }
        });

        
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