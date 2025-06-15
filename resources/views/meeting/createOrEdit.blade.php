@extends('adminlte::page')

@section('title', 'Pengajuan Rapat')

@section('content_header')
    <h1><i class="fas fa-calendar-plus me-2"></i> Input Pengajuan Rapat</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('meeting.store') }}" method="POST">
            @csrf

            {{-- Informasi Rapat --}}
            <x-adminlte-card title="Informasi Rapat" theme="primary" icon="fas fa-info-circle">
                <x-adminlte-input name="nama_rapat" label="Nama Rapat" placeholder="Masukkan nama rapat" value="{{ old('nama_rapat') }}" required />

                <x-adminlte-textarea name="agenda_rapat" label="Agenda Rapat" rows=3 placeholder="Deskripsikan agenda rapat secara lengkap">{{ old('agenda_rapat') }}</x-adminlte-textarea>

                <x-adminlte-textarea name="catatan" label="Catatan" rows=2 placeholder="Tambahkan catatan jika diperlukan">{{ old('catatan') }}</x-adminlte-textarea>
            </x-adminlte-card>

            {{-- Waktu Rapat --}}
            <x-adminlte-card title="Waktu Rapat" theme="primary" icon="fas fa-clock">
                <div class="row">
                    <div class="col-md-3">
                        <x-adminlte-input name="tanggal_mulai" label="Tanggal Mulai" type="date" value="{{ old('tanggal_mulai') }}" required />
                    </div>
                    <div class="col-md-3">
                        <x-adminlte-input name="tanggal_berakhir" label="Tanggal Berakhir" type="date" value="{{ old('tanggal_berakhir') }}" required />
                    </div>
                    <div class="col-md-3">
                        <x-adminlte-input name="jam_mulai" label="Jam Mulai" type="time" min="07:00" max="20:00" value="{{ old('jam_mulai') }}" required />
                    </div>
                    <div class="col-md-3">
                        <x-adminlte-input name="jam_berakhir" label="Jam Berakhir" type="time" min="07:00" max="20:00" value="{{ old('jam_berakhir') }}" required />
                    </div>
                </div>
            </x-adminlte-card>

            {{-- Peserta Rapat --}}
            <x-adminlte-card title="Peserta Rapat" theme="primary" icon="fas fa-users">
                <div class="row">
                    <div class="col-md-6">
                        <label for="nama_pic">Nama PIC</label>
                        <select name="nama_pic[]" id="nama_pic" multiple="multiple" class="form-control" required></select>
                        <small class="text-muted">Pilih satu atau lebih PIC</small>
                    </div>
                    <div class="col-md-6">
                        <label for="peserta">Peserta Rapat</label>
                        <select name="peserta[]" id="peserta" multiple="multiple" class="form-control" required></select>
                        <small class="text-muted">Pilih peserta rapat</small>
                    </div>
                </div>
            </x-adminlte-card>

            {{-- Tempat Rapat --}}
            <x-adminlte-card title="Tempat Rapat" theme="primary" icon="fas fa-map-marker-alt">
                <x-adminlte-select name="jenis_rapat" label="Jenis Rapat" id="jenis_rapat" required>
                    <option value="">---Pilih Jenis Rapat---</option>
                    <option value="offline" {{ old('jenis_rapat') == 'offline' ? 'selected' : '' }}>Offline</option>
                    <option value="online" {{ old('jenis_rapat') == 'online' ? 'selected' : '' }}>Online</option>
                </x-adminlte-select>

                <div id="google_meet_section" style="display:none">
                    <x-adminlte-input name="google_meet_link" label="Link Google Meet" type="url" placeholder="https://meet.google.com/xxx-yyyy-zzz" value="{{ old('google_meet_link') }}" />
                </div>

                <div id="tempat_rapat_section" style="display:none">
                    <x-adminlte-input name="tempat_rapat" label="Tempat Rapat" placeholder="Masukkan ruangan/lokasi rapat" value="{{ old('tempat_rapat') }}" />
                </div>

                <div id="google_event_section" style="display:none">
                    <x-adminlte-input name="google_event_id" label="ID Google Event" value="{{ old('google_event_id') }}" />
                </div>
            </x-adminlte-card>

            <div class="d-flex justify-content-between">
                <button type="reset" class="btn btn-danger">
                    <i class="fas fa-redo me-2"></i> Atur Ulang
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-calendar-plus me-2"></i> Buat Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/44.1.0/ckeditor5.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: none;
            margin-bottom: 30px;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            padding-bottom: 10px;
            margin: 25px 0 15px;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 5px;
        }
        
        .required-label::after {
            content: " *";
            color: #e74c3c;
        }
        
        .form-control, .select2-selection {
            border-radius: 5px;
            border: 1px solid var(--border-color);
            padding: 8px 12px;
            height: 40px;
        }
        
        .form-control:focus, .select2-selection:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(60, 141, 188, 0.25);
        }
        
        .invalid-feedback {
            font-size: 0.85rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 8px 20px;
            font-weight: 500;
            border-radius: 5px;
        }
        
        .btn-primary:hover {
            background-color: #367fa9;
            border-color: #367fa9;
        }
        
        .btn-danger {
            background-color: #dd4b39;
            border-color: #d73925;
            padding: 8px 20px;
            font-weight: 500;
            border-radius: 5px;
        }
        
        .btn-danger:hover {
            background-color: #d73925;
            border-color: #d73925;
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid var(--border-color);
        }
        
        .date-time-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .date-time-item {
            flex: 1;
            min-width: 200px;
        }
        
        .form-section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .icon-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(60, 141, 188, 0.1);
            border-radius: 5px 0 0 5px;
            color: var(--primary-color);
        }
        
        .form-row-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .form-row-item {
            flex: 1;
            min-width: 250px;
        }
        
        @media (max-width: 768px) {
            .date-time-item, .form-row-item {
                min-width: 100%;
            }
            
            .form-row-group {
                gap: 15px;
            }
        }
        
        .select2-container .select2-selection--multiple {
            min-height: 40px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .info-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/44.1.0/ckeditor5.umd.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"
        integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!--select2-->
    <script>
        $(document).ready(function() {
            $('.peserta').select2({
                placeholder: 'Pilih peserta rapat',
                allowClear: true,

            });

            $("#peserta").select2({
                ajax: {
                    url: "{{ route('get-users') }}",
                    type: "post",
                    delay: 250,
                    dataType: 'json',
                    data: function(params) {
                        return {
                            name: params.
                            term,
                            "_token": "{{ csrf_token() }}",
                        };
                    },

                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                }
                            })
                        };
                    },
                },
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.nama_pic').select2({
                placeholder: 'Pilih Nama PIC',
                allowClear: true,

            });

            $("#nama_pic").select2({
                ajax: {
                    url: "{{ route('get-users') }}",
                    type: "post",
                    delay: 250,
                    dataType: 'json',
                    data: function(params) {
                        return {
                            name: params.
                            term,
                            "_token": "{{ csrf_token() }}",
                        };
                    },

                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                }
                            })
                        };
                    },
                },
            });
        });
    </script>

    <!--ckeditor textarea-->
    <script>
        const {
            ClassicEditor,
            Essentials,
            Bold,
            Italic,
            Font,
            Paragraph
        } = CKEDITOR;

        // Fungsi untuk menginisialisasi editor
        const initializeEditor = (selector) => {
            ClassicEditor
                .create(document.querySelector(selector), {
                    licenseKey: 'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3Njc5MTY3OTksImp0aSI6ImQ0YTdhMWVmLTM5MGItNGRhYi1iNTg1LWFhNmEzOWQ3YjEyMiIsInVzYWdlRW5kcG9pbnQiOiJodHRwczovL3Byb3h5LWV2ZW50LmNrZWRpdG9yLmNvbSIsImRpc3RyaWJ1dGlvbkNoYW5uZWwiOlsiY2xvdWQiLCJkcnVwYWwiXSwiZmVhdHVyZXMiOlsiRFJVUCJdLCJ2YyI6ImI0Njc1NWU3In0.3p7AY9a3fj4AurrTrdBw_qa27RH99OoDsSj_6sK0DB1XKCyE_961SnbdkDZ5hyhdFVrtyCfoqEfPUlSb6xV_dA',
                    plugins: [Essentials, Bold, Italic, Font, Paragraph],
                    toolbar: [
                        'undo', 'redo', '|', 'bold', 'italic', '|',
                        'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor'
                    ]
                })
                .catch(error => {
                    console.error(`Error initializing editor for ${selector}:`, error);
                });
        };

        // Inisialisasi editor untuk #agenda_rapat
        initializeEditor('#agenda_rapat');

        // Inisialisasi editor untuk #catatan
        initializeEditor('#catatan');
    </script>
    <script>
        $(document).ready(function() {
            // Toggle Google Meet Link field based on jenis_rapat
            $('#jenis_rapat').on('change', function() {
                const jenisRapat = $(this).val();
                const googleMeetLink = $('#google_meet_link');
                const googleEventId = $('#google_event_id');
                const tempatRapat = $('#tempat_rapat');

                if (jenisRapat === 'online') {
                    $('#google_meet_section').show();
                    $('#tempat_rapat_section').hide();
                    tempatRapat.val('');

                    // Cek apakah form sudah diisi dengan lengkap
                    const namaRapat = $('#nama_rapat').val();
                    const tanggalMulai = $('#tanggal_mulai').val();
                    const tanggalBerakhir = $('#tanggal_berakhir').val();
                    const jamMulai = $('#jam_mulai').val();
                    const jamBerakhir = $('#jam_berakhir').val();
                    const selectedPic = $('#nama_pic').val();
                    const selectedPeserta = $('#peserta').val();


                    if (namaRapat && tanggalMulai && tanggalBerakhir && jamMulai && jamBerakhir) {
                        // Tampilkan loading
                        googleMeetLink.attr('disabled', true);
                        googleMeetLink.val('Membuat link meeting...');

                        console.log('Sending data:', {
                            nama_rapat: namaRapat,
                            tanggal_mulai: tanggalMulai,
                            tanggal_berakhir: tanggalBerakhir,
                            jam_mulai: jamMulai,
                            jam_berakhir: jamBerakhir,
                            nama_pic: selectedPic,
                            peserta: selectedPeserta,

                        });

                        // Dapatkan Google Meet
                        // Buat Google Meet
                        $.ajax({
                            url: '{{ route('meeting.create-google-meet') }}',
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                _token: '{{ csrf_token() }}',
                                nama_rapat: namaRapat,
                                tanggal_mulai: tanggalMulai,
                                tanggal_berakhir: tanggalBerakhir,
                                jam_mulai: jamMulai,
                                jam_berakhir: jamBerakhir,
                                nama_pic: selectedPic,
                                peserta: selectedPeserta
                            },
                            success: function(response) {
                                if (response.success) {
                                    googleMeetLink.val(response
                                        .link);
                                    googleEventId.val(response
                                        .event_id);

                                    // // Tampilkan input google_event_id
                                    // $('#google_event_section').show();
                                } else {
                                    alert('Gagal membuat link meeting');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX Error:', status, error);
                                alert('Gagal membuat link meeting');
                            },
                            complete: function() {
                                googleMeetLink.attr('disabled',
                                    false);
                                googleMeetLink.attr('readonly', true);
                            }
                        });
                    } else {
                        alert('Harap isi nama rapat, tanggal, dan jam terlebih dahulu');
                        $(this).val('');
                    }
                } else {
                    $('#google_meet_section').hide();
                    googleMeetLink.val('');
                    $('#tempat_rapat_section').show();
                }
            });

            // Trigger change event on page load for jenis_rapat
            $('#jenis_rapat').trigger('change');

            // Validate end date is after start date
            $('#tanggal_berakhir').on('blur', function() {
                const startDate = $('#tanggal_mulai').val();
                const endDate = $('#tanggal_berakhir').val();

                if (endDate < startDate) {
                    alert('Tanggal berakhir harus setelah tanggal mulai');
                    $('#tanggal_berakhir').val('');
                }
            });

            // Validate end time is after start time when dates are the same
            $('#jam_berakhir').on('blur', function() {
                const startDate = $('#tanggal_mulai').val();
                const endDate = $('#tanggal_berakhir').val();
                const startTime = $('#jam_mulai').val();
                const endTime = $(this).val();

                if (startDate === endDate && startTime && endTime && endTime <= startTime) {
                    alert('Jam berakhir harus setelah jam mulai');
                    $(this).val('');
                }
            });


        });
    </script>

@stop
