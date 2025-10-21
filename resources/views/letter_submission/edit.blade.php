@extends('adminlte::page')

@section('content_header')
    <h2 class="text-center">Pengajuan Surat</h2>
@stop

@php 
    $template = $letterSubmission->letterType->template;
    $fieldData = $letterSubmission->convert_field;
@endphp

@section('content')
<div class="col-md-12 p-3">
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
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
<form action="{{ route('letter-submission.update',$letterSubmission) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('put')
    
    <div class="card">
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
                        <h5 class="text-center"><strong>PERJANJIAN MAGANG</strong></h5>
                        <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
        
                        <p>Pada Hari {{ \Carbon\Carbon::parse($letterSubmission->created_at)->locale('id')->translatedFormat('l, d F Y') }}  bertempat di Jakarta, telah ditanda tangani perjanjian magang sama antara:</p>
        
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
                                    <option value="{{ $position->id }}" {{ (isset($fieldData['position_new_id']) && $fieldData['position_new_id'] == $position->id) ? 'selected' : '' }} >
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
                            <label for="work_location">Penempatan</label>
                            <input type="text" name="work_location" class="form-control" value="{{ $fieldData['work_location'] ?? ''  }}" placeholder="Masukkan penempatan kerja">
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
        
                        <p>Pada Hari {{ isset($fieldData['start_date']) ? \Carbon\Carbon::parse($fieldData['start_date'])->locale('id')->translatedFormat('l, d F Y') : $dateWithDay }} bertempat di Jakarta, telah ditanda tangani perjanjian kerja sama antara:</p>
        
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
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Tanggal Bergabung di Perusahaan <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" placeholder="Masukkan tanggal perhitungan gaji" value="{{ isset($fieldData['start_date']) ? $fieldData['start_date'] : '' }}" required>
                        </div>
                        @if(isset($fieldData['position_new_id']))
                        <!-- Jabatan -->
                        <div class="col-md-12 mb-3">
                            <label for="jabatan">Jabatan</label>
                            <select class="form-control selectOrCreate2" name="position_new_id">
                                <option value="" selected disabled>Pilih </option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}" {{ (isset($fieldData['position_new_id']) && $fieldData['position_new_id'] == $position->id) ? 'selected' : '' }} >
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
            <div class="form-row letter-template card" id="sk_management_template" style="display:none;">
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
                                <option value="{{ $position->id }}" {{ (isset($userPosition) && $userPosition->position_id == $position->id) ? 'selected' : '' }} >{{ $position->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <input type="hidden" class="form-control" id="amount" name="user_last_position" value="{{ $userPosition ? $userPosition->id : '' }}" readonly>

                    <div class="col-md-12 mb-3">
                        <label for="monthly_salary">Gaji Bulanan</label>
                        <input type="text" class="form-control" id="amount" value="{{ isset($salary) ? number_format($salary->salary, 0, ',', '.') : 'Rp. 0' }}" readonly>
                        <input type="hidden" class="form-control" id="amount" name="user_salary_id" value="{{ isset($salary) ? $salary->id : 0 }}" readonly>
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
                            @foreach($lastPositon as $position)
                                <option value="{{ $position->id }}" {{ (isset($fieldData['position_old_id']) && $fieldData['position_old_id'] == $position->id) ? 'selected' : '' }} >{{ $position->name }}</option>
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
                                <option value="{{ $position->id }}" {{ (isset($fieldData['position_new_id']) && $fieldData['position_new_id'] == $position->id) ? 'selected' : '' }} >{{ $position->name }}</option>
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

            @if($template == "sk_pengantar_kerja_template")
            <!-- SK Pengantar Kerja Template -->
            <div class="form-row" id="sk_pengantar_kerja_template">
                <div class="" >
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
                                    s/d {{ \Carbon\Carbon::parse($letterSubmission->created_at)->locale('id')->translatedFormat('d F Y') }} dengan posisi sebagai {{ $lastestPosition ? $lastestPosition->name : "" }}. Selama bekerja di perusahaan kami, yang bersangkutan telah bekerja dengan baik sesuai SOP perusahaan dan tidak pernah terlibat dalam tindakan yang dapat merugikan perusahaan.
                                </p>
                            </div>
                            <!-- Jabatan Terakhir-->
                            <div class="col-md-12 mb-3">
                                <label for="salary_date">Nama Lengkap</label>
                                <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="salary_date">Alamat</label>
                                <input type="text" class="form-control" value="{{ $user->address }}" readonly>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="salary_date">NIK</label>
                                <input type="text" class="form-control" value="{{ $user->id_card }}" readonly>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="jabatan">Jabatan Terakhir <span class="text-danger">*</span></label>
                                <select class="form-control" name="position_old_id" id="position_old_id" required>
                                    <option value="" selected disabled>Pilih </option>
                                    @foreach($lastPositon as $position)
                                        <option value="{{ $position->id }}" {{ (isset($fieldData['position_old_id']) && $fieldData['position_old_id'] == $position->id) ? 'selected' : '' }} >{{ $position->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="salary_date">Tanggal Mulai Kerja</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $user->first_position ? $user->first_position->start_date : '' }}" readonly>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="salary_date">Tanggal Terakhir Kerja</label>
                                <input type="date" name="end_date" class="form-control" value="{{ (isset($fieldData['end_date']) ) ? $fieldData['end_date'] : '' }}">
                                <span class="text-danger">Kosongkan jika saat ini masih bekerja</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif


            @if($template == "sk_kuasa_template")
            <!-- sk_pengantar_kerja_template -->
            <div class="form-row letter-template card" id="sk_kuasa_template" style="display:none;">
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
                                            Dengan ini memberikan kuasa penuh kepada :
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
                                            Selanjutnya disebut PENERIMA KUASA
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-12">
                            <p class="text-justify mb-0"> 
                                Penerima kuasa mewakili pemberi kuasa untuk :
                            </p>
                            {!! $fieldData['description'] ?? ''  !!}
                        </div>
                        <div class="col-12">
                            <p class="text-justify"> 
                                Demikian Surat kuasa ini dibuat dengan sebenarnya, untuk dapat dipergunakan sebagaimana mestinya
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <!-- Bagian Surat Keputusan -->
                    <div class="card-header">
                        <div class="col-md-12 mb-3">
                            <h5 class="mb-2 text-center"><strong>SURAT KUASA</strong></h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Tanggal Surat Kuasa</label>
                            <input type="date" name="date" class="form-control" value="{{ isset($fieldData['date']) ? \Carbon\Carbon::parse($fieldData['date'])->format('Y-m-d') : '' }}"required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">File Dokumen yang dikuasakan</label>
                            @if(isset($fieldData['file']))
                                <a href="{{ Storage::url($fieldData['file']) }}" target="_blank"><i class="fa fa-download"></i> File</a>
                            @endif
                            <input type="file" name="file" class="form-control" accept=".pdf">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Nama Lengkap</label>
                            <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">NIK</label>
                            <input type="text" class="form-control" value="{{ $user->id_card }}" readonly>
                        </div>
                         <!-- Jabatan -->
                        @if(isset($user->last_position))
                        <!-- Jabatan Terakhir-->
                        <div class="col-md-12 mb-3">
                            <label for="jabatan">Jabatan Terakhir <span class="text-danger">*</span></label>
                            <select class="form-control" name="position_old_id" id="position_old_id" disabled>
                                <option value="" selected disabled>Pilih </option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}" {{ (isset($userPosition) && $userPosition->position_id == $position->id) ? 'selected' : '' }} >{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" class="form-control" id="amount" name="user_last_position" value="{{ $userPosition ? $userPosition->id : '' }}" readonly>
                        @endif
                        <div class="col-md-12 mb-3">
                            <label for="job_responsibilities">Tanggung Jawab Pekerjaan <span class="text-danger">*</span></label>
                            <input class="thriveEditor form-control" id="description_description" data-ids="description" name="description" value="{{ $fieldData['description'] ?? ''  }}" required />
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($template == "sk_bekerja_resign_template")
            <!-- SK Bekerja Resign Template -->
            <div class="form-row" id="sk_bekerja_resign_template" style="display:none;">
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
                
                        <p>Menyatakan dengan sesungguhnya bahwa mulai tanggal {{ \Carbon\Carbon::parse($letterSubmission->created_at)->locale('id')->translatedFormat('d F Y') }} saya mengajukan permohonan untuk mengundurkan diri sebagai karyawan  {{ $company['name'] ?? "" }}</p>
                
                        <p>Ucapan terima kasih yang sebesar-besarnya saya sampaikan atas kesempatan yang diberikan untuk bekerja di {{ $company['name'] ?? "" }}</p>
                
                        <p>Melalui surat ini saya memohon maaf kepada segenap manajemen dan karyawan {{ $company['name'] ?? "" }} jika terdapat kesalahan yang saya perbuat selama bekerja. Besar harapan saya {{ $company['name'] ?? "" }} akan terus berkembang dan maju.</p>
                    </div>
                </div>
                <div class="card col-md-12" id="printThis">
                    <div class="card-body col-md-12">
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Nama Lengkap</label>
                            <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Alamat</label>
                            <input type="text" class="form-control" value="{{ $user->address }}" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">NIK</label>
                            <input type="text" class="form-control" value="{{ $user->id_card }}" readonly>
                        </div>
                        @if(isset($user->last_position))
                        <!-- Jabatan Terakhir-->
                        <div class="col-md-12 mb-3">
                            <label for="jabatan">Jabatan Terakhir <span class="text-danger">*</span></label>
                            <select class="form-control" name="position_old_id" id="position_old_id" required>
                                <option value="" selected disabled>Pilih </option>
                                @foreach($lastPositon as $position)
                                    <option value="{{ $position->id }}" {{ (isset($fieldData['position_old_id']) && $fieldData['position_old_id'] == $position->id) ? 'selected' : '' }} >{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-12 mb-3">
                            <label for="salary_date">Tanggal Terakhir Bekerja <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" min="{{ $twoMonthsLater }}"  class="form-control" value="{{ (isset($fieldData['end_date'])) ? $fieldData['end_date'] : '' }}" required>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($template == "sk_lembur_template")
            <div class="form-row" id="sk_lembur_template" style="display:none;">
                <div class="col-md-12">
                    <div class="card scrollable-div" id="printThis">
                        <div class="card-body">
                            <div class="col-md-12 mb-3">
                                <label for="no_surat">Nomor Surat <span class="text-danger">*</span></label>
                                <input type="text" 
                                    value="{{ $letterSubmission->number_result ?? '' }}"
                                    class="form-control" 
                                    disabled>
                            </div>
    
                            <!-- Tanggal Lembur -->
                            <div class="col-md-12 mb-3">
                                <label for="tanggal_lembur">Tanggal Lembur <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="date" name="tanggal_lembur_start" id="tanggal_lembur_start"
                                            value="{{ (isset($fieldData['tanggal_lembur_start'])) ? $fieldData['tanggal_lembur_start'] : '' }}"
                                            class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="date" name="tanggal_lembur_end" id="tanggal_lembur_end"
                                            value="{{ (isset($fieldData['tanggal_lembur_end'])) ? $fieldData['tanggal_lembur_end'] : '' }}"
                                            class="form-control"
                                            required>
                                    </div>
                                </div>
                            </div>
    
                            <!-- Jam Lembur -->
                            <div class="col-md-12 mb-3">
                                <label>Jam Lembur <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="time" name="jam_lembur_start" 
                                            value="{{ (isset($fieldData['jam_lembur_start'])) ? $fieldData['jam_lembur_start'] : '' }}"
                                            class="form-control"
                                            placeholder="Mulai"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="time" name="jam_lembur_end" 
                                            value="{{ (isset($fieldData['jam_lembur_end'])) ? $fieldData['jam_lembur_end'] : '' }}"
                                            class="form-control"
                                            placeholder="Selesai"
                                            required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($template == "sk_phk_template")
            <div class="form-row" id="sk_pengantar_kerja_template">
                <div class="" >
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
                                    s/d {{ \Carbon\Carbon::parse($letterSubmission->created_at)->locale('id')->translatedFormat('d F Y') }} dengan posisi sebagai {{ $lastestPosition ? $lastestPosition->name : "" }}. Selama bekerja di perusahaan kami, yang bersangkutan telah bekerja dengan baik sesuai SOP perusahaan dan tidak pernah terlibat dalam tindakan yang dapat merugikan perusahaan.
                                </p>
                            </div>
                            <!-- Jabatan Terakhir-->
                            <div class="col-md-12 mb-3">
                                <label for="salary_date">Nama Lengkap</label>
                                <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="salary_date">Alamat</label>
                                <input type="text" class="form-control" value="{{ $user->address }}" readonly>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="salary_date">NIK</label>
                                <input type="text" class="form-control" value="{{ $user->id_card }}" readonly>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="jabatan">Jabatan Terakhir <span class="text-danger">*</span></label>
                                <select class="form-control" name="position_old_id" id="position_old_id" required>
                                    <option value="" selected disabled>Pilih </option>
                                    @foreach($lastPositon as $position)
                                        <option value="{{ $position->id }}" {{ (isset($fieldData['position_old_id']) && $fieldData['position_old_id'] == $position->id) ? 'selected' : '' }} >{{ $position->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="salary_date">Tanggal Mulai Kerja</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $user->first_position ? $user->first_position->start_date : '' }}" readonly>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="salary_date">Tanggal Terakhir Kerja</label>
                                <input type="date" name="end_date" class="form-control" value="{{ (isset($fieldData['end_date']) ) ? $fieldData['end_date'] : '' }}">
                                <span class="text-danger">Kosongkan jika saat ini masih bekerja</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

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
                     {{--
                     @if (($fieldData['signature_image'] && $letterSubmission->status !== 0) || $letterSubmission->is_approve !== 0)
                         <div class="signature-container mt-2">
                             <img src="{{ Storage::url($fieldData['signature_image']) }}" alt="Tanda Tangan" class="img-fluid">
                         </div>
                     @else
                        <div class="col-md-6 mb-3">
                            <div class="signature-container">
                                <canvas id="signature-pad" class="signature-pad" width=400 height=200></canvas>
                            </div>
                            <button type="button" id="clear-signature" class="btn btn-warning mt-2">Hapus Tanda Tangan</button>
                            <input type="hidden" name="signature_image" id="signature_image">
                        </div>
                     @endif
                     --}}
                     @if($letterSubmission->is_approved === null)
                        @if(isset($letterSubmission->status) && $letterSubmission->status == 0)
                            <!-- Display signature form when status is "Need Signature" -->
                            <div class="col-md-6 mb-3">
                                <div class="signature-container">
                                    <canvas id="signature-pad" class="signature-pad" width=400 height=200></canvas>
                                </div>
                                <button type="button" id="clear-signature" class="btn btn-warning mt-2">Hapus Tanda Tangan</button>
                                <input type="hidden" name="signature_image" id="signature_image">
                            </div>
                        @else
                            <!-- Pending Status -->
                            <div class="signature-container mt-2">
                                <img src="{{ Storage::url($fieldData['signature_image']) }}" alt="Tanda Tangan" class="img-fluid">
                            </div>
                        @endif
                    @elseif(isset($letterSubmission->is_approved) && $letterSubmission->is_approved)
                        <!-- Display Approved and previous signature -->
                        @if(isset($fieldData['signature_image']))
                            <div class="signature-container mt-2">
                                <img src="{{ Storage::url($fieldData['signature_image']) }}" alt="Tanda Tangan" class="img-fluid">
                            </div>
                        @endif
                    @else
                        <!-- Display Rejected and show signature form again -->
                        <div class="col-md-6 mb-3">
                            <div class="signature-container">
                                <canvas id="signature-pad" class="signature-pad" width=400 height=200></canvas>
                            </div>
                            <button type="button" id="clear-signature" class="btn btn-warning mt-2">Hapus Tanda Tangan</button>
                            <input type="hidden" name="signature_image" id="signature_image">
                        </div>
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
                // tags: true
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
@if($template == "sk_lembur_template")
<script>
    $(document).ready(function () {
        addFormValidation(); 
    });
    function addFormValidation() 
    {
        const tanggalStart = document.getElementById('tanggal_lembur_start');
        const tanggalEnd = document.getElementById('tanggal_lembur_end');
        const jamStart = document.getElementsByName('jam_lembur_start')[0];
        const jamEnd = document.getElementsByName('jam_lembur_end')[0];

        // Listen for changes on tanggal_start and tanggal_end
        tanggalStart.addEventListener('change', validateDates);
        tanggalEnd.addEventListener('change', validateDates);

        jamStart.addEventListener('focusout', validateTimes);
        jamEnd.addEventListener('focusout', validateTimes);

        // Validate the dates and times
        function validateDates() {
            // If the start date is the same as the end date
            if (tanggalStart.value === tanggalEnd.value) {
                jamEnd.setAttribute('min', jamStart.value); // Set the min value for end time
            } else {
                jamEnd.removeAttribute('min'); // Remove the min restriction if dates are different
            }

            // Synchronize tanggalEnd with tanggalStart if not already set
            if (tanggalEnd.value === '') {
                tanggalEnd.value = tanggalStart.value;
            }
        }

        function validateTimes() {
            // If the dates are the same and end time is before or equal to start time
            console.log(jamEnd.value);
            console.log(jamStart.value);
            
            if (jamEnd.value && jamStart.value && tanggalStart.value === tanggalEnd.value && jamEnd.value <= jamStart.value) {
                alert('Jam selesai tidak boleh sama atau lebih awal dari jam mulai!');
                jamEnd.value = ''; // Reset the end time field
            } else if (jamEnd.value && jamStart.value && tanggalStart.value !== tanggalEnd.value && jamEnd.value === jamStart.value) {
                alert('Jam selesai tidak boleh sama dengan jam mulai ketika tanggal lembur berbeda!');
                jamEnd.value = ''; // Reset the end time field
            }
        }
    }
</script>
@endif
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