@extends('adminlte::page')

@section('content_header')
    <h2 class="text-center">Pengajuan Surat</h2>
@stop

@section('content')
<div class="container">
    <form action="{{ route('letter-submission.update',$letterSubmission) }}" method="POST">
        @csrf
        @method('put')
        
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
                    <input type="text" name="id_card" class="form-control" placeholder="Masukkan Nomor KTP" value="{{ Auth::user()->id_card }}"  required>
                </div>

                <!-- Tanda Tangan -->
                    <div class="col-md-12">
                        <label for="signature">Tanda Tangan <span class="text-danger">*</span></label>
                        @if (Auth::user()->signature)
                            <div class="signature-container mt-2">
                                <img src="{{ Storage::url(Auth::user()->signature) }}" alt="Tanda Tangan" class="img-fluid" style="max-width: 200px; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                            </div>
                        @else
                            <p class="text-muted">Tanda tangan belum tersedia.</p>
                        @endif
                    </div>
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
        @php 
            $template = $letterSubmission->letterType->template;
            $fieldData = $letterSubmission->convert_field;
        @endphp
        <div class="card mb-3">
            <div class="card-header">
                <h3>Formulir</h3>
            </div>
            <div class="card-body">
                <!-- SK Magang Template -->
                @if($template == \App\Schemas\ParamSchema::TEMPLATEMAGANG)
                <div class="form-row letter-template" id="sk_magang_template" style="display:none;">
                    <div class="col-md-12 mb-3">
                        <label for="masa_kerja">Masa Kerja <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="date" name="start_date" class="form-control" value="{{ $fieldData['start_date'] ?? null  }}" >
                            <span class="input-group-text">hingga</span>
                            <input type="date" name="end_date" class="form-control" value="{{ $fieldData['end_date'] ?? null  }}" >
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="deskripsi_tugas">Deskripsi Tugas <span class="text-danger">*</span></label>
                        <input class="thriveEditor form-control" id="description_description_task" data-ids="description_task" name="description_task" value="{{ $fieldData['description_task'] ?? null  }}"/>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="deskripsi_magang">Deskripsi Magang <span class="text-danger">*</span></label>
                        <input class="thriveEditor form-control" id="description_description_intern" data-ids="description_intern" name="description_intern" value="{{ $fieldData['description_intern'] ?? null  }}"/>
                    </div>
                </div>
                @endif

                <!-- Perjanjian Kerja Template -->
                <div class="letter-template" id="perjanjian_kerja_template" style="display:none;">
                    <div class="d-flex justify-content-center">
                        <h2>
                            Edit Surat Perjanjian Kerja
                        </h2>
                    </div>
                    <!-- Bagian Perjanjian Kerja -->
                    <div class="col-md-12 mb-3">
                        <h5 class="mb-2">Bagian Perjanjian Kerja</h5>
                    </div>

                    <!-- Pihak Pertama -->
                    <div class="col-md-12 mb-3">
                        <label for="first_party_company">Pihak Pertama: Nama PT</label>
                        <p>{{ $company['name'] ?? "" }}</p>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="responsible_person">Penanggung Jawab</label>
                        <p>{{ $company['director'] ?? "" }}</p>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="first_party_address">Alamat</label>
                        <p>{{ $company['address'] ?? "" }}</p>
                    </div>

                    <!-- Pihak Kedua -->
                    <div class="col-md-12 mb-3">
                        <h5 class="mb-2">Pihak Kedua</h5>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="second_party_name">Nama Lengkap</label>
                        <p>{{ Auth::user()->name }}</p>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="second_party_ktp">No KTP</label>
                        <p>{{ Auth::user()->id_card }}</p>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="second_party_address">Alamat</label>
                        <p>{{ Auth::user()->address }}</p>

                    </div>

                    <!-- Bagian Surat Keputusan -->
                    <div class="col-md-12 mb-3">
                        <h5 class="mb-2">Bagian Surat Keputusan</h5>
                    </div>


                    @if(isset(Auth::user()->last_position))
                    <!-- Jabatan -->
                    <div class="col-md-12 mb-3">
                        <label for="jabatan">Jabatan</label>
                        <select class="form-control selectOrCreate2" name="position_id" >
                            <option value="" selected disabled>Pilih </option>
                            @foreach($positions as $position)
                                <option value="{{ $position->name }}" {{ Auth::user()->last_position->position_id == $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-12 mb-3">
                        <label for="monthly_salary">Gaji Bulanan</label>
                        <input type="text" class="form-control" id="amount_show" placeholder="Rp {{ number_format($letterSubmission->salary ?? 0, 0, ',', '.') }}" oninput="formatRupiahFormat(this,'amount')"/>
                        <input type="hidden" id="amount" name="salary" value="{{ $fieldData['salary'] ?? null }}">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="salary_date">Tanggal Perhitungan Gaji</label>
                        <input type="date" name="salary_date" class="form-control" value="{{ $fieldData['salary_date'] ?? null }}" placeholder="Masukkan tanggal perhitungan gaji">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="working_hours">Jam Kerja</label>
                        <input type="text" name="working_hours" class="form-control" value="{{ $fieldData['working_hours'] ?? null  }}" placeholder="Masukkan jam kerja">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="work_location">Penempatan</label>
                        <input type="text" name="work_location" class="form-control" value="{{ $fieldData['work_location'] ?? null  }}" placeholder="Masukkan penempatan kerja">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="job_responsibilities">Tanggung Jawab Pekerjaan</label>
                        <input class="thriveEditor form-control" id="description_job_responsibilities" data-ids="job_responsibilities" name="job_responsibilities" value="{{ $fieldData['job_responsibilities'] ?? null  }}" />
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="npwp_number">No. NPWP</label>
                        <input type="number" name="npwp_number" class="form-control" value="{{ Auth::user()->npwp_number }}" placeholder="Masukkan nomor NPWP">
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
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        let amount = document.getElementById("amount").value;
        if (amount) 
        {
            document.getElementById("amount_show").value = amount;
            formatRupiahFormat(document.getElementById("amount_show"),"amount"); // Format default value
        }

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
</style>
@endsection