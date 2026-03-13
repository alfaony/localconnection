<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 10px; }
        h2 { font-size: 13px; margin-bottom: 4px; }
        .info { font-size: 10px; color: #555; margin-bottom: 12px; }
        .grid { display: flex; flex-wrap: wrap; gap: 6px; }
        .voucher-card {
            width: 180px;
            border: 1px solid #333;
            border-radius: 4px;
            padding: 8px 10px;
            page-break-inside: avoid;
        }
        .voucher-title { font-size: 9px; font-weight: bold; color: #555; margin-bottom: 4px; text-transform: uppercase; }
        .voucher-profile { font-size: 10px; font-weight: bold; margin-bottom: 6px; color: #111; }
        .label { font-size: 9px; color: #888; }
        .value { font-size: 12px; font-weight: bold; letter-spacing: 1px; color: #111; }
        .divider { border-top: 1px dashed #ccc; margin: 6px 0; }
        .meta { font-size: 9px; color: #777; }
    </style>
</head>
<body>
    <h2>Voucher Hotspot — {{ $batch->hotspotServer->name ?? '-' }}</h2>
    <div class="info">
        Profile: {{ $batch->internetPackage->name ?? '-' }} &nbsp;|&nbsp;
        Durasi: {{ $batch->internetPackage->duration_label ?? '-' }} &nbsp;|&nbsp;
        Quota: {{ $batch->internetPackage->quota_label ?? '-' }} &nbsp;|&nbsp;
        Bandwidth: {{ $batch->internetPackage->rate_down_mbps }}M/{{ $batch->internetPackage->rate_up_mbps }}M &nbsp;|&nbsp;
        Generate: {{ $batch->created_at->format('d/m/Y H:i') }}
    </div>

    <div class="grid">
        @foreach ($batch->vouchers as $voucher)
        <div class="voucher-card">
            <div class="voucher-title">Voucher Hotspot</div>
            <div class="voucher-profile">{{ $batch->internetPackage->name ?? '-' }}</div>
            <div class="label">Username</div>
            <div class="value">{{ $voucher->username }}</div>
            <div class="divider"></div>
            <div class="label">Password</div>
            <div class="value">{{ $voucher->password }}</div>
            <div class="divider"></div>
            <div class="meta">
                @if($batch->internetPackage->session_timeout_seconds > 0)
                    Waktu: {{ $batch->internetPackage->duration_label }}<br>
                @endif
                @if($batch->internetPackage->quota_bytes > 0)
                    Quota: {{ $batch->internetPackage->quota_label }}<br>
                @endif
                Speed: {{ $batch->internetPackage->rate_down_mbps }}M/{{ $batch->internetPackage->rate_up_mbps }}M
            </div>
        </div>
        @endforeach
    </div>
</body>
</html>
