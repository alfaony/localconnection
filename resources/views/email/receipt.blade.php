<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembelian - {{ $data['transaction_code'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #2d3748;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        /* Header with Image */
        .header {
            position: relative;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px;
            color: white;
        }
        
        .header-image {
            max-width: 180px;
            height: auto;
            margin: 0 auto 20px;
            border-radius: 12px;
            background: white;
            padding: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .header-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .company-name {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .receipt-title {
            font-size: 16px;
            font-weight: 500;
            opacity: 0.95;
            letter-spacing: 2px;
            margin-bottom: 15px;
        }
        
        .transaction-code {
            display: inline-block;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            padding: 10px 24px;
            border-radius: 25px;
            font-size: 15px;
            font-weight: 600;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Content Area */
        .content {
            padding: 35px 30px;
        }
        
        .greeting {
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        /* Info Section */
        .info-section {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        }
        
        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
        }
        
        .info-value {
            color: #2d3748;
            font-weight: 500;
            text-align: right;
        }
        
        /* Items Section */
        .items-section {
            margin: 30px 0;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #667eea;
            display: flex;
            align-items: center;
        }
        
        .section-title::before {
            content: "🛍️";
            margin-right: 10px;
            font-size: 24px;
        }
        
        .item {
            background: #f7fafc;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 12px;
            border-left: 4px solid #667eea;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .item:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }
        
        .item-name {
            font-weight: 700;
            font-size: 16px;
            color: #2d3748;
            margin-bottom: 8px;
        }
        
        .item-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .item-qty-price {
            color: #718096;
            font-size: 14px;
            font-weight: 500;
        }
        
        .item-subtotal {
            font-weight: 700;
            color: #667eea;
            font-size: 17px;
        }
        
        /* Totals Section */
        .totals-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 15px;
        }
        
        .subtotal-row, .tax-row {
            opacity: 0.9;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .grand-total {
            border-top: 2px solid rgba(255, 255, 255, 0.5);
            padding-top: 15px;
            margin-top: 10px;
            font-size: 22px;
            font-weight: 700;
        }
        
        /* Payment Section */
        .payment-section {
            background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);
            padding: 20px;
            border-radius: 12px;
            margin: 25px 0;
            border: 2px solid #68d391;
        }
        
        .payment-method {
            font-weight: 700;
            color: #22543d;
            margin-bottom: 12px;
            font-size: 16px;
            display: flex;
            align-items: center;
        }
        
        .payment-method::before {
            content: "💳";
            margin-right: 8px;
            font-size: 20px;
        }
        
        .payment-section .total-row {
            color: #22543d;
            font-weight: 600;
            border-bottom: 1px solid rgba(34, 84, 61, 0.2);
        }
        
        .payment-section .total-row:last-child {
            border-bottom: none;
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 30px;
            text-align: center;
            border-top: 3px solid #667eea;
        }
        
        .thank-you {
            font-size: 20px;
            color: #667eea;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .footer-message {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 2px dashed #cbd5e0;
            color: #4a5568;
            font-size: 15px;
            line-height: 1.8;
        }
        
        .footer p {
            color: #718096;
            font-size: 14px;
            margin: 8px 0;
        }
        
        .contact-info {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 13px;
            color: #a0aec0;
            line-height: 1.6;
        }
        
        /* Responsive Design */
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .content {
                padding: 25px 20px;
            }
            
            .company-name {
                font-size: 26px;
            }
            
            .header-image {
                max-width: 150px;
            }
            
            .item-details {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .item-subtotal {
                margin-top: 8px;
            }
            
            .info-row {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .info-value {
                margin-top: 4px;
                text-align: left;
            }
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .email-container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header Section -->
        <div class="header">
            @if(isset($data['header_store_image']) && $data['header_store_image'])
            <div class="header-image">
                <img src="{{ $data['header_store_image'] }}" alt="{{ $data['store_name'] }} Logo">
            </div>
            @endif
            
            <div class="company-name">{{ $data['store_name'] }}</div>
            <div class="receipt-title">STRUK PEMBELIAN</div>
            <div class="transaction-code">{{ $data['transaction_code'] }}</div>
        </div>

        <!-- Content Section -->
        <div class="content">
            <div class="greeting">
                Terima kasih atas pembelian Anda! 🎉
            </div>

            <!-- Transaction Info -->
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">📅 Tanggal & Waktu:</span>
                    <span class="info-value">{{ $data['created_at'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">👤 Kasir:</span>
                    <span class="info-value">{{ $data['kasir_name'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">🔖 Kode Transaksi:</span>
                    <span class="info-value">{{ $data['transaction_code'] }}</span>
                </div>
            </div>

            <!-- Items Section -->
            <div class="items-section">
                <div class="section-title">Detail Pembelian</div>
                @foreach($data['items'] as $item)
                <div class="item">
                    <div class="item-name">{{ $item->productStore->name }}</div>
                    <div class="item-details">
                        <div class="item-qty-price">
                            {{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        </div>
                        <div class="item-subtotal">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Totals Section -->
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

            <!-- Payment Section -->
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
        </div>

        <!-- Footer Section -->
        <div class="footer">
            <div class="thank-you">Terima Kasih! 🙏</div>
            
            @if(isset($data['footer_store_message']) && $data['footer_store_message'])
            <div class="footer-message">
                {!! $data['footer_store_message'] !!}
            </div>
            @endif
            
            <p>📧 Simpan email ini sebagai bukti pembelian Anda.</p>
            <p>⚠️ Barang yang sudah dibeli tidak dapat dikembalikan kecuali ada kesepakatan khusus.</p>
            
            <div class="contact-info">
                Email ini dikirim secara otomatis dari sistem Point of Sale {{ $data['company_name'] }}.
                <br>
                Jika Anda memiliki pertanyaan, silakan hubungi toko kami.
            </div>
        </div>
    </div>
</body>
</html>