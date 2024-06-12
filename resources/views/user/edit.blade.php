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
        <h2>{{ @$userEdit ? 'Edit Profile' : 'Create User Profile' }}</h2>
        
        <form action="{{ route('user.profileUpdate',$userEdit->slug) }}" method="post">
            @csrf
            @if($userEdit)
            @method('PUT')
            @endif
            
            <div class="form-group">
                <label for="name">Nama:</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Anwar" value="{{ old('name') ?? @$userEdit->name }}" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <p class="form-control-plaintext">{{ old('email') ?? @$userEdit->email }}<p>
            </div>

            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="08568989080" value="{{ old('phone') ?? @$userEdit->phone }}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/^((0|62)[0-9]*)$/, '$1');">
            </div>

            <div class="form-group">
                <label for="divisions">Divisi:</label>
                @foreach($userEdit->divisions as $division)
                <p class="form-control-plaintext">
                    {{ $division->name }}
                </p>
                @endforeach
            </div>
            


            <div class="form-group">
                <label for="approvement_user_id">User Persetujuan:</label>
                <p class="form-control-plaintext">
                    {{ $userEdit->approver ? $userEdit->approver->name : '' }}
                </p>
            </div>

            <div class="form-group">
                <label for="company">Company:</label>
                <p class="form-control-plaintext">
                    {{ $userEdit->company ? $userEdit->company->name : '' }}
                </p>
            </div>

            @if(!@$userEdit)
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="**********" value="{{ old('password') }}" required>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="**********">
            </div>
            @else
            @if(@$userEdit->id == Auth::user()->id)
            <div class="form-group">
                <label for="oldPassword">Password Lama:</label>
                <input type="password" id="oldPassword" name="oldPassword" class="form-control" placeholder="**********">
            </div>

            <div class="form-group">
                <label for="newPassword">Password Baru:</label>
                <input type="password" id="newPassword" name="newPassword" class="form-control" placeholder="**********">
            </div>

            <div class="form-group">
                <label for="confirmPassword">Konfirmasi Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="**********">
            </div>
            @endif
            @endif
            <div class="form-group">
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
</style>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
        $('.user-select2').select2();

        $('#alertCheckbox').change(function() {
            if ($(this).is(':checked')) {
                $('#alertOptions').show();
            } else {
                $('#alertOptions').hide();
            }
        });
    });
</script>
@endsection
