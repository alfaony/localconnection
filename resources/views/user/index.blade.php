@extends('adminlte::page')

@php
$no = ($user->currentPage() - 1) * $user->perPage() + 1;
$totalUser = $totalUser + 1; // Get the total number of projects

@endphp

@section('content')

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
<div class="container p-3 mt-3">
    <div class="col-md-12 mt-2">
        @if(!@$userEdit)
        @canAccess('store','users')
        <!-- Create -->
        <p id="penggunaNo"></p>
        <form action="{{ route('user.store') }}" method="post">
            @csrf
            <label for="name">Nama:</label>
            <input type="text" id="name" name="name" placeholder="Anwar" value="{{ old('name') ?? @$userEdit->name }}" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Budiman@gmail.com" value="{{ old('email') ?? @$userEdit->email }}" required>

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" placeholder="08568989080" value="{{ old('phone') ?? @$userEdit->phone }}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/^((0|62)[0-9]*)$/, '$1');" >

            <label for="phone">Divisi:</label>
            <select name="divisions[]" multiple class="form-control select2">
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                @endforeach
            </select>

            <div class="form-group mt-2 mb-1">
                <label for="checkin-settings">Setting Check-In:</label>
                
                <!-- Check-In Setting -->
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="is_checkin" id="is_checkin" value="1" >
                    <label class="form-check-label" for="is_checkin">Enable Check-In</label>
                </div>

                <!-- Additional Settings - Visible only if is_checkin is enabled -->
                <div id="additionalSettings" style="display: none;">
                    <!-- Manual Check-In -->
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" name="manual_checkin" id="manual_checkin" value="1" {{ old('manual_checkin', 0) ? 'checked' : '' }}>
                        <label class="form-check-label" for="manual_checkin">Check-In Manual</label>
                    </div>

                    <!-- Require Photo -->
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" name="requires_photo" id="requires_photo" value="1" {{ old('requires_photo', false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="requires_photo">Memerlukan Foto Check-In</label>
                    </div>

                    <!-- Require Location -->
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" name="requires_location" id="requires_location" value="1" {{ old('requires_location', false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="requires_location">Memerlukan Lokasi Check-In</label>
                    </div>

                    <!-- Time Settings -->
                    <div class="form-group mt-3">
                        <label for="start_time">Jam Kerja:</label>
                        <div class="input-group">
                            <span class="input-group-text">Mulai</span>
                            <input type="time" class="form-control" name="start_time" id="start_time" value="{{ old('start_time') }}">
                            <span class="input-group-text">Selesai</span>
                            <input type="time" class="form-control" name="end_time" id="end_time" value="{{ old('end_time') }}">
                        </div>
                    </div>

                    <!-- Rest Time -->
                    <div class="form-group mt-2">
                        <label for="rest_time">Waktu Istirahat:</label>
                        <input type="time" class="form-control" name="rest_time" id="rest_time" value="{{ old('rest_time') }}">
                    </div>
                    <div class="form-group mt-3">
                        <label>Waktu Istirahat Khusus:</label>
                        @foreach($dayofweek as $day => $value)
                            <div class="input-group mt-2">
                                <span class="input-group-text">{{ $day }}</span>
                                <input type="time" class="form-control" name="custom_rest_times[{{ $value }}][start]" 
                                    placeholder="Mulai" value="{{ old("custom_rest_times.$day.start") ?? @$userEdit->custom_rest_times[$value]['start'] }}">
                                <span class="input-group-text">to</span>
                                <input type="time" class="form-control" name="custom_rest_times[{{ $value }}][end]" 
                                    placeholder="Selesai" value="{{ old("custom_rest_times.$day.end") ?? @$userEdit->custom_rest_times[$value]['end'] }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($roleAccess)
            <label for="phone">Role:</label>
            <select name="role" class="form-control mb-2 select2" required>
                <option value="" selected disabled>Pilih</option>
                @foreach($role as $a)
                <option value="{{ $a->id }}" {{ @$userEdit->role_id == $a->id ? 'selected' : '' }}> {{ $a->name }} </option>
                @endforeach
            </select>

            <label for="phone">User Persetujuan:</label>
            <select name="approvement_user_id" class="form-control mb-2 user-select2">
                <option value="" selected disabled>Pilih</option>
                @foreach($users as $a)
                <option value="{{ $a->id }}" {{ @$userEdit->approvement_user_id == $a->id ? 'selected' : '' }}> {{ $a->name ." - ".  $a->company->name }} </option>
                @endforeach
            </select>
            @endif

            @if($companyAccess && !@$userEdit)
            <label for="phone">Company:</label>
            <select name="company" class="form-control mb-2" required>
                <option value="" selected disabled>Pilih</option>
                @foreach($company as $a)
                <option value="{{ $a->id }}" {{ @$userEdit->company_id == $a->id ? 'selected' : '' }}> {{ $a->name }} </option>
                @endforeach
            </select>
            @endif

            @if(!@$userEdit)
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="**********" value="{{ old('password') }}" required>

            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="**********">

            <button id="buttonSubmit" type="submit">Simpan</button>
            @else
            @if(@$userEdit->id == Auth::user()->id)
            <label for="oldPassword">Password Lama:</label>
            <input type="password" id="oldPassword" name="oldPassword" placeholder="**********">

            <label for="newPassword">Password Baru:</label>
            <input type="password" id="newPassword" name="newPassword" placeholder="**********">

            <label for="confirmPassword">Konfirmasi Password:</label>
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="**********">
            @endif
            <button id="buttonSubmit" type="submit">Ubah</button>
            @endif
        </form>
        @endcanAccess

        <!-- Update -->
        @elseif(@$userEdit)
        @canAccess('update','users')
        <form action="{{ route('user.update',$userEdit) }}" method="post">
        @method('put')
            @csrf
            <label for="name">Nama:</label>
            <input type="text" id="name" name="name" placeholder="Anwar" value="{{ old('name') ?? @$userEdit->name }}" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Budiman@gmail.com" value="{{ old('email') ?? @$userEdit->email }}" required>

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" placeholder="08568989080" value="{{ old('phone') ?? @$userEdit->phone }}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/^((0|62)[0-9]*)$/, '$1');" >

            <label for="phone">Divisi:</label>
            <select name="divisions[]" multiple class="form-control select-division">
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" {{ isset($divisionsUser) && in_array($division->id, $divisionsUser) ? 'selected' : '' }}>{{ $division->name }}</option>
                @endforeach
            </select>
            <div class="form-group mt-2 mb-1">
                <label for="checkin-settings">Setting Check-In:</label>
                
                <!-- Check-In Setting -->
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="is_checkin" id="is_checkin" value="1" {{ @$userEdit->is_checkin ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_checkin">Enable Check-In</label>
                </div>

                <!-- Additional Settings - Visible only if is_checkin is enabled -->
                <div id="additionalSettings" style="display: none;">
                    <!-- Manual Check-In -->
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" name="manual_checkin" id="manual_checkin" value="1" {{ @$userEdit->manual_checkin ? 'checked' : '' }}>
                        <label class="form-check-label" for="manual_checkin">Check-In Manual</label>
                    </div>

                    <!-- Require Photo -->
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" name="requires_photo" id="requires_photo" value="1" {{ @$userEdit->requires_photo ? 'checked' : '' }}>
                        <label class="form-check-label" for="requires_photo">Memerlukan Foto Check-In</label>
                    </div>

                    <!-- Require Location -->
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" name="requires_location" id="requires_location" value="1" {{ @$userEdit->requires_location ? 'checked' : '' }}>
                        <label class="form-check-label" for="requires_location">Memerlukan Lokasi Check-In</label>
                    </div>

                    <!-- Time Settings -->
                    <div class="form-group mt-3">
                        <label for="start_time">Jam Kerja:</label>
                        <div class="input-group">
                            <span class="input-group-text">Mulai</span>
                            <input type="time" class="form-control" name="start_time" id="start_time" value="{{ old('start_time') ?? @$userEdit->start_time }}">
                            <span class="input-group-text">Selesai</span>
                            <input type="time" class="form-control" name="end_time" id="end_time" value="{{ old('end_time') ?? @$userEdit->end_time }}">
                        </div>
                    </div>

                    <!-- Rest Time -->
                    <div class="form-group mt-2">
                        <label for="rest_time">Waktu Istirahat:</label>
                        <input type="time" class="form-control" name="rest_time" id="rest_time" value="{{ old('rest_time') ?? @$userEdit->rest_time }}">
                    </div>
                    <div class="form-group mt-4">
                        <label>Custom Rest Times:</label>
                        @foreach($dayofweek as $day => $value)
                            <div class="input-group mt-2">
                                <span class="input-group-text">{{ $day }}</span>
                                <input type="time" class="form-control" name="custom_rest_times[{{ $value }}][start]" 
                                    placeholder="Start" value="{{ old("custom_rest_times.$value.start") ?? @$userEdit->custom_rest_times[$value]['start'] }}">
                                <span class="input-group-text">to</span>
                                <input type="time" class="form-control" name="custom_rest_times[{{ $value }}][end]" 
                                    placeholder="End" value="{{ old("custom_rest_times.$value.end") ?? @$userEdit->custom_rest_times[$value]['end'] }}">
                            </div>
                        @endforeach
                    </div>                    
                </div>
            </div>
            @if($roleAccess)
            <label for="phone">Role:</label>
            <select name="role" class="form-control mb-2 select2" required>
                <option value="" selected disabled>Pilih</option>
                @foreach($role as $a)
                <option value="{{ $a->id }}" {{ @$userEdit->role_id == $a->id ? 'selected' : '' }}> {{ $a->name }} </option>
                @endforeach
            </select>

            <label for="phone">User Persetujuan:</label>
            <select name="approvement_user_id" class="form-control mb-2 user-select2">
                <option value="" selected disabled>Pilih</option>
                @foreach($users as $a)
                <option value="{{ $a->id }}" {{ @$userEdit->approvement_user_id == $a->id ? 'selected' : '' }}> {{ $a->name ." ( ".  $a->company->name." )"}} </option>
                @endforeach
            </select>
            @endif

            @if($companyAccess && !@$userEdit)
            <label for="phone">Company:</label>
            <select name="company" class="form-control mb-2" required>
                <option value="" selected disabled>Pilih</option>
                @foreach($company as $a)
                <option value="{{ $a->id }}" {{ @$userEdit->company_id == $a->id ? 'selected' : '' }}> {{ $a->name }} </option>
                @endforeach
            </select>
            @endif

            @if(!@$userEdit)
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="**********" value="{{ old('password') }}" required>

            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="**********">

            <button id="buttonSubmit" type="submit">Simpan</button>
            @else
            @if(@$userEdit->id == Auth::user()->id)
            <label for="oldPassword">Password Lama:</label>
            <input type="password" id="oldPassword" name="oldPassword" placeholder="**********">

            <label for="newPassword">Password Baru:</label>
            <input type="password" id="newPassword" name="newPassword" placeholder="**********">

            <label for="confirmPassword">Konfirmasi Password:</label>
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="**********">
            @endif
            <button id="buttonSubmit" type="submit">Ubah</button>
            @endif
        </form>
        @endcanAccess
        @endif

    </div>
    <div class="col-md-12 mt-2">
        <h3>Daftar Pengguna</h3>
        <form action="{{ route('user.index') }}" method="get">
            <div class="d-flex flex-row-reverse">
                <div class="p-2">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                </div>
                <div class="p-2">
                    <input type="text" name="email" class="form-control" placeholder="Search">
                </div>
            </div>
        </form>

        <table class="table table-bordered">
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Perusahaan</th>
                <th>Pic Persetujuan</th>
                <th>Aksi</th>
            </tr>
            @forelse($user as $a)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $a->name }}</td>
                <td>{{ $a->email }}</td>
                <td> {{ $a->company ? $a->company->name : '' }} </td>
                <td>{{ $a->approver ? $a->approver->name : "Belum Memiliki Pic Persetujuan" }}</td>
                <td>
                    <form method="post" action="{{ route('user.destroy',$a) }}">
                        @csrf
                        @method('delete')
                        @canAccess('edit','users')
                        <a href="{{ route('user.edit',$a->slug) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                        @endcanAccess
                        @canAccess('destroy','users')
                        <button onclick="return window.confirm('{{ __('Apakah Anda Yakin ? ') }}')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                        @endcanAccess
                    </form>
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="3">
                        <center>Data Kosong</center>
                    </td>
                </tr>
            @endforelse
        </table>

        {{ $user->withQueryString()->links('vendor.pagination.bootstrap-4') }}
    </div>
</div>

@stop


@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Script to toggle dependent options -->
<script>
    function toggleAdditionalSettings() 
    {
        const isCheckinChecked = document.getElementById('is_checkin').checked;
        document.getElementById('additionalSettings').style.display = isCheckinChecked ? 'block' : 'none';

        // Set required attributes for time fields based on is_checkin state
        document.getElementById('start_time').required = isCheckinChecked;
        document.getElementById('end_time').required = isCheckinChecked;
        document.getElementById('rest_time').required = isCheckinChecked;
    }
    // Show/hide additional settings based on "Enable Check-In" checkbox
    document.getElementById('is_checkin').addEventListener('change', toggleAdditionalSettings);

    // Initialize display and required attributes on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleAdditionalSettings();
    });
</script>
<script>
    $(document).ready(function()
    {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih Tugas'
        });

        $('.user-select2').select2({
            width: '100%',
            placeholder: 'Pilih Petugas'
        });

        $('.select-division').select2({
            placeholder: "Select Divisions",
            tags: true
        });
    });
</script>
<script>
    $(document).ready(function ()
    {
        let nomor = "{{ $totalUser }}";
        document.getElementById('penggunaNo').innerHTML = "No Pengguna :"+nomor;


        let getPrice = document.getElementById("budget").value;
        if (getPrice)
        {
            document.getElementById("budget_show").value = getPrice;
            formatRupiahFormat(document.getElementById("budget_show"),"budget"); // Format default value
        }

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
            input.value = 'Rp 0';
            numStr = 0;
        } else {
            // Menghapus angka 0 di depan jika input diawali dengan 0
            rupiah = rupiah.replace(/^0+/, '');
            input.value = rupiah;
        }

        // Update 'salary' input with non-formatted number
        document.getElementById(inputNonFormat).value = parseInt(numStr);
    }
</script>
@stop


@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
        body {
            font-family: Arial, sans-serif;
            /* padding: 20px; */
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            padding: 10px;
            border-radius: 5px;
        }
        input[type="text"], input[type="email"], input[type="password"], input[type="search"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        #buttonSubmit {
            padding: 10px 20px;
            margin-top: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
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

        .select2-selection__choice
        {
            background-color: #007bff !important;
            border: 1px solid #007bff !important;
        }

        .select2-selection__choice__remove
        {
            color: #fe0700 !important;
            border: 1px solid #007bff !important;
        }
</style>
@stop
