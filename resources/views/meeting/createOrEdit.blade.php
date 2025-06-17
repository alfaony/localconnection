@extends('adminlte::page')

@section('title', isset($meeting) ? 'Edit Meeting' : 'Create Meeting')

@section('content_header')
    <h3><i class="fas fa-calendar-plus me-2"></i> {{ isset($meeting) ? 'Edit Meeting' : 'Create Meeting' }}</h3>
@endsection

@section('content')
@include('components.alert')
<div class="card">
    <div class="card-body">
        <form action="{{ isset($meeting) ? route('meeting.update', $meeting->slug) : route('meeting.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($meeting))
                @method('PUT')
            @endif

            {{-- Info Rapat --}}
            <x-adminlte-card title="Info Rapat" theme="primary" icon="fas fa-info-circle">
                <x-adminlte-input name="meeting_name" label="Nama Rapat" placeholder="Masukkan nama rapat" value="{{ old('meeting_name', $meeting->meeting_name ?? '') }}" required />
                <label for="description_meeting_agenda">Agenda Rapat</label>
                <input class="thriveEditor form-control" id="description_meeting_agenda" data-ids="meeting_agenda" name="meeting_agenda" rows="3" placeholder="Masukkan agenda rapat" value="{{ old('meeting_agenda') ?? @$meeting->meeting_agenda }}"/>

                <!-- <x-adminlte-textarea name="notes" label="Keterangan" rows=2 placeholder="Keterangan tambahan jika perlu">{{ old('notes', $meeting->notes ?? '') }}</x-adminlte-textarea> -->
            </x-adminlte-card>

            {{-- Jadwal --}}
            <x-adminlte-card title="Jadwal" theme="primary" icon="fas fa-clock">
                <div class="row">
                    <div class="col-md-3">
                        <x-adminlte-input name="start_date" label="Tanggal Mulai" type="date" value="{{ old('start_date', $meeting->start_date ?? '') }}" required />
                    </div>
                    <div class="col-md-3">
                        <x-adminlte-input name="end_date" label="Tanggal Berakhir" type="date" value="{{ old('end_date', $meeting->end_date ?? '') }}" required />
                    </div>
                    <div class="col-md-3">
                        <x-adminlte-input name="start_time" label="Jam Mulai" type="time" min="07:00" max="20:00" value="{{ old('start_time', $meeting->start_time ?? '') }}" required />
                    </div>
                    <div class="col-md-3">
                        <x-adminlte-input name="end_time" label="Jam Berakhir" type="time" min="07:00" max="20:00" value="{{ old('end_time', $meeting->end_time ?? '') }}" required />
                    </div>
                </div>
            </x-adminlte-card>

            {{-- Peserta --}}
            <x-adminlte-card title="Peserta" theme="primary" icon="fas fa-users">
                <div class="row">
                    <div class="col-md-6">
                        <label for="pic_name">PIC</label>
                        <p>{{ Auth::user()->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <label for="participant">Peserta</label>
                <select name="participant[]" multiple class="form-control selectMulti2">
    @foreach ($meeting->combined_participants ?? [] as $p)
        <option value="{{ $p['id'] }}" selected>{{ $p['name'] }}</option>
    @endforeach
</select>
                        <small class="text-muted">Pilih peserta rapat</small>
                    </div>
                </div>
            </x-adminlte-card>

            {{-- Lokasi --}}
            <x-adminlte-card title="Lokasi" theme="primary" icon="fas fa-map-marker-alt">
                <x-adminlte-select name="meeting_type" label="Tipe Rapat" id="meeting_type" required>
                    <option value="">---Pilih Tipe Rapat---</option>
                    <option value="offline" {{ old('meeting_type', $meeting->meeting_type ?? '') == 'offline' ? 'selected' : '' }}>Offline</option>
                    <option value="online" {{ old('meeting_type', $meeting->meeting_type ?? '') == 'online' ? 'selected' : '' }}>Online</option>
                </x-adminlte-select>

                <div id="google_meet_section" style="display:none">
                    <x-adminlte-input name="google_meet_link" label="Link" type="url" placeholder="https://meet.google.com/xxx-yyyy-zzz" value="{{ old('google_meet_link', $meeting->google_meet_link ?? '') }}" />
                </div>

                <div id="meeting_location_section" style="display:none">
                    <x-adminlte-input name="meeting_location" label="Lokasi Rapat" placeholder="Masukkan lokasi rapat" value="{{ old('meeting_location', $meeting->meeting_location ?? '') }}" />
                </div>

                <div id="google_event_section" style="display:none">
                    <x-adminlte-input name="google_event_id" label="ID Google Event" value="{{ old('google_event_id', $meeting->google_event_id ?? '') }}" />
                </div>
            </x-adminlte-card>

            {{-- Lain-lain --}}
            <x-adminlte-card title="Lain-lain" theme="primary" icon="fas fa-paperclip">
                <x-adminlte-select name="status" label="Proyek" class="select2">
                    <option selected disabled>---Pilih Proyek---</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" {{ old('project_id', $meeting->project_id ?? '') == $project->id ? 'selected' : '' }}>{{ $project->title }}</option>
                    @endforeach
                </x-adminlte-select>
                <x-adminlte-input name="attachment" label="Lampiran" type="file" />
            </x-adminlte-card>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-calendar-plus me-2"></i> {{ isset($meeting) ? 'Update' : 'Submit' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Select an option',
            allowClear: true
        });

        $('.selectMulti2').select2({
            tags: true,
            placeholder: 'Select an option',
            allowClear: true
        });
    });
</script>
<script>
    $(document).ready(function() {
        function toggleFields() {
            let jenis = $('#meeting_type').val();
            $('#google_meet_section, #meeting_location_section').hide();
            if (jenis === 'online') {
                $('#google_meet_section').show();
                $('#meeting_location').val('');
            } else if (jenis === 'offline') {
                $('#meeting_location_section').show();
                $('#google_meet_link').val('');
            }
        }

        $('#meeting_type').change(toggleFields).trigger('change');

        $('#pic_name, #participant').select2({
            placeholder: 'Search name...',
            ajax: {
                url: '{{ route('get-users') }}',
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        name: params.term,
                        _token: '{{ csrf_token() }}'
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(item) {
                            return { id: item.id, text: item.name };
                        })
                    };
                },
            }
        });

        $('#end_date').on('change', function () {
            if (new Date($(this).val()) < new Date($('#start_date').val())) {
                alert('Tanggal berakhir harus setelah tanggal mulai');
                $(this).val('');
            }
        });

        $('#end_time').on('change', function () {
            if ($('#start_date').val() === $('#end_date').val()) {
                if ($(this).val() <= $('#start_time').val()) {
                    alert('Jam selesai harus setelah jam mulai');
                    $(this).val('');
                }
            }
        });
    });

    // Auto-fill end_date = start_date jika end_date masih kosong
    $('#start_date').on('change', function () {
        if (!$('#end_date').val()) {
            $('#end_date').val($(this).val());
        }
    });

    // Auto-fill end_time = start_time + 1 jam jika end_time masih kosong
    $('#start_time').on('change', function () {
        const startTime = $(this).val();
        const endTimeInput = $('#end_time');
        
        if (startTime && !endTimeInput.val()) {
            const [hour, minute] = startTime.split(':').map(Number);
            let endHour = hour + 1;
            let endMinute = minute;

            if (endHour >= 24) {
                endHour = 23;
                endMinute = 59;
            }

            const formattedEndTime = `${String(endHour).padStart(2, '0')}:${String(endMinute).padStart(2, '0')}`;
            endTimeInput.val(formattedEndTime);
        }
    });
</script>
@endsection

@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
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
    .select2-selection__choice {
        background-color: #007bff !important;
        border: 1px solid #007bff !important;
        color: #ffffff !important;
    }
    .select2-selection--single {
        height: 38px !important;
    }
</style>
@endsection