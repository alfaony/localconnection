@extends('adminlte::page')

@section('title', 'Tambah Master Account')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Tambah Master Account</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master-account.index') }}">Master Accounts</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('master-account.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="card-body">
                {{-- Basic Info --}}
                <h5 class="mb-3"><i class="fas fa-info-circle"></i> Informasi Dasar</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="software_id">Software <span class="text-danger">*</span></label>
                            <select class="form-control @error('software_id') is-invalid @enderror select2" 
                                    id="software_id" 
                                    name="software_id" 
                                    required>
                                <option value="">-- Pilih Software --</option>
                                @foreach($softwares as $software)
                                <option value="{{ $software->id }}" {{ old('software_id') == $software->id ? 'selected' : '' }}>
                                    {{ $software->nama }} - {{ $software->tipe_paket }}
                                </option>
                                @endforeach
                            </select>
                            @error('software_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nama_akun">Nama Akun <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama_akun') is-invalid @enderror" 
                                   id="nama_akun" 
                                   name="nama_akun" 
                                   value="{{ old('nama_akun') }}" 
                                   placeholder="Contoh: Netflix Premium #1"
                                   required>
                            @error('nama_akun')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_slots">Maksimal Slot <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('max_slots') is-invalid @enderror" 
                                   id="max_slots" 
                                   name="max_slots" 
                                   value="{{ old('max_slots', 5) }}" 
                                   min="1"
                                   required>
                            @error('max_slots')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Jumlah maksimal customer yang bisa menggunakan akun ini</small>
                        </div>

                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Flexible Credentials --}}
                <h5 class="mb-3"><i class="fas fa-key"></i> Kredensial Akses</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email_akun">Email Akun</label>
                            <input type="text" 
                                   class="form-control @error('email_akun') is-invalid @enderror" 
                                   id="email_akun" 
                                   name="email_akun" 
                                   value="{{ old('email_akun') }}" 
                                   placeholder="email@example.com">
                            @error('email_akun')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_akun">Password Akun</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password_akun') is-invalid @enderror" 
                                       id="password_akun" 
                                       name="password_akun" 
                                       value="{{ old('password_akun') }}" 
                                       placeholder="••••••••">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password_akun')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Password akan dienkripsi otomatis</small>
                        </div>

                        <div class="form-group">
                            <label for="pin_code">PIN Code</label>
                            <input type="text" 
                                   class="form-control @error('pin_code') is-invalid @enderror" 
                                   id="pin_code" 
                                   name="pin_code" 
                                   value="{{ old('pin_code') }}" 
                                   placeholder="Contoh: 1234">
                            @error('pin_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="link_invite">Link Invite</label>
                            <textarea class="form-control @error('link_invite') is-invalid @enderror" 
                                      id="link_invite" 
                                      name="link_invite" 
                                      rows="3"
                                      placeholder="https://...">{{ old('link_invite') }}</textarea>
                            @error('link_invite')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="attachment">File Attachment</label>
                            <div class="custom-file">
                                <input type="file" 
                                       class="custom-file-input @error('attachment') is-invalid @enderror" 
                                       id="attachment" 
                                       name="attachment"
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <label class="custom-file-label" for="attachment">Pilih file...</label>
                                @error('attachment')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Format: PDF, JPG, PNG. Max: 5MB</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="instruksi_akses">Instruksi Akses</label>
                    <input type="text" class="thriveEditor form-control" id="description_instruksi_akses" data-ids="instruksi_akses"  name="instruksi_akses" value="{{ $masterAccount->instruksi_akses ?? '' }}">
                    @error('instruksi_akses')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Instruksi lengkap cara akses untuk customer</small>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="{{ route('master-account.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
@stop


@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: '-- Pilih --',
        allowClear: true,
    });

    // Toggle password visibility
    $('#toggle-password').on('click', function() {
        const passwordInput = $('#password_akun');
        const icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Custom file input label
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>
@stop


@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
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
<style>
   body {
            font-family: Arial, sans-serif;
            /* padding: 20px; */
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            border-radius: 5px;
        }
        

</style>
@stop
