@extends('adminlte::page')

@section('title', 'User Profile')

@section('content')

<div class="row mt-2">
    <div class="col-md-12">
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

    <div class="col-md-12">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                    aria-controls="profile" aria-selected="true">Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="aplikasi-tab" data-toggle="tab" href="#aplikasi" role="tab"
                    aria-controls="aplikasi" aria-selected="false">Browser Aktif</a>
            </li>
        </ul>
        @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <form action="{{ route('user.profileUpdate', $userEdit->slug) }}" method="post" enctype="multipart/form-data">
            @csrf
            @if($userEdit)
            @method('PUT')
            @endif
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="accordion" id="accordionExample">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left" type="button"
                                        data-toggle="collapse" data-target="#collapseOne" aria-expanded="true"
                                        aria-controls="collapseOne">
                                        User Profile
                                    </button>
                                </h2>
                            </div>

                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                                data-parent="#accordionExample">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="name">Nama:</label>
                                        <input type="text" id="name" name="name" class="form-control"
                                            placeholder="Anwar" value="{{ old('name') ?? @$userEdit->name }}" required>
                                    </div>
                                    <!-- Alamat -->
                                    <div class="form-group">
                                        <label for="alamat">Alamat <span class="text-danger">*</span></label>
                                        <input type="text" name="address" class="form-control"
                                            placeholder="Masukkan Alamat Lengkap"
                                            value="{{ old('address') ?? @$userEdit->address }}" required>
                                    </div>

                                    <!-- Nomor KTP -->
                                    <div class="form-group">
                                        <label for="ktp">Nomor KTP <span class="text-danger">*</span></label>
                                        <input type="number" name="id_card" class="form-control"
                                            placeholder="Masukkan Nomor KTP"
                                            value="{{ old('id_card') ?? @$userEdit->id_card }}" required>
                                    </div>

                                    <!-- Upload KTP -->
                                    <div class="form-group">
                                        <label for="id_card_image">Upload KTP</label>
                                        @if(@$userEdit->id_card_image)
                                        <div class="mt-1 mb-2">
                                            <img src="{{ Storage::url(@$userEdit->id_card_image) }}" alt="Tanda Tangan"
                                                class="img-fluid"
                                                style="max-width: 200px; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                                        </div>
                                        @endif
                                        <input type="file" name="id_card_image" id="id_card_image" class="form-control"
                                            accept="image/*">
                                    </div>

                                    <div class="form-group">
                                        <label for="npwp_number">No. NPWP</label>
                                        <input type="number" name="npwp_number" class="form-control"
                                            placeholder="Masukkan nomor NPWP"
                                            value="{{ old('npwp_number') ?? @$userEdit->npwp_number }}" />
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email:</label>
                                        <p class="form-control-plaintext">{{ old('email') ?? @$userEdit->email }}</p>
                                    </div>

                                    <div class="form-group">
                                        <label for="phone">Phone:</label>
                                        <input type="text" id="phone" name="phone" class="form-control"
                                            placeholder="08568989080" value="{{ old('phone') ?? @$userEdit->phone }}"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/^((0|62)[0-9]*)$/, '$1');">
                                    </div>

                                    <div class="form-group">
                                        <label for="divisions">Divisi:</label>
                                        @foreach($userEdit->divisions as $division)
                                        <p class="form-control-plaintext">{{ $division->name }}</p>
                                        @endforeach
                                    </div>

                                    <div class="form-group">
                                        <label for="approvement_user_id">User Persetujuan:</label>
                                        <p class="form-control-plaintext">
                                            {{ $userEdit->approver ? $userEdit->approver->name : '' }}</p>
                                    </div>

                                    <div class="form-group">
                                        <label for="company">Company:</label>
                                        <p class="form-control-plaintext">
                                            {{ $userEdit->company ? $userEdit->company->name : '' }}</p>
                                    </div>

                                    @if(!@$userEdit)
                                    <div class="form-group">
                                        <label for="password">Password:</label>
                                        <input type="password" id="password" name="password" class="form-control"
                                            placeholder="**********" value="{{ old('password') }}"
                                            autocomplete="new-password">
                                    </div>

                                    <div class="form-group">
                                        <label for="confirmPassword">Confirm Password:</label>
                                        <input type="password" id="confirmPassword" name="confirmPassword"
                                            class="form-control" placeholder="**********" autocomplete="new-password">
                                    </div>
                                    @else
                                    @if(@$userEdit->id == Auth::user()->id)
                                    <div class="form-group">
                                        <label for="oldPassword">Password Lama:</label>
                                        <input type="password" id="oldPassword" name="oldPassword" class="form-control"
                                            placeholder="**********" autocomplete="off">
                                    </div>

                                    <div class="form-group">
                                        <label for="newPassword">Password Baru:</label>
                                        <input type="password" id="newPassword" name="newPassword" class="form-control"
                                            placeholder="**********" autocomplete="new-password">
                                    </div>

                                    <div class="form-group">
                                        <label for="confirmPassword">Konfirmasi Password:</label>
                                        <input type="password" id="confirmPassword" name="confirmPassword"
                                            class="form-control" placeholder="**********" autocomplete="new-password">
                                    </div>
                                    @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left collapsed" type="button"
                                        data-toggle="collapse" data-target="#collapseThree" aria-expanded="false"
                                        aria-controls="collapseThree">
                                        Latar Belakang Anda
                                    </button>
                                </h2>
                            </div>

                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree"
                                data-parent="#accordionExample">
                                <div class="card-body">
                                    <!-- Latar Belakang -->
                                    <div class="form-group">
                                        <label for="latar_belakang">Pendidikan</label>
                                        <input type="text" class="thriveEditor" data-ids="background" name="background"
                                            id="description_background" placeholder="Deskripsi"
                                            value="{{ $userEdit->background }}">
                                    </div>

                                    <!-- Pengalaman Kerja -->
                                    <div class="form-group">
                                        <label for="pengalaman_kerja">Pengalaman Kerja</label>
                                        <input type="text" class="thriveEditor" data-ids="experience" name="experience"
                                            id="description_experience" placeholder="Pengalaman Kerja"
                                            value="{{ $userEdit->experience }}">
                                    </div>

                                    <!-- Menguasai -->
                                    <div class="form-group">
                                        <label for="menguasai">Menguasai</label>
                                        <input type="text" class="thriveEditor" data-ids="skill" name="skill"
                                            id="description_skill" placeholder="Skill Yang Di miliki"
                                            value="{{ $userEdit->skill }}">
                                    </div>

                                    <!-- Pencapaian -->
                                    <div class="mb-3">
                                        <label for="achievement" class="form-label">Pencapaian</label>
                                        <div class="input-group mb-2">
                                            <div class="input-group-append">
                                                <button type="button"
                                                    class="btn btn-success addPencapaian">+</button>
                                            </div>
                                        </div>
                                        <div id="pencapaianContainer">
                                            @foreach($userEdit->achievement_decode ?? [] as $achievement)
                                            @if(!$permissionEditProfile)
                                                <div class="form-control-plaintext bg-light p-2 mt-2">{{ $achievement }}</div>
                                            @else
                                                <div class="input-group mb-2">
                                                    <input type="text" name="achievement[]" class="form-control"
                                                        value="{{ $achievement }}" placeholder="Masukkan Pencapaian">
                                                    <div class="input-group-append">
                                                        <button type="button"
                                                            class="btn btn-danger removePencapaian">-</button>
                                                    </div>
                                                </div>
                                            @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Kegagalan -->
                                    <div class="mb-3">
                                        <label for="failure" class="form-label">Kegagalan</label>
                                        <div class="input-group mb-2">
                                            <div class="input-group-append">
                                                <button type="button"
                                                    class="btn btn-success addKegagalan">+</button>
                                            </div>
                                        </div>
                                        <div id="kegagalanContainer">
                                            @foreach($userEdit->failure_decode ?? [] as $failure)
                                            @if(!$permissionEditProfile)
                                                <div class="form-control-plaintext bg-light p-2 mt-2">{{ $failure }}</div>
                                            @else
                                            <div class="input-group mb-2">
                                                <input type="text" name="failure[]" class="form-control"
                                                    value="{{ $failure }}" placeholder="Masukkan Kegagalan">
                                                <div class="input-group-append">
                                                    <button type="button"
                                                        class="btn btn-danger removeKegagalan">-</button>
                                                </div>
                                            </div>
                                            @endif
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="form-group text-right">
                            <button id="buttonSubmit" type="submit"
                                class="btn btn-primary">{{ @$userEdit ? 'Ubah' : 'Simpan' }}</button>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="aplikasi" role="tabpanel" aria-labelledby="aplikasi-tab">
                    {{-- aplikasi --}}
                    <div class="card">
                        <div class="card-header">
                            Browser Aktif
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nama Browser</th>
                                            <th>Login Terakhir</th>
                                            <th>Checkin Terakhir</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($userApps as $browser)
                                        <tr>
                                            <td>{{ $browser->browser_name }}</td>
                                            <td>{{ $browser->last_login_at ? \Carbon\Carbon::parse($browser->last_login_at)->format('d-m-Y H:i:s') : '-' }}
                                            </td>
                                            <td>{{ $browser->last_scheduled_checkin ? \Carbon\Carbon::parse($browser->last_scheduled_checkin)->format('d-m-Y H:i:s') : '-' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-2">
                                {{ $userApps->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>

@endsection

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>

<script>
$(document).ready(function() {
    // Add Pencapaian
    $('.addPencapaian').click(function() {
        $('#pencapaianContainer').append(`
                <div class="input-group mb-2">
                    <input type="text" name="achievement[]" class="form-control" placeholder="Masukkan Pencapaian">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger removePencapaian">-</button>
                    </div>
                </div>
            `);
    });

    $(document).on('click', '.removePencapaian', function() {
        $(this).closest('.input-group').remove();
    });

    // Add Kegagalan
    $('.addKegagalan').click(function() {
        $('#kegagalanContainer').append(`
                <div class="input-group mb-2">
                    <input type="text" name="failure[]" class="form-control" placeholder="Masukkan Kegagalan">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger removeKegagalan">-</button>
                    </div>
                </div>
            `);
    });

    $(document).on('click', '.removeKegagalan', function() {
        $(this).closest('.input-group').remove();
    });
});
</script>
@endsection