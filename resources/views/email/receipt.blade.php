<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembelian - {{ $data['transaction_code'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .receipt-title {
            font-size: 18px;
            color: #666;
            margin-bottom: 10px;
        }
        .transaction-code {
            font-size: 16px;
            font-weight: bold;
            background-color: #f8f9fa;
            padding: 8px 15px;
            border-radius: 5px;
            display: inline-block;
        }
        .greeting {
            margin-bottom: 20px;
            font-size: 16px;
        }
        .info-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-weight: 600;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .items-section {
            margin: 30px 0;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
        }
        .item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .item:last-child {
            border-bottom: none;
        }
        .item-name {
            font-weight: bold;
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }
        .item-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .item-qty-price {
            color: #666;
            font-size: 14px;
        }
        .item-subtotal {
            font-weight: bold;
            color: #007bff;
            font-size: 16px;
        }
        .totals-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }
        .total-row:last-child {
            margin-bottom: 0;
        }
        .subtotal-row {
            color: #666;
        }
        .tax-row {
            color: #666;
        }
        .grand-total {
            border-top: 2px solid #007bff;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }
        .payment-section {
            background-color: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .payment-method {
            font-weight: bold;
            color: #28a745;
            margin-bottom: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .thank-you {
            font-size: 16px;
            color: #007bff;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .contact-info {
            margin-top: 20px;
            font-size: 12px;
            color: #999;
        }
        
        @media (max-width: 600px) {
            .email-container {
                padding: 20px;
            }
            .item-details {
                flex-direction: column;
                align-items: flex-start;
            }
            .item-subtotal {
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="company-name">{{ $data['company_name'] }}</div>
            <div class="receipt-title">STRUK PEMBELIAN</div>
            <div class="transaction-code">{{ $data['transaction_code'] }}</div>
        </div>

        <div class="greeting">
            Terima kasih atas pembelian Anda!
        </div>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Tanggal & Waktu:</span>
                <span class="info-value">{{ $data['created_at'] }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Kasir:</span>
                <span class="info-value">{{ $data['kasir_name'] }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Kode Transaksi:</span>
                <span class="info-value">{{ $data['transaction_code'] }}</span>
            </div>
        </div>
        <div class="items-section">
            <div class="section-title">Detail Pembelian</div>
            @foreach($data['items'] as $item)
            <div class="item">
                <div class="item-name">{{ $item->productStore->name }}</div>
                <div class="item-details">
                    <div class="item-qty-price">
                        {{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        <br>
                        <small>SKU: {{ $item->productStore->code }}</small>
                    </div>
                    <div class="item-subtotal">
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="totals-section">
            <div class="total-row subtotal-row">
                <span>Subtotal:</span>
                <span>Rp {{ number_format($data['total_amount'], 0, ',', '.') }}</span>
            </div>
            <div class="total-row tax-row">
                <span>Pajak ({{ $data['sale']->tax_value }}%):</span>
                <span>Rp {{ number_format($data['tax_amount'], 0, ',', '.') }}</span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($data['final_amount'], 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="payment-section">
            <div class="payment-method">
                Metode Pembayaran: 
                @if($data['payment_method'] === 'cash')
                    Tunai
                @elseif($data['payment_method'] === 'debit_credit')
                    Kartu Debit/Kredit
                @elseif($data['payment_method'] === 'qris')
                    QRIS
                @else
                    {{ $data['payment_method'] }}
                @endif
            </div>
            
            @if($data['payment_method'] === 'cash' && isset($data->payment_details['cash_amount']))
            <div class="total-row">
                <span>Dibayar:</span>
                <span>Rp {{ number_format($data->payment_details['cash_amount'], 0, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span>Kembalian:</span>
                <span>Rp {{ number_format($data->payment_details['cash_amount'] - $data->final_amount, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>

        <div class="footer">
            <div class="thank-you">Terima kasih atas kunjungan Anda!</div>
            <p>Simpan email ini sebagai bukti pembelian Anda.</p>
            <p>Barang yang sudah dibeli tidak dapat dikembalikan kecuali ada kesepakatan khusus.</p>
            
            <div class="contact-info">
                Email ini dikirim secara otomatis dari sistem Point of Sale {{ $data['company_name'] }}.
                <br>
                Jika Anda memiliki pertanyaan, silakan hubungi toko kami.
            </div>
        </div>
    </div>
</body>
</html>