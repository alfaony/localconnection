<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoiceNumber }}</title>
    <style>
        @page {
            margin: 20mm 18mm 20mm 15mm;
            size: A4;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.5;
            padding: 0px 20px 10px 5px; 
        }
        
        .container {
            width: 100%;
            max-width: 100%;
            padding: 8px;
        }
        
        /* Header */
        .header-table {
            width: 100%;
            margin-bottom: 18px;
            border-bottom: 2px solid #db2729;
            padding-bottom: 12px;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #db2729;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }
        
        .company-info {
            font-size: 8.5px;
            color: #666;
            line-height: 1.6;
        }
        
        .company-logo img {
            max-height: 55px;
            max-width: 160px;
        }
        
        /* Invoice Title */
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #db2729;
            letter-spacing: 1.5px;
            margin: 12px 0 15px 0;
            text-align: left;
        }
        
        /* Invoice Meta */
        .invoice-meta-table {
            width: 100%;
            background: #fff5f5;
            border-left: 3px solid #db2729;
            margin-bottom: 18px;
            border-radius: 4px;
        }
        
        .invoice-meta-table td {
            padding: 8px 12px;
            font-size: 9px;
        }
        
        .meta-label {
            font-weight: 600;
            color: #555;
        }
        
        .meta-value {
            font-weight: 500;
            color: #333;
        }
        
        /* Customer Info */
        .info-box {
            width: 100%;
            background: #fafafa;
            border: 1px solid #e5e5e5;
            border-left: 3px solid #db2729;
            margin-bottom: 18px;
            border-radius: 4px;
            page-break-inside: avoid;
        }
        
        .box-title {
            font-size: 11px;
            font-weight: bold;
            color: #db2729;
            padding: 12px 14px 8px 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-table {
            width: 100%;
        }
        
        .info-table td {
            padding: 5px 14px;
            font-size: 9px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            width: 115px;
            vertical-align: top;
        }
        
        .info-value {
            color: #333;
            vertical-align: top;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            border-radius: 4px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        
        .items-table thead {
            background: #db2729;
        }
        
        .items-table th {
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            background: white;
            font-size: 9px;
        }
        
        /* Bottom Section */
        .bottom-table {
            width: 100%;
            margin-bottom: 18px;
            page-break-inside: avoid;
        }
        
        .bottom-table td {
            vertical-align: top;
            padding: 0 6px;
        }
        
        /* Payment Box */
        .payment-box {
            background: #fff5f5;
            border: 1px solid #f0f0f0;
            border-left: 3px solid #db2729;
            padding: 14px;
            border-radius: 4px;
        }
        
        .payment-title {
            font-size: 10px;
            font-weight: bold;
            color: #db2729;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .payment-detail {
            font-size: 8px;
            color: #666;
            margin-top: 6px;
        }
        
        /* Summary Table */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .summary-table td {
            padding: 8px 12px;
            font-size: 9px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .summary-label {
            font-weight: 600;
            color: #555;
            text-align: left;
            width: 60%;
        }
        
        .summary-value {
            font-weight: 500;
            text-align: right;
            color: #333;
            width: 40%;
        }
        
        .tax-row {
            background: #fef3f3;
        }
        
        .total-row {
            background: #db2729;
            border: none;
        }
        
        .total-row td {
            color: white !important;
            font-weight: bold;
            font-size: 10px;
            padding: 12px;
            border: none;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 5px 11px;
            border-radius: 15px;
            font-size: 7.5px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .badge-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }
        
        /* Footer */
        .footer {
            width: 100%;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1.5px solid #e5e5e5;
            text-align: center;
            font-size: 8px;
            color: #999;
            page-break-inside: avoid;
        }
        
        .footer p {
            margin: 3px 0;
            line-height: 1.6;
        }
        
        /* Utilities */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #16a34a; }
        .text-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <table class="header-table" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div class="company-name">{{ $company['internet_company_name'] ?? $company['name'] ?? 'HIKARI NET' }}</div>
                    <div class="company-info">
                        {{ $company['internet_company_address'] ?? $company['address'] ?? 'MOJOSARI RT.001 RW.001 KEL.BEKONANG KEC.MOJOLABAN' }}<br>
                        Telp: {{ $company['internet_phone'] ?? $company['phone'] ?? '084542479990' }} | Email: {{ $company['email'] ?? '' }}
                    </div>
                </td>
                <td style="width: 40%; vertical-align: top; text-align: right;">
                    @if($logoBase64)
                    <div class="company-logo">
                        <img src="{{ $logoBase64 }}" alt="Logo">
                    </div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Invoice Title -->
        <div class="invoice-title">INVOICE</div>
        
        <!-- Invoice Meta -->
        <table class="invoice-meta-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="meta-label" style="width: 25%;">Nomor Invoice:</td>
                <td class="meta-value" style="width: 25%;"><strong>{{ $invoiceNumber }}</strong></td>
                <td class="meta-label" style="width: 20%;">Tanggal:</td>
                <td class="meta-value" style="width: 30%;">{{ $invoiceDate }}</td>
            </tr>
            <tr>
                <td class="meta-label">Status:</td>
                <td colspan="3" class="meta-value">
                    @if($purchase->confirmation_finance_at || $purchase->xendit_paid_at || $purchase->midtrans_paid_at)
                        <span class="badge badge-success">Lunas</span>
                    @else
                        <span class="badge badge-warning">Menunggu Konfirmasi</span>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Customer Information -->
        <div class="info-box">
            <div class="box-title">Informasi Pelanggan</div>
            <table class="info-table" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="info-label">Nama:</td>
                    <td class="info-value">{{ $purchase->customer->name }}</td>
                </tr>
                <tr>
                    <td class="info-label">Kode:</td>
                    <td class="info-value">{{ $purchase->customer->code }}</td>
                </tr>
                <tr>
                    <td class="info-label">Email:</td>
                    <td class="info-value">{{ $purchase->customer->userCustomer->email ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Telepon:</td>
                    <td class="info-value">{{ $purchase->customer->userCustomer->phone_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Alamat:</td>
                    <td class="info-value">
                        {{ $purchase->customer->address }}, {{ $purchase->customer->subdistrict->name ?? '' }}, {{ $purchase->customer->district->name ?? '' }}, {{ $purchase->customer->city->name ?? '' }}, {{ $purchase->customer->province->name ?? '' }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Items Table -->
        <table class="items-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th style="width: 45%;">Deskripsi Paket</th>
                    <th class="text-center" style="width: 15%;">Jumlah Bulan</th>
                    <th class="text-right" style="width: 20%;">Harga/Bulan</th>
                    <th class="text-right" style="width: 20%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-bold">{{ $purchase->customer->internetPackage->name }}</td>
                    <td class="text-center">{{ $purchase->payment_months }}</td>
                    <td class="text-right">Rp {{ number_format($purchase->customer->internetPackage->price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($purchase->total_before_discount ?? ($purchase->customer->internetPackage->price * $purchase->payment_months), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Bottom Section: Payment & Summary -->
        <table class="bottom-table" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 48%;">
                    <div class="payment-box">
                        <div class="payment-title">Metode Pembayaran</div>
                        <div>
                            @if($purchase->payment_method == 'manual_transfer' || $purchase->payment_method == 'transfer')
                                <span class="badge badge-info">Transfer Bank Manual</span>
                            @elseif($purchase->payment_method == 'xendit')
                                <span class="badge badge-success">Xendit</span>
                                @if($purchase->xendit_payment_channel)
                                    <div class="payment-detail">Channel: {{ $purchase->xendit_payment_channel }}</div>
                                @endif
                            @elseif($purchase->payment_method == 'midtrans')
                                <span class="badge badge-warning">Midtrans</span>
                                @if($purchase->midtrans_payment_type)
                                    <div class="payment-detail">Tipe: {{ $purchase->midtrans_payment_type }}</div>
                                @endif
                            @else
                                {{ ucfirst($purchase->payment_method) }}
                            @endif
                            
                            @if($purchase->payment_date)
                                <div class="payment-detail" style="margin-top: 6px;">
                                    <strong>Tanggal Pembayaran:</strong><br>
                                    {{ $purchase->payment_date->format('d M Y H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%;">
                    @php
                        $subtotalAmount = $purchase->total_before_discount ?? ($purchase->customer->internetPackage->price * $purchase->payment_months);
                        $discountAmount = $purchase->discount_amount ?? 0;
                        $amountBeforeTax = $purchase->amount_before_tax ?? ($subtotalAmount - $discountAmount);
                        $taxRate = $purchase->tax_rate ?? 11;
                        $taxAmount = $purchase->tax_amount ?? (int)round(($amountBeforeTax * $taxRate) / 100);
                        $totalAmount = $purchase->amount_paid ?? (int)($amountBeforeTax + $taxAmount);
                    @endphp
                    
                    <table class="summary-table" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="summary-label">Subtotal:</td>
                            <td class="summary-value">Rp {{ number_format($subtotalAmount, 0, ',', '.') }}</td>
                        </tr>
                        @if($discountAmount > 0)
                        <tr>
                            <td class="summary-label">Diskon:</td>
                            <td class="summary-value text-success">- Rp {{ number_format($discountAmount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr class="tax-row">
                            <td class="summary-label">Harga Sebelum Pajak:</td>
                            <td class="summary-value">Rp {{ number_format($amountBeforeTax, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="tax-row">
                            <td class="summary-label">PPN ({{ $taxRate }}%):</td>
                            <td class="summary-value">Rp {{ number_format($taxAmount, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="total-row">
                            <td class="summary-label">TOTAL PEMBAYARAN:</td>
                            <td class="summary-value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ $company['internet_footer_message'] ?? 'Terimakasih Atas Kepercayaan Anda' }}</strong></p>
            <p>{{ $company['internet_company_name'] ?? $company['name'] ?? 'HIKARI NET' }} - {{ $company['website'] ?? '' }}</p>
        </div>
    </div>
</body>
</html>