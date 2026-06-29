{{--
    Shared map picker partial.
    Variables (set by caller via @include):
      $mapId   (string)  unique DOM id, e.g. 'reg-map' or 'admin-edit-map'
      $height  (string)  CSS height, default '260px'
      $btnClass (string) button classes, default 'btn btn-outline-primary btn-sm'
--}}
@php
    $mapId    = $mapId    ?? 'location-map';
    $height   = $height   ?? '260px';
    $btnClass = $btnClass ?? 'btn btn-outline-primary btn-sm';
@endphp

<div class="d-flex align-items-center mb-2" style="gap:.5rem;">
    <button type="button" class="{{ $btnClass }}"
            onclick="locMapGetMyLocation('{{ $mapId }}')">
        <i class="fas fa-map-marker-alt"></i>&nbsp;Lokasi Saya
    </button>
    <span id="{{ $mapId }}-status" class="text-muted small"></span>
</div>

<div wire:ignore>
    <div id="{{ $mapId }}" style="height:{{ $height }}; border-radius:6px; border:1px solid #dee2e6;"></div>
</div>

<div class="row mt-2 g-1">
    <div class="col-6">
        <input type="number" step="any" id="{{ $mapId }}-lat"
               class="form-control form-control-sm"
               placeholder="Latitude" readonly>
    </div>
    <div class="col-6">
        <input type="number" step="any" id="{{ $mapId }}-lng"
               class="form-control form-control-sm"
               placeholder="Longitude" readonly>
    </div>
</div>
<small class="text-muted d-block mt-1">Klik "Lokasi Saya" atau klik langsung pada peta.</small>
