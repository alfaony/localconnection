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
            <input type="text" id="name" name="name" placeholder="Anwar" value="{{ old('name') }}" required autocomplete="off">

            @canAccess('search','user_blacklists')
            <div id="blacklist-alert" style="display:none;" class="mt-2 mb-3">
                <div class="bl-warning-header">
                    <i class="fas fa-ban mr-2"></i>
                    <span>Peringatan! Nama ini cocok dengan data Blacklist</span>
                    <span id="bl-count-badge" class="bl-count-badge"></span>
                </div>
                <div id="blacklist-results"></div>
            </div>
            @endcanAccess

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Budiman@gmail.com" value="{{ old('email') }}" required>

            @if($roleAccess)
            <div class="form-group mt-2">
                <label for="role">Role:</label>
                <select name="role" id="role-select" class="form-control mb-2 select2" required>
                    <option value="" selected disabled>Pilih</option>
                    @foreach($role as $a)
                    <option value="{{ $a->id }}" {{ old('role') == $a->id ? 'selected' : '' }} data-reportmandatory="{{ $a->is_mandatory_report }}"> {{ $a->name ?? '' }} </option>
                    @endforeach
                </select>
            </div>
            @endif

            @if($companyAccess)
            <div class="form-group">
                <label for="company">Company:</label>
                <select name="company" class="form-control mb-2" required>
                    <option value="" selected disabled>Pilih</option>
                    @foreach($company as $a)
                    <option value="{{ $a->id }}" {{ old('company') == $a->id ? 'selected' : '' }}> {{ $a->name ?? '' }} </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="form-group mt-2">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="**********" required>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="**********" required>
            </div>

            <button id="buttonSubmit" type="submit">Simpan</button>
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

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" placeholder="08568989080" value="{{ old('phone') ?? @$userEdit->phone }}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/^((0|62)[0-9]*)$/, '$1');" >

            {{-- 
            <div class="form-group">
                <label for="phone">Company Access Allow:</label>
                <select name="company_access[]" multiple class="form-control select2">
                    @foreach($company as $a)
                    <option value="{{ $a->id }}" 
                        {{ in_array($a->id, old('company_access', @$userEdit?->accessibleCompanies->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
                        {{ $a->name ?? '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            --}}
            @if($roleAccess)
            <label for="phone">Role:</label>
            <select name="role" id="role-select" class="form-control mb-2 select2" required>
                <option value="" selected disabled>Pilih</option>
                @foreach($role as $a)
                <option value="{{ $a->id }}" {{ @$userEdit->role_id == $a->id ? 'selected' : '' }} data-reportmandatory="{{  $a->is_mandatory_report}}"> {{ $a->name ?? '' }} </option>
                @endforeach
            </select>
            @endif

            @if($companyAccess && !@$userEdit)
            <label for="phone">Company:</label>
            <select name="company" class="form-control mb-2" required>
                <option value="" selected disabled>Pilih</option>
                @foreach($company as $a)
                <option value="{{ $a->id }}" {{ @$userEdit->company_id == $a->id ? 'selected' : '' }}> {{ $a->name ?? '' }} </option>
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
                <th>Aksi</th>
            </tr>
            @forelse($user as $a)
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $a->name }}</td>
                <td>{{ $a->email }}</td>
                <td>{{ $a->company ? $a->company->name : '' }}</td>
                <td>{{ $a->approver ? $a->approver->name : 'Belum Memiliki Pic Persetujuan' }}</td>
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
                        @if($a->delete_able)
                        @canAccess('destroy','users')
                        <button onclick="return window.confirm('{{ __('Apakah Anda Yakin ? ') }}')" class="btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></button>
                        @endcanAccess
                        @endif
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
    document.addEventListener("DOMContentLoaded", function () {
    const ipInputsEl = document.getElementById("ipInputs");
    if (ipInputsEl) {
        ipInputsEl.addEventListener("click", function (e) {
            if (e.target && e.target.classList.contains("remove-ip")) {
                e.target.closest(".ip-input-group").remove();
            }
        });
    }
    (function () {
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
    if (addIpButton) addIpButton.addEventListener("click", addIPInput);
    })();
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
    document.addEventListener('DOMContentLoaded', function() {
        const isCheckinEl = document.getElementById('is_checkin');
        if (!isCheckinEl) return;

        function toggleAdditionalSettings() {
            const isCheckinValue = isCheckinEl.value;
            const showWfhSettings = isCheckinValue === 'wfh';
            const showWfoSettings = isCheckinValue === 'wfo';

            document.getElementById('additionalSettings').style.display = showWfhSettings ? 'block' : 'none';
            document.getElementById('wfoSettings').style.display = showWfoSettings ? 'block' : 'none';

            document.getElementById('start_time').required = showWfhSettings;
            document.getElementById('end_time').required = showWfhSettings;
            document.getElementById('rest_time').required = showWfhSettings;
        }

        isCheckinEl.addEventListener('change', toggleAdditionalSettings);
        toggleAdditionalSettings();
    });
</script>

@canAccess('search','user_blacklists')
<script>
// ===== Blacklist check saat mengetik nama (form create user) =====
(function () {
    const nameInput  = document.getElementById('name');
    if (!nameInput) return;

    @if(@$userEdit) return; @endif

    const alertBox   = document.getElementById('blacklist-alert');
    const resultsBox = document.getElementById('blacklist-results');
    const countBadge = document.getElementById('bl-count-badge');
    const searchUrl  = '{{ route("user-blacklist.search") }}';

    let debounceTimer = null;

    function esc(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function buildCard(p) {
        // Avatar
        const avatar = p.avatar_url
            ? `<img src="${esc(p.avatar_url)}" class="bl-avatar" alt="foto">`
            : `<div class="bl-avatar-placeholder"><i class="fas fa-user"></i></div>`;

        // Company tag
        const company = p.company_name
            ? `<span class="bl-company-tag"><i class="fas fa-building mr-1"></i>${esc(p.company_name)}</span>`
            : '';

        // Detail fields grid
        const fields = [
            p.email   ? `<div class="bl-field"><i class="fas fa-envelope"></i><span>${esc(p.email)}</span></div>`           : '',
            p.phone   ? `<div class="bl-field"><i class="fas fa-phone"></i><span>${esc(p.phone)}</span></div>`               : '',
            p.id_card ? `<div class="bl-field"><i class="fas fa-id-card"></i><span>KTP: ${esc(p.id_card)}</span></div>`      : '',
            p.address ? `<div class="bl-field"><i class="fas fa-map-marker-alt"></i><span>${esc(p.address)}</span></div>`    : '',
        ].filter(Boolean).join('');

        // Reason box
        const reason = p.reason
            ? `<div class="bl-reason-box mt-2">
                <i class="fas fa-exclamation-circle"></i>
                <span><strong>Alasan:</strong> ${esc(p.reason)}</span>
               </div>`
            : '';

        return `
        <div class="bl-person-card">
            <div class="bl-avatar-wrap">
                ${avatar}
                <div class="bl-ban-stamp"><i class="fas fa-ban"></i></div>
            </div>
            <div class="bl-body">
                <div class="bl-name-row">
                    <span class="bl-name">${esc(p.name)}</span>
                    <span class="bl-badge"><i class="fas fa-ban mr-1"></i>BLACKLIST</span>
                    ${company}
                </div>
                ${fields ? `<div class="bl-grid">${fields}</div>` : ''}
                ${reason}
            </div>
        </div>`;
    }

    nameInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const val = this.value.trim();

        if (val.length < 2) {
            alertBox.style.display = 'none';
            resultsBox.innerHTML   = '';
            return;
        }

        debounceTimer = setTimeout(function () {
            fetch(searchUrl + '?name=' + encodeURIComponent(val), {
                headers: { 'Accept': 'application/json' }
            })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (data) {
                if (!Array.isArray(data) || data.length === 0) {
                    alertBox.style.display = 'none';
                    resultsBox.innerHTML   = '';
                    return;
                }

                countBadge.textContent   = data.length + ' data ditemukan';
                resultsBox.innerHTML     = data.map(buildCard).join('');
                alertBox.style.display   = 'block';
            })
            .catch(function () {
                alertBox.style.display = 'none';
                resultsBox.innerHTML   = '';
            });
        }, 400);
    });
})();
</script>
@endcanAccess
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

        /* Division Induk Badge */
        .division-assign-table td { vertical-align: middle; }
        .primary-division-label { cursor: pointer; }
        .badge-induk {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 600;
            transition: all 0.15s ease;
        }
        .badge-induk-off {
            background-color: #e9ecef;
            color: #6c757d;
            border: 1px dashed #adb5bd;
        }
        .badge-induk-off:hover {
            background-color: #fff3cd;
            color: #856404;
            border-color: #ffc107;
        }
        .badge-induk-on { display: none; background-color: #ffc107; color: #212529; border: 1px solid #e0a800; }
        .primary-division-radio:checked + .primary-division-label .badge-induk-off { display: none; }
        .primary-division-radio:checked + .primary-division-label .badge-induk-on { display: inline-block; }

        /* ===== Blacklist Alert Styles ===== */
        .bl-warning-header {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            color: #fff;
            font-weight: 700;
            font-size: 0.88rem;
            padding: 10px 14px;
            border-radius: 8px 8px 0 0;
            letter-spacing: 0.3px;
        }
        .bl-count-badge {
            margin-left: auto;
            background: rgba(255,255,255,0.25);
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        #blacklist-results {
            border: 2px solid #e74c3c;
            border-top: none;
            border-radius: 0 0 8px 8px;
            background: #fff;
            overflow: hidden;
        }
        .bl-person-card {
            display: flex;
            align-items: flex-start;
            padding: 12px 14px;
            border-bottom: 1px solid #fde8e8;
            transition: background 0.15s;
        }
        .bl-person-card:last-child { border-bottom: none; }
        .bl-person-card:hover { background: #fff8f8; }
        .bl-avatar-wrap {
            flex-shrink: 0;
            margin-right: 14px;
            position: relative;
        }
        .bl-avatar {
            width: 62px; height: 62px; object-fit: cover;
            border-radius: 50%;
            border: 3px solid #e74c3c;
            box-shadow: 0 2px 8px rgba(231,76,60,.3);
        }
        .bl-avatar-placeholder {
            width: 62px; height: 62px; border-radius: 50%;
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(231,76,60,.3);
        }
        .bl-avatar-placeholder i { color: #fff; font-size: 1.4rem; }
        .bl-ban-stamp {
            position: absolute; bottom: -4px; right: -4px;
            background: #c0392b; border-radius: 50%;
            width: 20px; height: 20px;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid #fff;
        }
        .bl-ban-stamp i { color: #fff; font-size: 0.6rem; }
        .bl-body { flex: 1; min-width: 0; }
        .bl-name-row {
            display: flex; align-items: center; flex-wrap: wrap; gap: 6px;
            margin-bottom: 6px;
        }
        .bl-name {
            font-weight: 700; font-size: 0.97rem; color: #c0392b;
        }
        .bl-badge {
            display: inline-flex; align-items: center;
            background: #c0392b; color: #fff;
            font-size: 0.67rem; font-weight: 700; letter-spacing: 0.5px;
            padding: 2px 8px; border-radius: 20px;
        }
        .bl-company-tag {
            display: inline-flex; align-items: center;
            background: #fde8e8; color: #922b21;
            font-size: 0.72rem; padding: 2px 8px; border-radius: 20px;
            border: 1px solid #f5c6cb;
        }
        .bl-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 4px 16px;
            margin-bottom: 6px;
        }
        .bl-field {
            display: flex; align-items: flex-start; gap: 5px;
            font-size: 0.78rem; color: #555; line-height: 1.4;
        }
        .bl-field i { color: #e74c3c; margin-top: 2px; flex-shrink: 0; font-size: 0.72rem; }
        .bl-reason-box {
            display: flex; align-items: flex-start; gap: 6px;
            background: #fdf0ef; border: 1px solid #f5b7b1;
            border-radius: 6px; padding: 6px 10px;
            font-size: 0.78rem; color: #922b21;
        }
        .bl-reason-box i { flex-shrink: 0; margin-top: 2px; }
</style>
@stop
