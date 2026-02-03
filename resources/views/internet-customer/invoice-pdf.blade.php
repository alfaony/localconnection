<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoiceNumber }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            padding: 15px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            background: radial-gradient(circle at center, rgba(219, 39, 41, 0.08) 0%, rgba(219, 39, 41, 0) 80%);
            border-bottom: 3px solid #db2729;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px 8px 0 0;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #db2729;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        .company-info {
            font-size: 10px;
            color: #666;
            line-height: 1.5;
        }
        .invoice-title {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin: 12px 0;
            color: #db2729;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .invoice-meta {
            margin-bottom: 15px;
            background: #f9fafb;
            padding: 10px;
            border-radius: 6px;
            border-left: 4px solid #db2729;
        }
        .invoice-meta table {
            width: 100%;
        }
        .invoice-meta td {
            padding: 3px 0;
        }
        .invoice-meta .label {
            font-weight: 600;
            width: 150px;
            color: #555;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #db2729;
            margin: 12px 0 8px 0;
            padding-bottom: 6px;
            padding-left: 10px;
            border-left: 4px solid #db2729;
            background: linear-gradient(to right, rgba(219, 39, 41, 0.05), transparent);
        }
        .info-table {
            width: 100%;
            margin-bottom: 12px;
            background: #fafafa;
            border-radius: 6px;
            overflow: hidden;
        }
        .info-table td {
            padding: 6px 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-table tr:last-child td {
            border-bottom: none;
        }
        .info-table .label {
            font-weight: 600;
            width: 180px;
            color: #666;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 6px;
            overflow: hidden;
        }
        .items-table th {
            background: linear-gradient(135deg, #db2729 0%, #b21e20 100%);
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #f0f0f0;
            background: #ffffff;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-success {
            color: #16a34a;
        }
        .summary-table {
            width: 380px;
            margin-left: auto;
            margin-top: 25px;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        .summary-table td {
            padding: 6px 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        .summary-table tr:last-child td {
            border-bottom: none;
        }
        .summary-table .label {
            text-align: right;
            font-weight: 600;
            width: 220px;
            color: #555;
        }
        .summary-table .value {
            text-align: right;
            width: 160px;
            font-weight: 500;
        }
        .summary-table .total-row {
            background: linear-gradient(135deg, #db2729 0%, #b21e20 100%);
            color: #2a0e0eff !important;
            font-weight: bold;
            font-size: 14px;
        }
        .summary-table .total-row td {
            padding: 10px 12px;
            border-bottom: none;
            color: #410909ff !important;
        }
        .summary-table .tax-row {
            background: #fef3f3;
        }
        .payment-info {
            background: linear-gradient(135deg, rgba(219, 39, 41, 0.05) 0%, rgba(219, 39, 41, 0.02) 100%);
            padding: 18px;
            border-radius: 6px;
            margin-top: 25px;
            border-left: 4px solid #db2729;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            text-align: center;
            font-size: 10px;
            color: #888;
        }
        .footer p {
            margin: 4px 0;
        }
        .badge {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 14px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .badge-success {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
            border: 1px solid #86efac;
        }
        .badge-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        .badge-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border: 1px solid #fcd34d;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            @if($logoBase64)
                <div style="text-align: center; margin-bottom: 15px;">
                    <img src="{{ $logoBase64 }}" style="max-height: 60px;">
                </div>
            @endif
            <div class="company-name">{{ $company['internet_company_name'] ?? $company['name'] ?? 'PT. Keloola' }}</div>
            <div class="company-info">
                {{ $company['internet_company_address'] ?? $company['address'] ?? '' }}<br>
                Telp: {{ $company['internet_phone'] ?? $company['phone'] ?? '' }} | Email: {{ $company['email'] ?? '' }}
            </div>
        </div>

        <!-- Invoice Title -->
        <div class="invoice-title">INVOICE</div>

        <!-- Invoice Meta -->
        <div class="invoice-meta">
            <table>
                <tr>
                    <td class="label">Nomor Invoice:</td>
                    <td><strong>{{ $invoiceNumber }}</strong></td>
                    <td class="label" style="text-align: right;">Tanggal:</td>
                    <td style="text-align: right;">{{ $invoiceDate }}</td>
                </tr>
                <tr>
                    <td class="label">Status Pembayaran:</td>
                    <td colspan="3">
                        @if($purchase->confirmation_finance_at || $purchase->xendit_paid_at || $purchase->midtrans_paid_at)
                            <span class="badge badge-success">LUNAS</span>
                        @else
                            <span class="badge badge-warning">MENUNGGU KONFIRMASI</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Customer Information -->
        <div class="section-title">Informasi Pelanggan</div>
        <table class="info-table">
            <tr>
                <td class="label">Nama Lengkap:</td>
                <td>{{ $purchase->customer->name }}</td>
            </tr>
            <tr>
                <td class="label">Kode Pelanggan:</td>
                <td>{{ $purchase->customer->code }}</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td>{{ $purchase->customer->userCustomer->email ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">No. Telepon:</td>
                <td>{{ $purchase->customer->userCustomer->phone_number ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Alamat:</td>
                <td>
                    {{ $purchase->customer->address }},
                    {{ $purchase->customer->subdistrict->name ?? '' }},
                    {{ $purchase->customer->district->name ?? '' }},
                    {{ $purchase->customer->city->name ?? '' }},
                    {{ $purchase->customer->province->name ?? '' }}
                </td>
            </tr>
        </table>

        <!-- Package Details -->
        <div class="section-title">Detail Paket & Periode Layanan</div>
        <table class="info-table">
            <tr>
                <td class="label">Paket Internet:</td>
                <td><strong>{{ $purchase->customer->internetPackage->name }}</strong></td>
            </tr>
            <tr>
                <td class="label">Periode Layanan:</td>
                <td>{{ $purchase->period_formatted }}</td>
            </tr>
            <tr>
                <td class="label">Durasi:</td>
                <td>{{ $purchase->payment_months }} bulan</td>
            </tr>
        </table>

        <!-- Pricing Breakdown -->
        <div class="section-title">Rincian Pembayaran</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th class="text-center">Jumlah Bulan</th>
                    <th class="text-right">Harga/Bulan</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $purchase->customer->internetPackage->name }}</td>
                    <td class="text-center">{{ $purchase->payment_months }}</td>
                    <td class="text-right">Rp {{ number_format($purchase->total_before_discount ?? ($purchase->customer->internetPackage->price * $purchase->payment_months), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Summary -->
        <table class="summary-table">
            @php
                // Calculate tax values with fallback for old records
                $subtotalAmount = $purchase->total_before_discount ?? ($purchase->customer->internetPackage->price * $purchase->payment_months);
                $discountAmount = $purchase->discount_amount ?? 0;
                $amountBeforeTax = $purchase->amount_before_tax ?? ($subtotalAmount - $discountAmount);
                $taxRate = $purchase->tax_rate ?? 11;
                $taxAmount = $purchase->tax_amount ?? (int)round(($amountBeforeTax * $taxRate) / 100);
                $totalAmount = $purchase->amount_paid ?? (int)($amountBeforeTax + $taxAmount);
            @endphp
            
            <tr>
                <td class="label">Subtotal:</td>
                <td class="value">Rp {{ number_format($subtotalAmount, 0, ',', '.') }}</td>
            </tr>
            @if($discountAmount > 0)
            <tr>
                <td class="label">Diskon:</td>
                <td class="value text-success">- Rp {{ number_format($discountAmount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="tax-row">
                <td class="label">Harga Sebelum Pajak:</td>
                <td class="value">Rp {{ number_format($amountBeforeTax, 0, ',', '.') }}</td>
            </tr>
            <tr class="tax-row">
                <td class="label">PPN ({{ $taxRate }}%):</td>
                <td class="value">Rp {{ number_format($taxAmount, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td class="label">TOTAL PEMBAYARAN:</td>
                <td class="value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
            </tr>
        </table>

        <!-- Payment Method -->
        <div class="payment-info">
            <strong>Metode Pembayaran:</strong>
            @if($purchase->payment_method == 'manual_transfer' || $purchase->payment_method == 'transfer')
                <span class="badge badge-info">Transfer Bank Manual</span>
            @elseif($purchase->payment_method == 'xendit')
                <span class="badge badge-success">Xendit</span>
                @if($purchase->xendit_payment_channel)
                    <br><small>Channel: {{ $purchase->xendit_payment_channel }}</small>
                @endif
            @elseif($purchase->payment_method == 'midtrans')
                <span class="badge badge-warning">Midtrans</span>
                @if($purchase->midtrans_payment_type)
                    <br><small>Tipe: {{ $purchase->midtrans_payment_type }}</small>
                @endif
            @else
                {{ ucfirst($purchase->payment_method) }}
            @endif
            
            @if($purchase->payment_date)
                <br><small>Tanggal Pembayaran: {{ $purchase->payment_date->format('d M Y H:i') }}</small>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>{{ $company['internet_footer_message'] ?? 'Terima kasih atas kepercayaan Anda menggunakan layanan kami.' }}</p>
            <p>{{ $company['internet_company_name'] ?? $company['name'] ?? 'PT. Keloola' }} - {{ $company['website'] ?? '' }}</p>
        </div>
    </div>
</body>
</html>
