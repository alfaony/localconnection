@extends('adminlte::page')

@section('title', 'User Profile')

@section('content')

<div class="col-md-12 mt-2">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Pengguna Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Pengguna Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Pengguna Berhasil Dihapus</div>
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
        <h2 class="mb-4">{{ @$userEdit ? 'Edit Profile' : 'Create User Profile' }}</h2>
        
        <form action="{{ route('user.profileUpdate', $userEdit->slug) }}" method="post" enctype="multipart/form-data">
            @csrf
            @if($userEdit)
            @method('PUT')
            @endif
            
            <div class="form-group">
                <label for="name">Nama:</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Anwar" value="{{ old('name') ?? @$userEdit->name }}" required>
            </div>
            <!-- Alamat -->
            <div class="form-group">
                <label for="alamat">Alamat <span class="text-danger">*</span></label>
                <input type="text" name="address" class="form-control" placeholder="Masukkan Alamat Lengkap" value="{{ old('address') ?? @$userEdit->address }}" required>
            </div>

            <!-- Nomor KTP -->
            <div class="form-group">
                <label for="ktp">Nomor KTP <span class="text-danger">*</span></label>
                <input type="number" name="id_card" class="form-control" placeholder="Masukkan Nomor KTP" value="{{ old('id_card') ?? @$userEdit->id_card }}"  required>
            </div>

            <!-- Upload KTP -->
            <div class="form-group">
                <label for="id_card_image">Upload KTP</label>
                @if(@$userEdit->id_card_image)
                <div class="mt-1 mb-2">
                    <img src="{{ Storage::url(@$userEdit->id_card_image) }}" alt="Tanda Tangan" class="img-fluid" style="max-width: 200px; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                </div>
                @endif
                <input type="file" name="id_card_image" id="id_card_image" class="form-control" accept="image/*">
            </div>

            <div class="form-group">
                <label for="npwp_number">No. NPWP</label>
                <input type="number" name="npwp_number" class="form-control" placeholder="Masukkan nomor NPWP" value="{{ old('npwp_number') ?? @$userEdit->npwp_number }}"/>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <p class="form-control-plaintext">{{ old('email') ?? @$userEdit->email }}</p>
            </div>

            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="08568989080" value="{{ old('phone') ?? @$userEdit->phone }}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/^((0|62)[0-9]*)$/, '$1');">
            </div>

            <div class="form-group">
                <label for="divisions">Divisi:</label>
                @foreach($userEdit->divisions as $division)
                <p class="form-control-plaintext">{{ $division->name }}</p>
                @endforeach
            </div>

            <div class="form-group">
                <label for="approvement_user_id">User Persetujuan:</label>
                <p class="form-control-plaintext">{{ $userEdit->approver ? $userEdit->approver->name : '' }}</p>
            </div>

            <div class="form-group">
                <label for="company">Company:</label>
                <p class="form-control-plaintext">{{ $userEdit->company ? $userEdit->company->name : '' }}</p>
            </div>

            @if(!@$userEdit)
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="**********" value="{{ old('password') }}" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="**********" autocomplete="new-password">
            </div>
            @else
            @if(@$userEdit->id == @$userEditid)
            <div class="form-group">
                <label for="oldPassword">Password Lama:</label>
                <input type="password" id="oldPassword" name="oldPassword" class="form-control" placeholder="**********" autocomplete="off" >
            </div>

            <div class="form-group">
                <label for="newPassword">Password Baru:</label>
                <input type="password" id="newPassword" name="newPassword" class="form-control" placeholder="**********" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="confirmPassword">Konfirmasi Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="**********" autocomplete="new-password">
            </div>
            @endif
            @endif
            <div class="form-group text-right">
                <button id="buttonSubmit" type="submit" class="btn btn-primary">{{ @$userEdit ? 'Ubah' : 'Simpan' }}</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<style>
    .form-group {
        margin-bottom: 1.5rem;
    }
    .card {
        margin-top: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .card-body {
        padding: 20px;
    }
    .form-control-plaintext {
        padding-left: 0;
        font-weight: bold;
    }
    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }
    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }
</style>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
        $('.user-select2').select2();
    });
</script>
@endsection
