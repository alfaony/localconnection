/**
 * Reusable Leaflet map picker.
 * Requires Leaflet CSS + JS to be loaded before this file.
 *
 * API:
 *   locMapInit(mapId, lat, lng, onUpdate)  – create/hydrate a map instance
 *   locMapDestroy(mapId)                   – remove a map instance (call before re-opening a modal)
 *   locMapGetMyLocation(mapId)             – GPS button handler, called from onclick attribute
 */

window._locMaps = window._locMaps || {};

// options: { autoLocate: bool }
window.locMapInit = function (mapId, lat, lng, onUpdate, options) {
    var el = document.getElementById(mapId);
    if (!el) return;

    // Already initialized: just invalidate size (modal re-open resize fix)
    if (window._locMaps[mapId]) {
        setTimeout(function () {
            window._locMaps[mapId].map.invalidateSize();
        }, 100);
        return;
    }

    var opts = options || {};
    var hasCoords = lat != null && lng != null && lat !== '' && lng !== '';
    var map = L.map(mapId).setView(
        hasCoords ? [lat, lng] : [-2.5489, 118.0149],
        hasCoords ? 15 : 5
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    var marker = null;
    if (hasCoords) {
        marker = _locMakeMarker(mapId, map, parseFloat(lat), parseFloat(lng), onUpdate);
        _locSetDisplay(mapId, parseFloat(lat), parseFloat(lng));
    }

    map.on('click', function (e) {
        var info = window._locMaps[mapId];
        if (!info.marker) {
            info.marker = _locMakeMarker(mapId, map, e.latlng.lat, e.latlng.lng, onUpdate);
        } else {
            info.marker.setLatLng([e.latlng.lat, e.latlng.lng]);
        }
        map.setView([e.latlng.lat, e.latlng.lng], 16);
        _locSetDisplay(mapId, e.latlng.lat, e.latlng.lng);
        if (onUpdate) onUpdate(e.latlng.lat, e.latlng.lng);
    });

    window._locMaps[mapId] = { map: map, marker: marker, onUpdate: onUpdate };

    // Auto-locate: langsung ambil GPS jika tidak ada koordinat tersimpan
    if (!hasCoords && opts.autoLocate && navigator.geolocation) {
        var statusEl = document.getElementById(mapId + '-status');
        if (statusEl) { statusEl.textContent = 'Mendapatkan lokasi...'; statusEl.className = 'text-muted small'; }
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                var aLat = pos.coords.latitude, aLng = pos.coords.longitude;
                var info = window._locMaps[mapId];
                if (!info) return;
                if (!info.marker) {
                    info.marker = _locMakeMarker(mapId, info.map, aLat, aLng, onUpdate);
                } else {
                    info.marker.setLatLng([aLat, aLng]);
                }
                info.map.setView([aLat, aLng], 16);
                _locSetDisplay(mapId, aLat, aLng);
                if (onUpdate) onUpdate(aLat, aLng);
                if (statusEl) { statusEl.textContent = 'Lokasi ditemukan!'; statusEl.className = 'text-success small'; }
            },
            function () {
                if (statusEl) { statusEl.textContent = ''; }
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }
};

window.locMapDestroy = function (mapId) {
    if (window._locMaps[mapId]) {
        window._locMaps[mapId].map.remove();
        delete window._locMaps[mapId];
    }
};

window.locMapGetMyLocation = function (mapId) {
    var statusEl = document.getElementById(mapId + '-status');
    function setStatus(msg, cls) {
        if (statusEl) { statusEl.textContent = msg; statusEl.className = cls + ' small'; }
    }
    if (!navigator.geolocation) { setStatus('Browser tidak mendukung geolokasi.', 'text-danger'); return; }
    setStatus('Mendapatkan lokasi...', 'text-muted');
    navigator.geolocation.getCurrentPosition(
        function (pos) {
            var lat = pos.coords.latitude, lng = pos.coords.longitude;
            var info = window._locMaps[mapId];
            if (!info) return;
            if (!info.marker) {
                info.marker = _locMakeMarker(mapId, info.map, lat, lng, info.onUpdate);
            } else {
                info.marker.setLatLng([lat, lng]);
            }
            info.map.setView([lat, lng], 16);
            _locSetDisplay(mapId, lat, lng);
            if (info.onUpdate) info.onUpdate(lat, lng);
            setStatus('Lokasi ditemukan!', 'text-success');
        },
        function (err) { setStatus('Gagal: ' + err.message, 'text-danger'); },
        { enableHighAccuracy: true, timeout: 10000 }
    );
};

function _locMakeMarker(mapId, map, lat, lng, onUpdate) {
    var m = L.marker([lat, lng], { draggable: true }).addTo(map);
    m.on('dragend', function () {
        var p = m.getLatLng();
        _locSetDisplay(mapId, p.lat, p.lng);
        if (onUpdate) onUpdate(p.lat, p.lng);
    });
    return m;
}

function _locSetDisplay(mapId, lat, lng) {
    var rLat = parseFloat(lat.toFixed(7));
    var rLng = parseFloat(lng.toFixed(7));
    var latEl = document.getElementById(mapId + '-lat');
    var lngEl = document.getElementById(mapId + '-lng');
    var statusEl = document.getElementById(mapId + '-status');
    if (latEl) latEl.value = rLat;
    if (lngEl) lngEl.value = rLng;
    if (statusEl && (!statusEl.textContent || statusEl.textContent === 'Mendapatkan lokasi...')) {
        statusEl.textContent = rLat + ', ' + rLng;
        statusEl.className = 'text-success small';
    }
}
