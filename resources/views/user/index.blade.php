@extends('adminlte::page')

@php
$no = ($user->currentPage() - 1) * $user->perPage() + 1;
$totalUser = $totalUser + 1; // Get the total number of projects

@endphp

@section('content')

<div class="col-md-12">
    @include('components.alert')
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

            <label for="email">Email Gmail:</label>
            <input type="email" id="email" name="email_gmail" placeholder="Budiman@gmail.com" value="{{ old('email_gmail') ?? @$userEdit->email_gmail }}">

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" placeholder="08568989080" value="{{ old('phone') ?? @$userEdit->phone }}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/^((0|62)[0-9]*)$/, '$1');" >

            <label for="phone">Divisi:</label>
            {{--
            <select name="divisions[]" multiple class="form-control select2">
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                @endforeach
            </select>
            --}}
            <table class="table table-bordered" id="divisi-wrapper">
                <thead>
                    <tr>
                        <th>Divisi</th>
                        <th>Wajib Weekly Report</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($divisions as $division)
                    <tr>
                        <td>
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="divisions[]" 
                                    value="{{ $division->id }}"
                                    id="div-check-{{ $division->id }}"
                                    {{ isset($divisionsUser) && in_array($division->id, $divisionsUser) ? 'checked' : '' }}>
                                <label class="form-check-label" for="div-check-{{ $division->id }}">
                                    {{ $division->name }}
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="form-check weekly-wrapper" data-division="{{ $division->id }}" style="display: none;">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="weekly_report_required[{{ $division->id }}]"
                                    id="weekly-check-{{ $division->id }}"
                                    {{ isset($weeklyRequired) && in_array($division->id, $weeklyRequired) ? 'checked' : '' }}>
                                <label class="form-check-label text-muted small" for="weekly-check-{{ $division->id }}">
                                    Wajib Weekly Report
                                </label>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="form-group mt-2">
                <label>Gunakan IP Tertentu:</label>

                <!-- Checkbox Enable IP Filtering -->
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="use_ip_restriction" name="use_ip_restriction" value="1">
                    <label class="form-check-label" for="use_ip_restriction">Aktifkan Restriksi IP</label>
                </div>

                <!-- Container untuk Input IP -->
                <div id="ipRestrictionContainer" class="mt-3" style="display: none;">
                    <button type="button" class="btn btn-success btn-sm mb-2" id="addIpBtn">➕ Tambah IP</button>
                    <div id="ipInputs"></div>
                </div>
            </div>
            <div class="form-group mt-2 mb-1">
                <label for="is_checkin">Metode Absensi</label>
                <!-- Check-In Setting -->
                <div class="form-group">
                    <select name="is_checkin" id="is_checkin" class="form-control">
                        <option value="">-- Pilih Metode Check-In --</option>
                        <option value="wfo" {{ old('is_checkin', @$userEdit->is_checkin ?? '') == 'wfo' ? 'selected' : '' }}>WFO Check-In</option>
                        <option value="wfh" {{ old('is_checkin', @$userEdit->is_checkin ?? '') == 'wfh' ? 'selected' : '' }}>WFH Check-In</option>
                        <option value="shift" {{ old('is_checkin', @$userEdit->is_checkin ?? '') == 'shift' ? 'selected' : '' }}>Shift Kehadiran</option>
                    </select>
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
                        <div class="input-group">
                            <span class="input-group-text">Mulai</span>
                            <input type="time" class="form-control" name="rest_time" id="rest_time" value="{{ old('rest_time') }}">
                            <span class="input-group-text">Selesai</span>
                            <input type="time" class="form-control" name="end_rest_time" id="end_rest_time" value="{{ old('end_rest_time') }}">
                        </div>
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

                <!-- WFO Working Days Settings -->
                <div id="wfoSettings" style="display: none;">
                    <div class="form-group mt-3">
                        <label class="font-weight-bold">Hari Kerja WFO:</label>
                        <div class="border p-3 rounded bg-light">
                            @foreach(config('custom.daysOfWeek') as $dayName => $dayValue)
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="wfo_working_days[{{ $dayValue }}]" 
                                    id="wfo-day-{{ $dayValue }}"
                                    value="1"
                                    {{ old("wfo_working_days.$dayValue", @$userEdit->wfo_working_days[$dayValue] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="wfo-day-{{ $dayValue }}">
                                    {{ $dayName }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mt-2 ">
                <label for="dayoff_active">Setting Cuti</label>
                <div class="form-check">
                    <input type="checkbox" name="dayoff_active" id="dayoff_active" class="form-check-input"
                        {{ old('dayoff_active', $userEdit->dayoff_active ?? false) ? 'checked' : '' }}>
                    <label for="dayoff_active" class="form-check-label">Aktifkan cuti</label>
                </div>
                
                <div id="quota-section" class="{{ old('dayoff_active', $userEdit->dayoff_active ?? false) ? '' : 'd-none' }}">
                    <h5 class="font-weight-bold mt-4">Kuota Cuti</h5>
                    <div class="border p-3 rounded bg-light">
                        @foreach($dayoffTypes as $type)
                            @if($type->is_limited)
                            <div class="form-group">
                                <label>{{ $type->name }}</label>
                                <input type="number"
                                    name="quotas[{{ $type->id }}]"
                                    class="form-control"
                                    min="0"
                                    value="{{ old('quotas.' . $type->id, $userQuotas[$type->id] ?? $type->default_quota) }}">
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            @if($roleAccess)

            <div class="form-group">
                <label for="phone">Role:</label>
                <select name="role" class="form-control mb-2 select2" required>
                    <option value="" selected disabled>Pilih</option>
                    @foreach($role as $a)
                    <option value="{{ $a->id }}" {{ @$userEdit->role_id == $a->id ? 'selected' : '' }}> {{ $a->name }} </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="phone">User Persetujuan:</label>
                <select name="approvement_user_id" class="form-control mb-2 user-select2">
                    <option value="" selected disabled>Pilih</option>
                    @foreach($users as $a)
                    <option value="{{ $a->id }}" {{ @$userEdit->approvement_user_id == $a->id ? 'selected' : '' }}> {{ $a->name ." - ".  $a->company->name }} </option>
                    @endforeach
                </select>
            </div>
            @endif

            @if($companyAccess && !@$userEdit)
            <div class="form-group">
                <label for="phone">Company (Induk):</label>
                <select name="company" class="form-control mb-2" required>
                    <option value="" selected disabled>Pilih</option>
                    @foreach($company as $a)
                    <option value="{{ $a->id }}" {{ @$userEdit->company_id == $a->id ? 'selected' : '' }}> {{ $a->name }} </option>
                    @endforeach
                </select>
            </div>
            @endif
            
            <div class="form-group">
                <label for="phone">Company Access Allow:</label>
                <select name="company_access[]" multiple class="form-control select2">
                    @foreach($company as $a)
                    <option value="{{ $a->id }}" 
                        {{ in_array($a->id, old('company_access', @$userEdit?->accessibleCompanies->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
                        {{ $a->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            @if(!@$userEdit)
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="**********" value="{{ old('password') }}" required>
            </div>

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
        <p id="penggunaNo"></p>
        <form action="{{ route('user.update',$userEdit) }}" method="post">
        @method('put')
            @csrf
            <label for="name">Nama:</label>
            <input type="text" id="name" name="name" placeholder="Anwar" value="{{ old('name') ?? @$userEdit->name }}" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Budiman@gmail.com" value="{{ old('email') ?? @$userEdit->email }}" required>

            <label for="email">Email Gmail:</label>
            <input type="email" id="email" name="email_gmail" placeholder="Budiman@gmail.com" value="{{ old('email_gmail') ?? @$userEdit->email_gmail }}">

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" placeholder="08568989080" value="{{ old('phone') ?? @$userEdit->phone }}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/^((0|62)[0-9]*)$/, '$1');" >

            {{--
            <select name="divisions[]" multiple class="form-control select-division">
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" {{ isset($divisionsUser) && in_array($division->id, $divisionsUser) ? 'selected' : '' }}>{{ $division->name }}</option>
                @endforeach
            </select>
            --}}
            <table class="table table-bordered" id="divisi-wrapper">
                <thead>
                    <tr>
                        <th>Divisi</th>
                        <th>Wajib Weekly Report</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($divisions as $division)
                    <tr>
                        <td>
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="divisions[]" 
                                    value="{{ $division->id }}"
                                    id="div-check-{{ $division->id }}"
                                    {{ isset($divisionsUser) && in_array($division->id, $divisionsUser) ? 'checked' : '' }}>
                                <label class="form-check-label" for="div-check-{{ $division->id }}">
                                    {{ $division->name }}
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="form-check weekly-wrapper" data-division="{{ $division->id }}" style="display: none;">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="weekly_report_required[{{ $division->id }}]"
                                    id="weekly-check-{{ $division->id }}"
                                    {{ isset($weeklyRequired) && in_array($division->id, $weeklyRequired) ? 'checked' : '' }}>
                                <label class="form-check-label text-muted small" for="weekly-check-{{ $division->id }}">
                                    Wajib Weekly Report
                                </label>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="form-group mt-2">
                <label>Gunakan IP Tertentu:</label>

                <!-- Checkbox Enable IP Filtering -->
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="use_ip_restriction" name="use_ip_restriction" value="1" {{ @$userEdit->use_ip_restriction ? 'checked' : '' }}>
                    <label class="form-check-label" for="use_ip_restriction">Aktifkan Restriksi IP</label>
                </div>

                <!-- Container untuk Input IP -->
                <div id="ipRestrictionContainer" class="mt-3" style="display: {{ @$userEdit->use_ip_restriction ? 'block' : 'none' }}">
                    <button type="button" class="btn btn-success btn-sm mb-2" id="addIpBtn">➕ Tambah IP</button>
                    <div id="ipInputs">
                        @if(@$userEdit->ip_addresses)
                            @foreach (@$userEdit->ip_addresses as $ip)
                                <div class="input-group mb-2 ip-input-group">
                                    <input type="text" class="form-control" name="ip_addresses[]" value="{{ $ip }}">
                                    <button type="button" class="btn btn-danger remove-ip ml-2 btn-sm"><i class="fa fa-trash"></i></button>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="form-group mt-2 mb-1">
                <label for="is_checkin">Metode Absensi</label>
                <!-- Check-In Setting -->
                <div class="form-group">
                    <select name="is_checkin" id="is_checkin" class="form-control">
                        <option value="">-- Pilih Metode Check-In --</option>
                        <option value="wfo" {{ @$userEdit->wfo_check_in ? 'selected' : '' }}>WFO Check-In</option>
                        <option value="wfh" {{ @$userEdit->is_checkin ? 'selected' : '' }}>WFH / Hybrid Check-In</option>
                        <option value="shift" {{ @$userEdit->is_shift_attendance ? 'selected' : '' }}>Shift Kehadiran</option>
                    </select>
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
                        <div class="input-group">
                            <span class="input-group-text">Mulai</span>
                            <input type="time" class="form-control" name="rest_time" id="rest_time" value="{{ old('rest_time') ?? @$userEdit->rest_time }}">
                            <span class="input-group-text">Selesai</span>
                            <input type="time" class="form-control" name="end_rest_time" id="end_rest_time" value="{{ old('end_rest_time') ?? @$userEdit->end_rest_time }}">
                        </div>
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

                <div id="wfoSettings" style="display: none;">
                    <div class="form-group mt-3">
                        <label class="font-weight-bold">Hari Kerja WFO:</label>
                        <div class="border p-3 rounded bg-light">
                            @foreach(config('custom.daysOfWeek') as $dayName => $dayValue)
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    name="wfo_working_days[{{ $dayValue }}]" 
                                    id="wfo-day-{{ $dayValue }}"
                                    value="1"
                                    {{ old("wfo_working_days.$dayValue", @$userEdit->wfo_working_days[$dayValue] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="wfo-day-{{ $dayValue }}">
                                    {{ $dayName }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group mt-2 ">
                <label for="dayoff_active">Setting Cuti</label>
                <div class="form-check">
                    <input type="checkbox" name="dayoff_active" id="dayoff_active" class="form-check-input"
                        {{ old('dayoff_active', $userEdit->dayoff_active ?? false) ? 'checked' : '' }}>
                    <label for="dayoff_active" class="form-check-label">Aktifkan cuti</label>
                </div>

                <div id="quota-section" class="{{ old('dayoff_active', $userEdit->dayoff_active ?? false) ? '' : 'd-none' }}">
                    <h5 class="font-weight-bold mt-4">Kuota Cuti</h5>
                    <div class="border p-3 rounded bg-light">
                        @foreach($dayoffTypes as $type)
                            @if($type->is_limited)
                            <div class="form-group">
                                <label>{{ $type->name }}</label>
                                <input type="number"
                                    name="quotas[{{ $type->id }}]"
                                    class="form-control"
                                    min="0"
                                    value="{{ old('quotas.' . $type->id, $userQuotas[$type->id] ?? $type->default_quota) }}">
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="phone">Company Access Allow:</label>
                <select name="company_access[]" multiple class="form-control select2">
                    @foreach($company as $a)
                    <option value="{{ $a->id }}" 
                        {{ in_array($a->id, old('company_access', @$userEdit?->accessibleCompanies->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
                        {{ $a->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @if($roleAccess)
            <label for="phone">Role:</label>
            <select name="role" id="role-select" class="form-control mb-2 select2" required>
                <option value="" selected disabled>Pilih</option>
                @foreach($role as $a)
                <option value="{{ $a->id }}" {{ @$userEdit->role_id == $a->id ? 'selected' : '' }} data-reportmandatory="{{  $a->is_mandatory_report}}"> {{ $a->name }} </option>
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

        @canAccess('KyeExport','kyes')
        <a href="{{ route('kye.KyeExport',request()->all()) }}" class="btn btn-primary">
            <i class="fas fa-file-export"></i> Export Key
        </a>
        @endcanAccess

        <table class="table table-bordered">
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Perusahaan</th>
                <th>Pic Persetujuan</th>
                <th>Cuti</th>
                <th>Aksi</th>
            </tr>
            @forelse($user as $a)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{!! $a->showName !!}</td>
                <td>{{ $a->email }}</td>
                <td> {{ $a->company ? $a->company->name : '' }} </td>
                <td>{{ $a->approver ? $a->approver->name : "Belum Memiliki Pic Persetujuan" }}</td>
                <td>
                @foreach($a->remainingDayoffQuotas() as $quota)
                    <li>{{ $quota['type'] }}: {{ $quota['remaining'] }} hari</li>
                @endforeach
                </td>
                <td>
                    <form method="post" action="{{ route('user.destroy',$a) }}">
                        @csrf
                        @method('delete')
                        @canAccess('index','kyes')
                        @canAccess('show','kyes')
                        @if($a->kye)
                        <a href="{{ route('kye.show', $a->kye->id) }}" class="btn btn-sm btn-warning mb-1" title="Lihat Detail KYE" data-toggle="tooltip" data-placement="top" style="color: white;">
                            <i class="fa fa-file"></i>
                        </a>
                        @endif
                        @endcanAccess
                        @endcanAccess
                        @canAccess('edit_profile_all_user','users')
                        <a href="{{ route('user.profileEdit', $a->slug) }}" class="btn btn-info btn-sm mb-1"><i class="fa fa-user"></i></a>
                        @endcanAccess
                        @canAccess('edit','users')
                        <a href="{{ route('user.edit',$a->slug) }}" class="btn btn-primary btn-sm mb-1"><i class="fa fa-edit"></i></a>
                        @endcanAccess
                        @canAccess('destroy','users')
                        <button onclick="return window.confirm('{{ __('Apakah Anda Yakin ? ') }}')" class="btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></button>
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
    function toggleWeeklyCheckboxVisibility() {
        const selectedRole = document.querySelector('#role-select option:checked');
        const isMandatory = selectedRole?.dataset.reportmandatory ?? false;

        console.log(isMandatory);
        

        document.querySelectorAll('.weekly-wrapper').forEach(wrapper => {
            wrapper.style.display = isMandatory ? 'block' : 'none';
        });

        // Setelah di-toggle tampil, update disable state juga
        updateWeeklyCheckboxState();
    }

    function updateWeeklyCheckboxState() {
        document.querySelectorAll('[id^="div-check-"]').forEach(divCheckbox => {
            const divisionId = divCheckbox.value;
            const weeklyCheckbox = document.querySelector(`#weekly-check-${divisionId}`);

            if (weeklyCheckbox) {
                if (divCheckbox.checked) {
                    weeklyCheckbox.disabled = false;
                } else {
                    weeklyCheckbox.checked = false;
                    weeklyCheckbox.disabled = true;
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Trigger awal
        toggleWeeklyCheckboxVisibility();

        // Saat role berubah
        document.getElementById('role-select').addEventListener('change', function () {
            console.log("changeee");
            
            toggleWeeklyCheckboxVisibility();
        });

        // Saat checkbox divisi berubah
        document.querySelectorAll('[id^="div-check-"]').forEach(divCheckbox => {
            divCheckbox.addEventListener('change', updateWeeklyCheckboxState);
        });

        $('#role-select').on('select2:select', function (e) {
            console.log("change select2");
            
            toggleWeeklyCheckboxVisibility();
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#dayoff_active').on('change', function () {
            if ($(this).is(':checked')) {
                $('#quota-section').removeClass('d-none');
            } else {
                $('#quota-section').addClass('d-none');
            }
        });
    });
</script>
 <script>
    document.getElementById("ipInputs").addEventListener("click", function (e) 
    {
        if (e.target && e.target.classList.contains("remove-ip")) {
            e.target.closest(".ip-input-group").remove();
        }
    })
    document.addEventListener("DOMContentLoaded", function () {
    let ipCheckbox = document.getElementById("use_ip_restriction");
    let ipContainer = document.getElementById("ipRestrictionContainer");
    let ipInputsContainer = document.getElementById("ipInputs");
    let addIpButton = document.getElementById("addIpBtn");

    // Fungsi menambahkan input IP baru
    function addIPInput() {
        let newInput = document.createElement("div");
        newInput.className = "input-group mb-2 ip-input-group";
        newInput.innerHTML = `
            <input type="text" class="form-control" name="ip_addresses[]" placeholder="Masukkan IP Address" required>
            <button type="button" class="btn btn-danger remove-ip ml-2 btn-sm"><i class="fa fa-trash"></i></button>
        `;
        ipInputsContainer.appendChild(newInput);

        // Tambahkan event listener untuk menghapus input
        newInput.querySelector(".remove-ip").addEventListener("click", function () {
            newInput.remove();
            checkRemainingIPs();
        });
    }

    // Cek apakah ada input IP, jika tidak, checkbox otomatis nonaktif
    function checkRemainingIPs() {
        if (ipInputsContainer.children.length === 0) {
            ipCheckbox.checked = false;
            ipContainer.style.display = "none";
        }
    }

    // Event listener untuk checkbox
    ipCheckbox.addEventListener("change", function () {
        if (this.checked) {
            ipContainer.style.display = "block";
            if (ipInputsContainer.children.length === 0) {
                addIPInput(); // Tambahkan satu input saat checkbox diaktifkan
            }
        } else {
            ipContainer.style.display = "none";
            ipInputsContainer.innerHTML = ""; // Hapus semua input jika checkbox dinonaktifkan
        }
    });

    // Event listener untuk tombol tambah input IP
    addIpButton.addEventListener("click", addIPInput);
});
 </script>
<script>
    function toggleAdditionalSettings() 
    {
        const isCheckinValue = document.getElementById('is_checkin').value;
        const showSettings = isCheckinValue === 'wfh';
        document.getElementById('additionalSettings').style.display = showSettings ? 'block' : 'none';

        // Set required attributes for time fields based on is_checkin state
        document.getElementById('start_time').required = showSettings;
        document.getElementById('end_time').required = showSettings;
        document.getElementById('rest_time').required = showSettings;
    }
    // Show/hide additional settings based on "Metode Check-In" select
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

<script>
    function toggleAdditionalSettings() 
    {
        const isCheckinValue = document.getElementById('is_checkin').value;
        const showWfhSettings = isCheckinValue === 'wfh';
        const showWfoSettings = isCheckinValue === 'wfo';
        
        document.getElementById('additionalSettings').style.display = showWfhSettings ? 'block' : 'none';
        document.getElementById('wfoSettings').style.display = showWfoSettings ? 'block' : 'none';

        // Set required attributes for time fields based on is_checkin state
        document.getElementById('start_time').required = showWfhSettings;
        document.getElementById('end_time').required = showWfhSettings;
        document.getElementById('rest_time').required = showWfhSettings;
    }
    
    // Show/hide additional settings based on "Metode Check-In" select
    document.getElementById('is_checkin').addEventListener('change', toggleAdditionalSettings);

    // Initialize display and required attributes on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleAdditionalSettings();
    });
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
