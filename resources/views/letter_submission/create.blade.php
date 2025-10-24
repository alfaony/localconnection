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

<form action="{{ route('letter-submission.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card">
        <div class="card-header">
            <h3>Profile</h3>
        </div>
        <div class="card-body">
            <!-- Formulir -->
            <div class="col-md-12 mb-3">
                <label for="surat">Surat <span class="text-danger">*</span></label>
                <select class="form-control" name="letter_type_id" id="letter_type_id" required>
                    <option value="" selected disabled>Pilih Surat</option>
                    @foreach($letterTypes as $letterType)
                        <option value="{{ $letterType->id }}" 
                                {{ @$letterSubmission->letter_type_id == $letterType->id ? 'selected' : '' }} 
                                data-template="{{ $letterType->template }}">
                            {{ $letterType->name }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" class="form-control" name="user_last_position" id="user_last_position" value="{{ Auth::user()->last_position->id ?? '' }}">
                <input type="hidden" class="form-control" name="user_salary_id" id="user_salary_id" value="{{ Auth::user()->lastSalary ? Auth::user()->lastSalary->id : '' }}">
            </div>
            
            @canAccess('created_for','letter_submissions')
            <div class="col-md-12 mb-3">
                <label for="user">Ditujukan Untuk</label>
                <select class="form-control select2" name="user_id" id="user_id">
                    <option value="" 
                            selected
                            data-name="{{ Auth::user()->name }}"
                            data-id-card="{{ Auth::user()->id_card ?? '' }}"
                            data-address="{{ Auth::user()->address ?? '' }}"
                            data-last-position-id="{{ Auth::user()->last_position->position_id ?? '' }}"
                            data-last-position-name="{{ Auth::user()->last_position_now ? Auth::user()->last_position_now->position->name : '' }}"
                            data-first-position-start="{{ Auth::user()->first_position ? Auth::user()->first_position->start_date : '' }}"
                            data-last-position-end="{{ Auth::user()->last_position->end_date ?? '' }}"
                            data-last-salary="{{ Auth::user()->lastSalary ? Auth::user()->lastSalary->salary : 0 }}"
                            data-last-salary-formatted="{{ Auth::user()->lastSalary ? 'Rp. '.number_format(Auth::user()->lastSalary->salary,0,',','.') : 'Rp. 0' }}"
                            data-last-salary-id="{{ Auth::user()->lastSalary ? Auth::user()->lastSalary->id : 0 }}"
                            data-id-card-image="{{ Auth::user()->photo_identity ?? '' }}"
                            data-last-position-full-id="{{ Auth::user()->last_position->id ?? '' }}">
                        Pilih User (Kosongkan untuk diri sendiri)
                    </option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" 
                                {{ @$letterSubmission->user_id == $user->id ? 'selected' : '' }}
                                data-name="{{ $user->name }}"
                                data-id-card="{{ $user->id_card ?? '' }}"
                                data-address="{{ $user->address ?? '' }}"
                                data-last-position-id="{{ $user->last_position->position_id ?? '' }}"
                                data-last-position-name="{{ $user->last_position_now ? $user->last_position_now->position->name : '' }}"
                                data-first-position-start="{{ $user->first_position ? $user->first_position->start_date : '' }}"
                                data-last-position-end="{{ $user->last_position->end_date ?? '' }}"
                                data-last-salary="{{ $user->lastSalary ? $user->lastSalary->salary : 0 }}"
                                data-last-salary-formatted="{{ $user->lastSalary ? 'Rp. '.number_format($user->lastSalary->salary,0,',','.') : 'Rp. 0' }}"
                                data-last-salary-id="{{ $user->lastSalary ? $user->lastSalary->id : 0 }}"
                                data-id-card-image="{{ $user->photo_identity ?? '' }}"
                                data-last-position-full-id="{{ $user->last_position->id ?? '' }}">
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endcanAccess
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
                    <img src="{{ s3_asset(true,10,Auth::user()->id_card_image) }}" alt="Tanda Tangan" class="img-fluid" style="max-width: 200px; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                </div>
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
    // Global variable untuk menyimpan data user saat ini
    let currentUserData = {
        name: "{{ Auth::user()->name }}",
        id_card: "{{ Auth::user()->id_card ?? '' }}",
        address: "{{ Auth::user()->address ?? '' }}",
        last_position_id: "{{ Auth::user()->last_position->position_id ?? '' }}",
        last_position_name: "{{ Auth::user()->last_position_now ? Auth::user()->last_position_now->position->name : '' }}",
        first_position_start_date: "{{ Auth::user()->first_position ? Auth::user()->first_position->start_date : '' }}",
        last_position_end_date: "{{ Auth::user()->last_position->end_date ?? '' }}",
        last_salary: "{{ Auth::user()->lastSalary ? Auth::user()->lastSalary->salary : 0 }}",
        last_salary_formatted: "{{ Auth::user()->lastSalary ? 'Rp. '.number_format(Auth::user()->lastSalary->salary,0,',','.') : 'Rp. 0' }}",
        last_salary_id: "{{ Auth::user()->lastSalary ? Auth::user()->lastSalary->id : 0 }}",
        id_card_image: "{{ Auth::user()->id_card_image ?? '' }}",
        last_position_full_id: "{{ Auth::user()->last_position->id ?? '' }}"
    };

    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: 'Pilih',
            allowClear: true
        });

        // Setup Signature Pad
        var canvas = document.getElementById('signature-pad');
        var signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)',
        });

        // Clear the signature
        $('#clear-signature').click(function() {
            signaturePad.clear();
        });

        // Handle form submission
        $('form').on('submit', function(e) {
             let selectedUserId = $('#user_id').val();
    
            // Hanya validasi signature jika tidak ada user_id
            if (!selectedUserId || selectedUserId === '') {
                if (signaturePad.isEmpty()) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Tanda Tangan Diperlukan',
                        text: 'Harap tanda tangan sebelum mengajukan surat!',
                    });
                    return false;
                } else {
                    var signatureDataUrl = signaturePad.toDataURL();
                    $('#signature_image').val(signatureDataUrl);
                }
            }
        });

        // ===== USER SELECTION HANDLER (USING DATA ATTRIBUTES) =====
        $('#user_id').on('change', function() {
            let selectedOption = $(this).find('option:selected');
            let selectedUserId = $(this).val();

            if (selectedUserId && selectedUserId !== '') {
                // Jika user dipilih, sembunyikan signature
                $('.signature-container').parent().hide();
                $('#clear-signature').hide();
                
                // Clear signature if exists
                if (typeof signaturePad !== 'undefined') {
                    signaturePad.clear();
                }
            } else {
                // Jika tidak ada user dipilih, tampilkan signature
                $('.signature-container').parent().show();
                $('#clear-signature').show();
            }
            
            // Ambil data dari attributes
            currentUserData = {
                name: selectedOption.data('name') || '',
                id_card: selectedOption.data('id-card') || '',
                address: selectedOption.data('address') || '',
                last_position_id: selectedOption.data('last-position-id') || '',
                last_position_name: selectedOption.data('last-position-name') || '',
                first_position_start_date: selectedOption.data('first-position-start') || '',
                last_position_end_date: selectedOption.data('last-position-end') || '',
                last_salary: selectedOption.data('last-salary') || 0,
                last_salary_formatted: selectedOption.data('last-salary-formatted') || 'Rp. 0',
                last_salary_id: selectedOption.data('last-salary-id') || 0,
                id_card_image: selectedOption.data('id-card-image') || '',
                last_position_full_id: selectedOption.data('last-position-full-id') || ''
            };
            
            // Update hidden inputs
            updateHiddenInputs();
            
            // Update KTP preview
            updateKTPPreview();
            
            // Regenerate template if already selected
            let selectedTemplate = $('#letter_type_id').val();
            if (selectedTemplate) {
                $('#letter_type_id').trigger('change');
            }
        });

        // Function to update hidden inputs
        function updateHiddenInputs() {
            $('#user_last_position').val(currentUserData.last_position_full_id);
            $('#user_salary_id').val(currentUserData.last_salary_id);
        }

        // Function to update KTP preview
        function updateKTPPreview() {
            let ktpContainer = $('#ktp_preview_container');
            
            if (currentUserData.id_card_image && currentUserData.id_card_image !== '') {
                console.log(currentUserData.id_card_image);
                
                ktpContainer.html(`
                    <div class="mt-1 mb-2">
                        <img src="${currentUserData.id_card_image}" 
                             alt="KTP" 
                             class="img-fluid ktp-preview" 
                             style="max-width: 200px; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                    </div>
                `);
            } else {
                ktpContainer.html(`
                    <input type="file" name="id_card_image" id="id_card_image" class="form-control" accept="image/*" required>
                `);
            }
        }

        // ===== LETTER TYPE SELECTION HANDLER =====
        $('#letter_type_id').on('change', function() {
            var selectedTemplate = $(this).find('option:selected').data('template');
            
            let form = ``;
            $("#card_form_template").show();
            $("#form_template").empty();
            
            switch (selectedTemplate) {
                case 'sk_magang_template':
                    form = generateSKMagangTemplate();
                    break;
                case 'perjanjian_kerja_template':
                    form = generatePerjanjianKerjaTemplate();
                    break;
                case 'sk_management_template':
                    form = generateSKManagementTemplate();
                    break;
                case 'sk_tugas_template':
                    form = generateSKTugasTemplate();
                    break;
                case 'sk_bekerja_resign_template':
                    form = generateSKResignTemplate();
                    break;
                case 'sk_pengantar_kerja_template':
                    form = generateSKPengantarTemplate();
                    break;
                case 'sk_kuasa_template':
                    form = generateSKKuasaTemplate();
                    break;
                case 'sk_lembur_template':
                    form = generateSKLemburTemplate();
                    break;
                case 'sk_phk_template':
                    form = generateSKPHKTemplate();
                    break;
                case 'sk_perjanjian':
                    $("#card_form_template").hide();
                    return;
                case 'sk_peringatan_template':
                    form = generateSKPeringatanTemplate();
                    break;
                default:
                    return;
            }
            
            $("#form_template").html(form);
            initializeFormComponents(selectedTemplate);
        });

        // Helper function to initialize form components after template generation
        function initializeFormComponents(template) {
            // Initialize Select2 for dynamic selects
            $('.selectOrCreate2').select2({
                placeholder: 'Pilih',
                allowClear: true
            });

            // Initialize rich text editor based on template
            if (['sk_magang_template', 'perjanjian_kerja_template', 'sk_management_template', 
                 'sk_tugas_template', 'sk_kuasa_template','sk_peringatan_template'].includes(template)) {
                let editorField = template === 'sk_magang_template' ? 'description_task' : 
                                template === 'sk_kuasa_template' ? 'description' : 'job_responsibilities';
                                template === 'sk_peringatan_template' ? 'description' : 'job_mistake';
                // console.log(editorField, template);
                
                generateThriveEditor(editorField);
            }

            // Special initialization for lembur template
            if (template === 'sk_lembur_template') {
                addFormValidation();
            }
        }

        // ===== TEMPLATE GENERATOR FUNCTIONS =====
        function generateSKMagangTemplate() {
            return `
            <div class="letter-template">
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-center"><strong>PERJANJIAN MAGANG</strong></h5>
                        <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
        
                        <p>Pada Hari {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }} bertempat di Jakarta, telah ditanda tangani perjanjian magang sama antara:</p>
        
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
                                    <td>: ${currentUserData.name}</td>
                                </tr>
                                <tr>
                                    <td><strong>No KTP</strong></td>
                                    <td>: ${currentUserData.id_card}</td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat</strong></td>
                                    <td>: ${currentUserData.address}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-center">
                            <h2>Surat Keterangan Magang</h2>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Nama Lengkap</label>
                            <input type="text" class="form-control" value="${currentUserData.name}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="jabatan">Jabatan <span class="text-danger">*</span></label>
                            <select class="form-control selectOrCreate2" name="position_new_id" id="position_id" required>
                                <option value="" selected disabled>Pilih </option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}">{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="monthly_salary">Kompensasi Magang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="amount_show" placeholder="Rp 30.000.000" oninput="formatRupiahFormat(this,'amount')" required/>
                            <input type="hidden" id="amount" name="salary" value="{{ old('salary') }}">
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
                            <label for="work_location">Penempatan</label>
                            <input type="text" name="work_location" class="form-control" value="{{ old('work_location') ?? '' }}" placeholder="Masukkan penempatan kerja">
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
            `;
        }

        function generatePerjanjianKerjaTemplate() {
            return `
            <div class="letter-template" id="perjanjian_kerja_template">
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-center"><strong>PERJANJIAN KERJA</strong></h5>
                        <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
        
                        <p>Pada Hari {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }} bertempat di Jakarta, telah ditanda tangani perjanjian kerja sama antara:</p>
        
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
                                    <td>: ${currentUserData.name}</td>
                                </tr>
                                <tr>
                                    <td><strong>No KTP</strong></td>
                                    <td>: ${currentUserData.id_card}</td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat</strong></td>
                                    <td>: ${currentUserData.address}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="col-md-12 mb-3">
                            <h5 class="mb-2 text-center"><strong>SURAT KEPUTUSAN MANAGEMENT</strong></h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Nama Lengkap</label>
                            <input type="text" class="form-control" value="${currentUserData.name}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Tanggal Bergabung di Perusahaan <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" placeholder="Masukkan tanggal perhitungan gaji" value="{{ old('start_date') }}" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="jabatan">Jabatan <span class="text-danger">*</span></label>
                            <select class="form-control selectOrCreate2" name="position_new_id" id="position_id" required>
                                <option value="" selected disabled>Pilih </option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}">{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="monthly_salary">Gaji Bulanan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="amount_show" placeholder="Rp 30.000.000" oninput="formatRupiahFormat(this,'amount')" required/>
                            <input type="hidden" id="amount" name="salary" value="{{ old('salary') }}">
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
                            <input type="text" name="work_location" class="form-control" placeholder="Masukkan penempatan kerja" value="{{ old('work_location') }}" required>
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="job_responsibilities">Tanggung Jawab Pekerjaan <span class="text-danger">*</span></label>
                            <input class="thriveEditor form-control" id="description_job_responsibilities" data-ids="job_responsibilities" name="job_responsibilities" required />
                        </div>
                    </div>
                </div>
            </div>
            `;
        }

        function generateSKManagementTemplate() {
            return `
                <div class="letter-template">
                    <div class="card">
                        <div class="card-body">
                            <div class="col-md-12 mb-3">
                                <label for="company_name">Perihal <span class="text-danger">*</span></label>
                                <input type="text" name="perihal" value="{{ $fieldData['perihal'] ?? '' }}" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="working_hours">Jam Kerja <span class="text-danger">*</span></label>
                                <input type="text" name="working_hours" class="form-control" placeholder="Masukkan Jam Kerja" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="salary_date">Tanggal Perhitungan Gaji</label>
                                <input type="text" name="salary_date" class="form-control" value="{{ $fieldData['salary_date'] ?? '' }}" placeholder="Masukkan tanggal perhitungan gaji">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="work_location">Penempatan <span class="text-danger">*</span></label>
                                <input type="text" name="work_location" class="form-control" placeholder="Masukkan Penempatan Kerja" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="job_responsibilities">Tanggung Jawab Pekerjaan <span class="text-danger">*</span></label>
                                <input class="thriveEditor form-control" id="description_job_responsibilities" data-ids="job_responsibilities" name="job_responsibilities" required />
                            </div>
                        </div>
                    </div>
                </div>  
            `;
        }

        function generateSKTugasTemplate() {
            return `
                <div class="letter-template card">
                    <div class="card-body">
                        <div class="col-md-12 mb-3">
                            <label for="company_name">Perihal <span class="text-danger">*</span></label>
                            <input type="text" name="perihal" value="{{ old('perihal') ?? '' }}" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="company_name">Nama PT <span class="text-danger">*</span></label>
                            <input type="text" value="{{ $company['name'] ?? '' }}" class="form-control" readonly>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="full_name">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" value="${currentUserData.name}" placeholder="Masukkan Nama Lengkap" readonly>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="jabatan">Jabatan Terakhir <span class="text-danger">*</span></label>
                            <select class="form-control" name="position_old_id" id="position_old_id" required>
                                <option value="" selected disabled>Pilih </option>
                                @foreach($lastPositon as $positionlast)
                                    <option value="{{ $positionlast->id }}" ${currentUserData.last_position_id == '{{ $positionlast->id }}' ? 'selected' : ''}>{{ $positionlast->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="jabatan">Jabatan Terbaru<span class="text-danger">*</span></label>
                            <select class="form-control selectOrCreate2" name="position_new_id" id="position_id" required>
                                <option value="" selected disabled>Pilih </option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}">{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="monthly_salary">Gaji Bulanan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="amount_show" placeholder="Rp 30.000.000" oninput="formatRupiahFormat(this,'amount')" required/>
                            <input type="hidden" id="amount" name="salary" value="{{ old('salary') }}">
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Tanggal Perhitungan Gaji <span class="text-danger">*</span></label>
                            <input type="text" name="salary_date" class="form-control" placeholder="Masukkan tanggal perhitungan gaji" value="{{ old('salary_date') }}" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="working_hours">Jam Kerja <span class="text-danger">*</span></label>
                            <input type="text" name="working_hours" class="form-control" placeholder="Masukkan Jam Kerja" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="work_location">Penempatan <span class="text-danger">*</span></label>
                            <input type="text" name="work_location" class="form-control" placeholder="Masukkan Penempatan Kerja" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="job_responsibilities">Tanggung Jawab Pekerjaan <span class="text-danger">*</span></label>
                            <input class="thriveEditor form-control" id="description_job_responsibilities" data-ids="job_responsibilities" name="job_responsibilities" required />
                        </div>
                    </div>
                </div>
            `;
        }

        function generateSKResignTemplate() {
            return `
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
                                        <td>: ${currentUserData.name}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>NIK</strong></td>
                                        <td>: ${currentUserData.id_card}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jabatan</strong></td>
                                        <td>: ${currentUserData.last_position_name}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Perusahaan</strong></td>
                                        <td>: {{ $company['address'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                
                        <p>Menyatakan dengan sesungguhnya bahwa mulai tanggal {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }} saya mengajukan permohonan untuk mengundurkan diri sebagai karyawan {{ $company['name'] ?? "" }}</p>
        
                        <p>
                            Demikianlah surat pemutusan hubungan kerja ini juga dibuat agar kedua pihak sepakat untuk membebaskan pihak lain dari segala bentuk tuntutan hukum di kemudian hari terkecuali tindakan pidana. Semoga Saudara dapat memaklumi dan mendapat pekerjaan pengganti yang sesuai, terima kasih
                        </p>
                    </div>
                </div>
                <div class="card" id="printThis">
                    <div class="card-body">
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Nama Lengkap</label>
                            <input type="text" class="form-control" value="${currentUserData.name}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Alamat</label>
                            <input type="text" class="form-control" value="${currentUserData.address}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">NIK</label>
                            <input type="text" class="form-control" value="${currentUserData.id_card}" readonly>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="jabatan">Jabatan Terakhir <span class="text-danger">*</span></label>
                            <select class="form-control" name="position_old_id" id="position_old_id" required>
                                <option value="" selected disabled>Pilih </option>
                                @foreach($lastPositon as $positionlast)
                                    <option value="{{ $positionlast->id }}" ${currentUserData.last_position_id == '{{ $positionlast->id }}' ? 'selected' : ''}>{{ $positionlast->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Tanggal Terakhir Bekerja <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" min="{{ $twoMonthsLater }}" class="form-control" value="${currentUserData.last_position_end_date}" required>
                        </div>
                    </div>
                </div>
            `;
        }

        function generateSKPengantarTemplate() {
            return `
            <div class="letter-template">
                <div class="card">
                    <div class="card-body">
                        <div class="col-12 justify-content-center align-items-center">
                            <h4 class="text-center"><strong>SURAT KETERANGAN KERJA</strong></h4>
                            <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
                        </div>

                        <div class="col-12 justify-content-center align-items-center mt-4 mb-4">
                            <p>Saya yang bertandatangan di bawah ini :</p>
                        </div>

                        <div class="col-12 mt-2">
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
                                        <td>: ${currentUserData.name}</td>
                                    </tr>
                                    <tr>
                                        <td>No KTP</td>
                                        <td>: ${currentUserData.id_card}</td>
                                    </tr>
                                    <tr>
                                        <td>Alamat</td>
                                        <td>: ${currentUserData.address}</td>
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
                                Telah bekerja di perusahaan kami, {{ $company['name'] ?? "" }}, sejak tgl ${currentUserData.first_position_start_date ? new Date(currentUserData.first_position_start_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : ''} 
                                s/d {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }} dengan posisi sebagai ${currentUserData.last_position_name}. Selama bekerja di perusahaan kami, yang bersangkutan telah bekerja dengan baik sesuai SOP perusahaan dan tidak pernah terlibat dalam tindakan yang dapat merugikan perusahaan.
                            </p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Nama Lengkap</label>
                            <input type="text" class="form-control" value="${currentUserData.name}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Alamat</label>
                            <input type="text" class="form-control" value="${currentUserData.address}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">NIK</label>
                            <input type="text" class="form-control" value="${currentUserData.id_card}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="jabatan">Jabatan Terakhir <span class="text-danger">*</span></label>
                            <select class="form-control" name="position_old_id" id="position_old_id" required>
                                <option value="" selected disabled>Pilih </option>
                                @foreach($lastPositon as $positionlast)
                                    <option value="{{ $positionlast->id }}" ${currentUserData.last_position_id == '{{ $positionlast->id }}' ? 'selected' : ''}>{{ $positionlast->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Tanggal Mulai Kerja</label>
                            <input type="date" name="start_date" class="form-control" value="${currentUserData.first_position_start_date}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Tanggal Terakhir Kerja</label>
                            <input type="date" name="end_date" class="form-control" value="${currentUserData.last_position_end_date}">
                            <span class="text-danger">Kosongkan jika saat ini masih bekerja</span>
                        </div>
                    </div>
                </div>
            </div>
            `;
        }

        function generateSKKuasaTemplate() {
            return `
            <div class="letter-template">
                <div class="card">
                    <div class="card-body">
                        <div class="col-12 justify-content-center align-items-center">
                            <h4 class="text-center"><strong>SURAT KUASA</strong></h4>
                            <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
                        </div>

                        <div class="col-12 justify-content-center align-items-center mt-4 mb-4">
                            <p>Saya yang bertandatangan di bawah ini :</p>
                        </div>

                        <div class="col-12 mt-2">
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
                                            Dengan ini memberikan kuasa penuh kepada :
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Nama</td>
                                        <td>: ${currentUserData.name}</td>
                                    </tr>
                                    <tr>
                                        <td>No KTP</td>
                                        <td>: ${currentUserData.id_card}</td>
                                    </tr>
                                    <tr>
                                        <td>Alamat</td>
                                        <td>: ${currentUserData.address}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            Selanjutnya disebut PENERIMA KUASA
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-12">
                            <p class="text-justify"> 
                                Penerima kuasa mewakili pemberi kuasa untuk :
                            </p>
                            <ol>
                                <li>[Tanggung jawab Penerima Kuasa]</li>
                                <li></li>
                                <li></li>
                            </ol>
                        </div>
                        <div class="col-12">
                            <p class="text-justify"> 
                                Demikian Surat kuasa ini dibuat dengan sebenarnya, untuk dapat dipergunakan sebagaimana mestinya
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="col-md-12 mb-3">
                            <h5 class="mb-2 text-center"><strong>SURAT KUASA</strong></h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Tanggal Surat Kuasa</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="salary_date">File Dokumen yang dikuasakan</label>
                            <input type="file" name="file" class="form-control" accept=".pdf" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Nama Lengkap</label>
                            <input type="text" class="form-control" value="${currentUserData.name}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">NIK</label>
                            <input type="text" class="form-control" value="${currentUserData.id_card}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="jabatan">Jabatan Terakhir <span class="text-danger">*</span></label>
                            <select class="form-control" name="position_old_id" id="position_old_id" readonly>
                                <option value="" selected disabled>Pilih </option>
                                @foreach($lastPositon as $positionlast)
                                    <option value="{{ $positionlast->id }}" ${currentUserData.last_position_id == '{{ $positionlast->id }}' ? 'selected' : ''}>{{ $positionlast->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" class="form-control" name="user_last_position" value="${currentUserData.last_position_full_id}" readonly>
                        <div class="col-md-12 mb-3">
                            <label for="job_responsibilities">Tanggung Jawab Pekerjaan <span class="text-danger">*</span></label>
                            <input class="thriveEditor form-control" id="description_description" data-ids="description" name="description" required />
                        </div>
                    </div>
                </div>
            </div>
            `;
        }

        function generateSKLemburTemplate() {
            return `
                <div class="letter-template">
                    <div class="card">
                        <div class="card-body">
                            <div class="col-md-12 mb-3">
                                <label for="tanggal_lembur">Tanggal Lembur <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="date" name="tanggal_lembur_start" id="tanggal_lembur_start"
                                            value="{{ old('tanggal_lembur_start') ?? '' }}"
                                            class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="date" name="tanggal_lembur_end" id="tanggal_lembur_end"
                                            value="{{ old('tanggal_lembur_end') ?? '' }}"
                                            class="form-control"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label>Jam Lembur <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="time" name="jam_lembur_start" 
                                            value="{{ old('jam_lembur_start') ?? '' }}"
                                            class="form-control"
                                            placeholder="Mulai"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="time" name="jam_lembur_end" 
                                            value="{{ old('jam_lembur_end') ?? '' }}"
                                            class="form-control"
                                            placeholder="Selesai"
                                            required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function generateSKPHKTemplate() {
            return `
                <div class="card scrollable-div" id="printThis">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h3><strong>SURAT PEMUTUSAN HUBUNGAN KERJA</strong></h3>
                            <p><strong>hal: Surat Pemutusan Hubungan Kerja</strong></p>
                        </div>
                        
                        <p>Kepada Yth,<br></p>
                
                        <p>Saya yang bertanda tangan di bawah ini :</p>
                
                        <p>Menyatakan dengan sesungguhnya bahwa mulai tanggal {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }} saya mengajukan permohonan untuk mengundurkan diri sebagai karyawan {{ $company['name'] ?? "" }}</p>
                
                        <p>Ucapan terima kasih yang sebesar-besarnya saya sampaikan atas kesempatan yang diberikan untuk bekerja di {{ $company['name'] ?? "" }}</p>
                
                        <p>Melalui surat ini saya memohon maaf kepada segenap manajemen dan karyawan {{ $company['name'] ?? "" }} jika terdapat kesalahan yang saya perbuat selama bekerja. Besar harapan saya {{ $company['name'] ?? "" }} akan terus berkembang dan maju.</p>
                    </div>
                </div>
                <div class="card" id="printThis">
                    <div class="card-body">
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Nama Lengkap</label>
                            <input type="text" class="form-control" value="${currentUserData.name}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Alamat</label>
                            <input type="text" class="form-control" value="${currentUserData.address}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">NIK</label>
                            <input type="text" class="form-control" value="${currentUserData.id_card}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="jabatan">Jabatan Terakhir <span class="text-danger">*</span></label>
                            <select class="form-control" name="position_old_id" id="position_old_id" required>
                                <option value="" selected disabled>Pilih </option>
                                @foreach($lastPositon as $positionlast)
                                    <option value="{{ $positionlast->id }}" ${currentUserData.last_position_id == '{{ $positionlast->id }}' ? 'selected' : ''}>{{ $positionlast->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Alasan</label>
                            <input type="text" class="form-control" name="custom_reason" required>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Tanggal Terakhir Bekerja <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" min="{{ $twoMonthsLater }}" class="form-control" value="${currentUserData.last_position_end_date}" required>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateSKPeringatanTemplate() {
            return `
                <div class="letter-template">
                    <!-- Preview Surat Peringatan -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-eye"></i> Preview Surat Peringatan</h5>
                        </div>
                        <div class="card-body" id="preview_sp">
                            <div class="text-center mb-4">
                                <h4><strong>SURAT PERINGATAN</strong></h4>
                            </div>
                            
                            <p>Kepada Yth,<br>
                            Sdr/i. <span id="preview_nama_user">${currentUserData.name}</span><br>
                            Di tempat</p>
                            
                            <p>Dengan hormat,</p>
                            
                            <p>Surat peringatan ini kami terbitkan karena Saudara/i <span id="preview_nama_user_2">${currentUserData.name}</span> telah melakukan kelalaian dalam menjalankan tanggung jawab sebagai <span id="preview_jabatan">${currentUserData.last_position_name}</span> di {{ $company['name'] ?? "" }}.</p>
                            
                            <p><strong>Adapun kelalaian yang dimaksud antara lain:</strong></p>
                            <div id="preview_kelalaian" style="min-height: 50px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                                <em class="text-muted">[Kelalaian akan muncul di sini]</em>
                            </div>
                            
                            <p class="mt-3">Hal-hal tersebut berdampak pada hasil kerja tim secara keseluruhan dan tidak sejalan dengan standar kerja yang telah disepakati bersama di perusahaan.</p>
                            
                            <p>Oleh karena itu, perusahaan memberikan <strong><span id="preview_jenis_sp">Surat Peringatan Satu (SP1)</span></strong> kepada Saudara/i sebagai bentuk pembinaan agar dapat meningkatkan kedisiplinan dan kualitas kerja ke depannya.</p>
                            
                            <p>Demikian surat peringatan ini kami buat agar dapat dijadikan perhatian serius oleh yang bersangkutan. Apabila dalam waktu ke depan tidak ada perbaikan, maka perusahaan berhak mengambil tindakan lanjutan sesuai dengan ketentuan yang berlaku.</p>
                            
                            <div class="mt-5">
                                <p>Jakarta, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Input Surat Peringatan -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-edit"></i> Form Surat Peringatan</h5>
                        </div>
                        <div class="card-body">
                            <!-- 1. Nama Lengkap (dari select user) -->
                            <div class="col-md-12 mb-3">
                                <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sp_nama_lengkap" value="${currentUserData.name}" readonly>
                                <small class="text-muted">Nama diambil dari user yang dipilih</small>
                            </div>

                            <!-- 2. Jenis Surat Peringatan -->
                            <div class="col-md-12 mb-3">
                                <label for="jenis_sp">Jenis Surat Peringatan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="type_sp" id="jenis_sp" value="Surat Peringatan 1 (SP1)" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="jenis_sp">Bagian Dari <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="part_of" id="part_of" placeholder="Tim Desain Grafis - Divisi Marketing" required>
                            </div>

                            <!-- 3. Kelalaian/Pelanggaran -->
                            <div class="col-md-12 mb-3">
                                <label for="kelalaian">Adapun kelalaian yang dimaksud antara lain: <span class="text-danger">*</span></label>
                                <small class="text-muted">Tuliskan kelalaian/pelanggaran yang dilakukan. Gunakan tanda bintik (*) atau angka untuk membuat list.</small>
                                <input class="thriveEditor form-control" id="description_job_responsibilities" data-ids="job_responsibilities" name="job_mistake" required />
                            </div>

                            <!-- Hidden inputs -->
                            <input type="hidden" name="position_old_id" value="${currentUserData.last_position_full_id}">
                            <input type="hidden" name="tanggal_sp" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>
            `;
        }

        function updatePreviewSP() 
        {
                const jenisSP = document.getElementById('jenis_sp').value;
                const previewSP = document.getElementById('preview_jenis_sp');
                const previewNoSurat = document.getElementById('preview_no_surat');
                
                const today = new Date();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const year = today.getFullYear();
                const day = String(today.getDate()).padStart(2, '0');
                
                let jenisSPText = '';
                let noSurat = '';
                
                switch(jenisSP) {
                    case 'SP1':
                        jenisSPText = 'Surat Peringatan Satu (SP1)';
                        noSurat = '001/HR/SP1/' + day + '/' + month + '/' + year;
                        break;
                    case 'SP2':
                        jenisSPText = 'Surat Peringatan Dua (SP2)';
                        noSurat = '001/HR/SP2/' + day + '/' + month + '/' + year;
                        break;
                    case 'SP3':
                        jenisSPText = 'Surat Peringatan Tiga (SP3)';
                        noSurat = '001/HR/SP3/' + day + '/' + month + '/' + year;
                        break;
                    default:
                        jenisSPText = 'Surat Peringatan ...';
                        noSurat = '001/HR/SP/...';
                }
                
                previewSP.textContent = jenisSPText;
                previewNoSurat.textContent = noSurat;
        }
    });

    // Function formatRupiahFormat
    function formatRupiahFormat(input, inputNonFormat) {
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
            rupiah = rupiah.replace(/^0+/, '');
            input.value = 'Rp ' + rupiah;
        }

        document.getElementById(inputNonFormat).value = parseInt(numStr);
    }

    // Function addFormValidation untuk lembur
    function addFormValidation() {
        const tanggalStart = document.getElementById('tanggal_lembur_start');
        const tanggalEnd = document.getElementById('tanggal_lembur_end');
        const jamStart = document.getElementsByName('jam_lembur_start')[0];
        const jamEnd = document.getElementsByName('jam_lembur_end')[0];

        tanggalStart.addEventListener('change', validateDates);
        tanggalEnd.addEventListener('change', validateDates);
        jamStart.addEventListener('focusout', validateTimes);
        jamEnd.addEventListener('focusout', validateTimes);

        function validateDates() {
            if (tanggalStart.value === tanggalEnd.value) {
                jamEnd.setAttribute('min', jamStart.value);
            } else {
                jamEnd.removeAttribute('min');
            }

            if (tanggalEnd.value === '') {
                tanggalEnd.value = tanggalStart.value;
            }
        }

        function validateTimes() {
            if (jamEnd.value && jamStart.value && tanggalStart.value === tanggalEnd.value && jamEnd.value <= jamStart.value) {
                alert('Jam selesai tidak boleh sama atau lebih awal dari jam mulai!');
                jamEnd.value = '';
            } else if (jamEnd.value && jamStart.value && tanggalStart.value !== tanggalEnd.value && jamEnd.value === jamStart.value) {
                alert('Jam selesai tidak boleh sama dengan jam mulai ketika tanggal lembur berbeda!');
                jamEnd.value = '';
            }
        }
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
    .ql-container {
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