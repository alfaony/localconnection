{{--
    Grouped User Select Component
    Variables:
      $selectName      – name attribute, e.g. "users[]" or "user_ids[]"
      $selectId        – id attribute for JS hook
      $groupedUsers    – Collection keyed by division name → collection of users
      $usersNoDivision – Collection of users with no division
      $selectedIds     – array of pre-selected user IDs (default [])
      $placeholder     – optional placeholder string
--}}
@php
    $selectedIds = $selectedIds ?? [];
    $placeholder = $placeholder ?? 'Cari atau pilih karyawan...';
    $totalCount  = $groupedUsers->flatten()->count() + $usersNoDivision->count();
@endphp

{{-- Division pills for quick-select --}}
@if($groupedUsers->isNotEmpty())
<div class="division-pills mb-2 d-flex flex-wrap gap-1">
    @foreach($groupedUsers as $divName => $divUsers)
    <button type="button"
            class="btn division-pill"
            data-select-id="{{ $selectId }}"
            data-division="{{ $divName }}"
            style="font-size:.68rem;padding:3px 10px;border-radius:20px;
                   background:rgba(102,126,234,.12);color:#a5b4fc;
                   border:1px solid rgba(102,126,234,.25);line-height:1.4;transition:all .15s;">
        <i class="fas fa-layer-group me-1" style="font-size:.6rem;"></i>{{ $divName }}
        <span class="pill-count" style="color:#667eea;font-weight:700;margin-left:3px;">({{ $divUsers->count() }})</span>
    </button>
    @endforeach
    @if($usersNoDivision->isNotEmpty())
    <button type="button"
            class="btn division-pill"
            data-select-id="{{ $selectId }}"
            data-division="__no_division__"
            style="font-size:.68rem;padding:3px 10px;border-radius:20px;
                   background:rgba(160,168,208,.1);color:#a0a8d0;
                   border:1px solid rgba(160,168,208,.2);line-height:1.4;transition:all .15s;">
        <i class="fas fa-user me-1" style="font-size:.6rem;"></i>Tanpa Divisi
        <span class="pill-count" style="color:#a0a8d0;font-weight:700;margin-left:3px;">({{ $usersNoDivision->count() }})</span>
    </button>
    @endif
    <button type="button"
            class="btn select-all-pill"
            data-select-id="{{ $selectId }}"
            style="font-size:.68rem;padding:3px 10px;border-radius:20px;
                   background:rgba(56,239,125,.1);color:#38ef7d;
                   border:1px solid rgba(56,239,125,.2);line-height:1.4;transition:all .15s;">
        <i class="fas fa-check-double me-1" style="font-size:.6rem;"></i>Semua
    </button>
    <button type="button"
            class="btn clear-all-pill"
            data-select-id="{{ $selectId }}"
            style="font-size:.68rem;padding:3px 10px;border-radius:20px;
                   background:rgba(248,113,113,.08);color:#f87171;
                   border:1px solid rgba(248,113,113,.2);line-height:1.4;transition:all .15s;">
        <i class="fas fa-times me-1" style="font-size:.6rem;"></i>Kosongkan
    </button>
</div>
@endif

{{-- Select with optgroups --}}
<select name="{{ $selectName }}" id="{{ $selectId }}" class="user-grouped-select" multiple
        data-placeholder="{{ $placeholder }}">
    @foreach($groupedUsers as $divName => $divUsers)
    <optgroup label="{{ $divName }}" data-division="{{ $divName }}">
        @foreach($divUsers as $u)
        <option value="{{ $u->id }}" {{ in_array($u->id, $selectedIds) ? 'selected' : '' }}>
            {{ $u->name }}
        </option>
        @endforeach
    </optgroup>
    @endforeach

    @if($usersNoDivision->isNotEmpty())
    <optgroup label="Tanpa Divisi" data-division="__no_division__">
        @foreach($usersNoDivision as $u)
        <option value="{{ $u->id }}" {{ in_array($u->id, $selectedIds) ? 'selected' : '' }}>
            {{ $u->name }}
        </option>
        @endforeach
    </optgroup>
    @endif
</select>

<div class="d-flex justify-content-between mt-1">
    <small style="color:#55596e;font-size:.68rem;">{{ $totalCount }} karyawan tersedia</small>
    <small id="{{ $selectId }}-count" style="color:#a5b4fc;font-size:.68rem;font-weight:600;">
        {{ count($selectedIds) }} dipilih
    </small>
</div>
