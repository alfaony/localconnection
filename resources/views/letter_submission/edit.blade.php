@extends('adminlte::page')

@section('content_header')
    <h2 class="text-center">Pengajuan Surat</h2>
@stop

@php 
    $template = $letterSubmission->letterType->template;
    $fieldData = $letterSubmission->convert_field;
@endphp

@section('content')
<form action="{{ route('letter-submission.update',$letterSubmission) }}" method="POST">
    @csrf
    @method('put')
    
    <div class="card">
        <div class="card-body">
            <!-- Formulir -->
            <div class="col-md-12 mb-3">
                <label for="surat">Surat <span class="text-danger">*</span></label>
                <select class="form-control select2" name="letter_type_id" id="letter_type_id">
                    <option value="" selected disabled>Pilih Surat</option>
                    @foreach($letterTypes as $letterType)
                        <option value="{{ $letterType->id }}" {{ @$letterSubmission->letter_type_id == $letterType->id ? 'selected' : '' }} data-template="{{ $letterType->template }}">{{ $letterType->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3>Formulir</h3>
        </div>
        <div class="card-body">
            <!-- SK Magang Template -->
            @if($template == \App\Schemas\ParamSchema::TEMPLATEMAGANG)
            <div class="letter-template" id="sk_magang_template" style="display:none;">
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
                                    <td>: {{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>No KTP</strong></td>
                                    <td>: {{ $user->id_card }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat</strong></td>
                                    <td>: {{ $user->address }}</td>
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
                            <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                        </div>
                        <!-- Jabatan -->
                        @if(isset($fieldData['position_new_id']))
                        <!-- Jabatan -->
                        <div class="col-md-12 mb-3">
                            <label for="jabatan">Jabatan</label>
                            <select class="form-control selectOrCreate2" name="position_new_id">
                                <option value="" selected disabled>Pilih </option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->name }}" {{ (isset($fieldData['position_new_id']) && $fieldData['position_new_id'] == $position->name) ? 'selected' : '' }} >
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
        
                        <div class="col-md-12 mb-3">
                            <label for="monthly_salary">Kompensasi Magang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control"  id="amount_show" placeholder="Rp 30.000.000" oninput="formatRupiahFormat(this,'amount')" required/>
                            <input type="hidden" id="amount" name="salary" name="name" value="{{ $fieldData['salary'] ?? '' }}">
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Tanggal Pembayaran Kompensasi Magang <span class="text-danger">*</span></label>
                            <input type="text" name="salary_date" class="form-control" placeholder="Masukkan tanggal perhitungan gaji" value="{{ $fieldData['salary_date'] ?? '' }}"required>
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="working_hours">Jam Kerja <span class="text-danger">*</span></label>
                            <input type="text" name="working_hours" class="form-control" placeholder="Masukkan jam kerja" value="{{ $fieldData['working_hours'] ?? '' }}" required>
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="masa_kerja">Masa Kerja <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="date" name="start_date" class="form-control" value="{{ $fieldData['start_date'] ?? '' }}" required>
                                <span class="input-group-text">hingga</span>
                                <input type="date" name="end_date" class="form-control" value="{{ $fieldData['end_date'] ?? '' }}" required>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="deskripsi_tugas">Deskripsi Tugas <span class="text-danger">*</span></label>
                            <input class="thriveEditor form-control" id="description_description_task" data-ids="description_task" name="description_task" value="{{ $fieldData['description_task'] ?? '' }}" required/>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($template == \App\Schemas\ParamSchema::TEMPLATEPERJANJIANKERJA)
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
                                    <td>: {{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>No KTP</strong></td>
                                    <td>: {{ $user->id_card }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat</strong></td>
                                    <td>: {{ $user->address }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Nama Lengkap</label>
                            <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                        </div>
                        @if(isset($fieldData['position_new_id']))
                        <!-- Jabatan -->
                        <div class="col-md-12 mb-3">
                            <label for="jabatan">Jabatan</label>
                            <select class="form-control selectOrCreate2" name="position_new_id">
                                <option value="" selected disabled>Pilih </option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->name }}" {{ (isset($fieldData['position_new_id']) && $fieldData['position_new_id'] == $position->name) ? 'selected' : '' }} >
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
        
                        <div class="col-md-12 mb-3">
                            <label for="monthly_salary">Gaji Bulanan</label>
                            <input type="text" class="form-control" id="amount_show" placeholder="Rp {{ number_format($letterSubmission->salary ?? 0, 0, ',', '.') }}" oninput="formatRupiahFormat(this,'amount')"/>
                            <input type="hidden" id="amount" name="salary" value="{{ $fieldData['salary'] ?? '' }}">
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Tanggal Perhitungan Gaji</label>
                            <input type="text" name="salary_date" class="form-control" value="{{ $fieldData['salary_date'] ?? '' }}" placeholder="Masukkan tanggal perhitungan gaji">
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="working_hours">Jam Kerja</label>
                            <input type="text" name="working_hours" class="form-control" value="{{ $fieldData['working_hours'] ?? ''  }}" placeholder="Masukkan jam kerja">
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="work_location">Penempatan</label>
                            <input type="text" name="work_location" class="form-control" value="{{ $fieldData['work_location'] ?? ''  }}" placeholder="Masukkan penempatan kerja">
                        </div>
        
                        <div class="col-md-12 mb-3">
                            <label for="job_responsibilities">Tanggung Jawab Pekerjaan</label>
                            <input class="thriveEditor form-control" id="description_job_responsibilities" data-ids="job_responsibilities" name="job_responsibilities" value="{{ $fieldData['job_responsibilities'] ?? ''  }}" />
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($template == \App\Schemas\ParamSchema::TEMPLATEJABATAN)
            <!-- SK Jabatan Template -->
            <div class="form-row letter-template card" id="sk_jabatan_template" style="display:none;">
                <div class="card-body">
                    <!-- Nama PT (Picklist) -->
                    <div class="col-md-12 mb-3">
                        <label for="company_name">Perihal <span class="text-danger">*</span></label>
                        <input type="text" name="perihal" value="{{ $fieldData['perihal'] ?? '' }}" class="form-control" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="company_name">Nama PT <span class="text-danger">*</span></label>
                        <input type="text" value="{{ $company['name'] ?? '' }}" class="form-control" readonly>
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="col-md-12 mb-3">
                        <label for="full_name">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" value="{{ $user->name }}" placeholder="Masukkan Nama Lengkap" readonly>
                    </div>

                    <!-- Jabatan -->
                    @if(isset($user->last_position))
                    <!-- Jabatan -->
                    <div class="col-md-12 mb-3">
                        <label for="jabatan">Jabatan</label>
                        <select class="form-control" name="position_id" readonly>
                            <option value="" selected disabled>Pilih </option>
                            @foreach($positions as $position)
                                <option value="{{ $position->name }}" {{ $user->last_position->position_id == $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-12 mb-3">
                        <label for="monthly_salary">Gaji Bulanan</label>
                        <input type="text" class="form-control" id="amount_show" placeholder="Rp {{ number_format($letterSubmission->salary ?? 0, 0, ',', '.') }}" oninput="formatRupiahFormat(this,'amount')"/>
                        <input type="hidden" id="amount" name="salary" value="{{ $fieldData['salary'] ?? '' }}">
                    </div>
    
                    <div class="col-md-12 mb-3">
                        <label for="salary_date">Tanggal Perhitungan Gaji</label>
                        <input type="text" name="salary_date" class="form-control" value="{{ $fieldData['salary_date'] ?? '' }}" placeholder="Masukkan tanggal perhitungan gaji">
                    </div>
    
                    <div class="col-md-12 mb-3">
                        <label for="working_hours">Jam Kerja</label>
                        <input type="text" name="working_hours" class="form-control" value="{{ $fieldData['working_hours'] ?? ''  }}" placeholder="Masukkan jam kerja">
                    </div>
    
                    <div class="col-md-12 mb-3">
                        <label for="work_location">Penempatan</label>
                        <input type="text" name="work_location" class="form-control" value="{{ $fieldData['work_location'] ?? ''  }}" placeholder="Masukkan penempatan kerja">
                    </div>
    
                    <div class="col-md-12 mb-3">
                        <label for="job_responsibilities">Tanggung Jawab Pekerjaan</label>
                        <input class="thriveEditor form-control" id="description_job_responsibilities" data-ids="job_responsibilities" name="job_responsibilities" value="{{ $fieldData['job_responsibilities'] ?? ''  }}" />
                    </div>
                </div>
            </div>
            @endif

            @if($template == \App\Schemas\ParamSchema::TEMPLATETUGAS)
            <div class="form-row letter-template card" id="sk_tugas_template" style="display:none;">
                <div class="card-body">
                    <div class="col-md-12 mb-3">
                        <label for="company_name">Perihal <span class="text-danger">*</span></label>
                        <input type="text" name="perihal" value="{{ $fieldData['perihal'] ?? '' }}" class="form-control" required>
                    </div>
                    <!-- Nama PT (Picklist) -->
                    <div class="col-md-12 mb-3">
                        <label for="company_name">Nama PT <span class="text-danger">*</span></label>
                        <input type="text" value="{{ $company['name'] ?? '' }}" class="form-control" readonly>
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="col-md-12 mb-3">
                        <label for="full_name">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" value="{{ $user->name }}" placeholder="Masukkan Nama Lengkap" readonly>
                    </div>
                    
                    @if(isset($user->last_position))
                    <!-- Jabatan Terakhir-->
                    <div class="col-md-12 mb-3">
                        <label for="jabatan">Jabatan Terakhir <span class="text-danger">*</span></label>
                        <select class="form-control" name="position_old_id" id="position_old_id" required>
                            <option value="" selected disabled>Pilih </option>
                            @foreach($lastPositon as $positionlast)
                                <option value="{{ $positionlast->name }}" {{ $positionlast->id == $user->last_position->position_id ? 'selected' : '' }}>{{ $positionlast->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Jabatan Terbaru (Picklist) -->
                    <div class="col-md-12 mb-3">
                        <label for="jabatan">Jabatan Terbaru <span class="text-danger">*</span></label>
                        <select class="form-control selectOrCreate2" name="position_new_id" id="position_id" required>
                            <option value="" selected disabled>Pilih </option>
                            @foreach($positions as $position)
                                <option value="{{ $position->name }}" {{ (isset($fieldData['position_new_id']) && $fieldData['position_new_id'] == $position->name) ? 'selected' : '' }} >{{ $position->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Gaji Bulanan -->
                    <div class="col-md-12 mb-3">
                        <label for="monthly_salary">Gaji Bulanan</label>
                        <input type="text" class="form-control" id="amount_show" placeholder="Rp {{ number_format($letterSubmission->salary ?? 0, 0, ',', '.') }}" oninput="formatRupiahFormat(this,'amount')"/>
                        <input type="hidden" id="amount" name="salary" value="{{ $fieldData['salary'] ?? '' }}">
                    </div>
    
                    <div class="col-md-12 mb-3">
                        <label for="salary_date">Tanggal Perhitungan Gaji</label>
                        <input type="text" name="salary_date" class="form-control" value="{{ $fieldData['salary_date'] ?? '' }}" placeholder="Masukkan tanggal perhitungan gaji">
                    </div>
    
                    <div class="col-md-12 mb-3">
                        <label for="working_hours">Jam Kerja</label>
                        <input type="text" name="working_hours" class="form-control" value="{{ $fieldData['working_hours'] ?? ''  }}" placeholder="Masukkan jam kerja">
                    </div>
    
                    <div class="col-md-12 mb-3">
                        <label for="work_location">Penempatan</label>
                        <input type="text" name="work_location" class="form-control" value="{{ $fieldData['work_location'] ?? ''  }}" placeholder="Masukkan penempatan kerja">
                    </div>
    
                    <div class="col-md-12 mb-3">
                        <label for="job_responsibilities">Tanggung Jawab Pekerjaan</label>
                        <input class="thriveEditor form-control" id="description_job_responsibilities" data-ids="job_responsibilities" name="job_responsibilities" value="{{ $fieldData['job_responsibilities'] ?? ''  }}" />
                    </div>
                </div>
            </div>
            @endif

            <!-- SK Pengantar Kerja Template -->
            <div class="form-row letter-template" id="sk_pengantar_kerja_template" style="display:none;">
                <div class="letter-template" >
                    <div class="card">
                        <div class="card-body">
                            <div class="col-12 justify-content-center align-items-center">
                                <h6 class="text-center"><strong>SURAT KETERANGAN</strong></h6>
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
                                            <td>: {{ $user->name }}</td>
                                        </tr>
                                        <tr>
                                            <td>No KTP</td>
                                            <td>: {{ $user->id_card }}</td>
                                        </tr>
                                        <tr>
                                            <td>Alamat</td>
                                            <td>: {{ $user->address }}</td>
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
                                    Telah bekerja di perusahaan kami, {{ $company['name'] ?? "" }}, sejak tgl {{ $user->first_position ? \Carbon\Carbon::parse($user->first_position->start_date)->locale('id')->translatedFormat('d F Y') : "" }} 
                                    s/d {{ isset($user->last_position_now) && isset($user->last_position_now->end_date) ? \Carbon\Carbon::parse($user->last_position_now->end_date)->locale('id')->translatedFormat('d F Y') : \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }} dengan posisi sebagai {{ isset($user->last_position_now) && isset($user->last_position_now->position) ? $user->last_position_now->position->name : '' }} dengan posisi sebagai {{ $user->last_position_now ? $user->last_position_now->position->name : '' }}. Selama bekerja di perusahaan kami, yang bersangkutan telah bekerja dengan baik sesuai SOP perusahaan dan tidak pernah terlibat dalam tindakan yang dapat merugikan perusahaan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SK Bekerja Resign Template -->
            <div class="form-row letter-template" id="sk_bekerja_resign_template" style="display:none;">
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
                                        <td>: {{ $user->name ?? "" }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>NIK</strong></td>
                                        <td>: {{ $user->id_card ?? "" }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jabatan</strong></td>
                                        <td>: {{ $user->last_position_now ? $user->last_position_now->position->name : "" }}</td>
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
                
                        <p>Menyatakan dengan sesungguhnya bahwa mulai tanggal  saya mengajukan permohonan untuk mengundurkan diri sebagai karyawan P{{ $company['name'] ?? "" }}</p>
                
                        <p>Ucapan terima kasih yang sebesar-besarnya saya sampaikan atas kesempatan yang diberikan untuk bekerja di {{ $company['name'] ?? "" }}</p>
                
                        <p>Melalui surat ini saya memohon maaf kepada segenap manajemen dan karyawan {{ $company['name'] ?? "" }} jika terdapat kesalahan yang saya perbuat selama bekerja. Besar harapan saya {{ $company['name'] ?? "" }} akan terus berkembang dan maju.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    <div class="card">
        <div class="card-body">
            <!-- Nomor KTP -->
             <div class="row">
                 <div class="col-md-6">
                     <label for="ktp">Nomor KTP</label>
                     @if($user->id_card_image)
                     <div class="mt-1 mb-2">
                         <img src="{{ Storage::url($user->id_card_image) }}" alt="Tanda Tangan" class="img-fluid" style="max-width: 200px; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                     </div>
                     @endif
                 </div>
     
                 <!-- Tanda Tangan -->
                 <div class="col-md-6">
                     <label for="signature">Tanda Tangan <span class="text-danger">*</span></label>
                     @if ($fieldData['signature_image'])
                         <div class="signature-container mt-2">
                             <img src="{{ Storage::url($fieldData['signature_image']) }}" alt="Tanda Tangan" class="img-fluid" style="max-width: 200px; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                         </div>
                     @else
                         <p class="text-muted">Tanda tangan belum tersedia.</p>
                     @endif
                 </div>
             </div>
        </div>
    </div>

    <div class="d-flex justify-content-end p-3">
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>

</form>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
    $(document).ready(function () {
        // Initialize Select2
        let amount = document.getElementById("amount").value;

        console.log(amount);
        
        if (amount) 
        {
            document.getElementById("amount_show").value = amount;
            formatRupiahFormat(document.getElementById("amount_show"),"amount"); // Format default value
        }
    });
</script>
<script>
    $(document).ready(function() {
        // Handle letter type selection
        var selectedTemplate = $(this).find('option:selected').data('template');
        
        if(selectedTemplate) 
        {
            $('#' + selectedTemplate).show();
        }
        
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

        $("#letter_type_id").trigger("change");
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
</style>
@endsection