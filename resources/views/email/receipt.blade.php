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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1a202c;
            background: #f8fafc;
            padding: 40px 20px;
        }
        
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 10px 20px rgba(0, 0, 0, 0.05);
        }
        
        /* Minimalist Header */
        .header {
            background: #ffffff;
            padding: 48px 40px 32px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .logo-container {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            background: #f7fafc;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e2e8f0;
        }
        
        .logo-container img {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }
        
        .logo-fallback {
            font-size: 40px;
            color: #64748b;
        }
        
        .company-title {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        
        .store-address {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 12px;
            line-height: 1.6;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .header-subtitle {
            font-size: 13px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
        }
        
        /* Transaction Badge */
        .transaction-header {
            background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
            padding: 32px 40px;
            text-align: center;
        }
        
        .transaction-id {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            color: white;
            letter-spacing: 0.5px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 16px;
        }
        
        .transaction-date {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Content Area */
        .content {
            padding: 40px;
        }
        
        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 32px;
        }
        
        .info-card {
            background: #f8fafc;
            padding: 16px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        
        .info-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            font-weight: 600;
        }
        
        .info-value {
            font-size: 15px;
            color: #0f172a;
            font-weight: 600;
        }
        
        /* Items List */
        .items-header {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
            font-weight: 700;
        }
        
        .item-row {
            padding: 20px 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .item-row:last-child {
            border-bottom: none;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 6px;
        }
        
        .item-meta {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }
        
        .item-price {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            margin-left: 20px;
        }
        
        /* Summary Section */
        .summary-section {
            background: #f8fafc;
            padding: 24px;
            border-radius: 12px;
            margin-top: 32px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
        }
        
        .summary-row.subtotal {
            color: #64748b;
            font-weight: 500;
        }
        
        .summary-row.tax {
            color: #64748b;
            font-weight: 500;
            padding-bottom: 16px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .summary-row.total {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            padding-top: 16px;
        }
        
        /* Payment Info */
        .payment-info {
            margin-top: 32px;
            padding: 24px;
            background: linear-gradient(135deg, #ecfeff 0%, #e0f2fe 100%);
            border-radius: 12px;
            border: 1px solid #bae6fd;
        }
        
        .payment-method-label {
            font-size: 12px;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .payment-method-value {
            font-size: 16px;
            color: #0c4a6e;
            font-weight: 700;
            margin-bottom: 16px;
        }
        
        .payment-details {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            color: #0c4a6e;
            font-weight: 600;
        }
        
        /* Footer */
        .footer {
            background: #f8fafc;
            padding: 40px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer-message {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            color: #475569;
            font-size: 14px;
            line-height: 1.8;
            border: 1px solid #e2e8f0;
        }
        
        .footer-notes {
            font-size: 13px;
            color: #64748b;
            line-height: 1.8;
        }
        
        .footer-notes p {
            margin: 8px 0;
        }
        
        .divider {
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, #0ea5e9, #6366f1);
            margin: 24px auto;
            border-radius: 2px;
        }
        
        /* Responsive */
        @media (max-width: 600px) {
            body {
                padding: 20px 10px;
            }
            
            .content {
                padding: 32px 24px;
            }
            
            .header {
                padding: 40px 24px 24px;
            }
            
            .transaction-header {
                padding: 24px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .item-row {
                flex-direction: column;
            }
            
            .item-price {
                margin-left: 0;
                margin-top: 8px;
            }
            
            .footer {
                padding: 32px 24px;
            }
        }
        
        /* Print */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .email-wrapper {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="header">
            <div class="logo-container">
                @if(isset($data['header_store_image']) && $data['header_store_image'])
                    <img src="{{ $data['header_store_image_url'] }}" alt="{{ $data['store_name'] }}">
                @else
                    <div class="logo-fallback">&#x1F3EA;</div>
                @endif
            </div>
            <div class="company-title">{{ $data['store_name'] }}</div>
            @if(isset($data['store_address']) && $data['store_address'])
            <div class="store-address">{{ $data['store_address'] }}</div>
            @endif
            <div class="header-subtitle">Struk Pembelian</div>
        </div>
        
        <!-- Transaction Header -->
        <div class="transaction-header">
            <div class="transaction-id">{{ $data['transaction_code'] }}</div>
            <div class="transaction-date">{{ $data['created_at'] }}</div>
        </div>
        
        <!-- Content -->
        <div class="content">
            <!-- Info Grid -->
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">Kasir</div>
                    <div class="info-value">{{ $data['kasir_name'] }}</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Metode Pembayaran</div>
                    <div class="info-value">
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
                </div>
            </div>
            
            <!-- Items -->
            <div class="items-header">Daftar Pembelian</div>
            
            @foreach($data['items'] as $item)
            <div class="item-row">
                <div class="item-info">
                    <div class="item-name">{{ $item->productStore->name }}</div>
                    <div class="item-meta">{{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</div>
                </div>
                <div class="item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
            </div>
            @endforeach
            
            <!-- Summary -->
            <div class="summary-section">
                <div class="summary-row subtotal">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($data['total_amount'], 0, ',', '.') }}</span>
                </div>
                <div class="summary-row tax">
                    <span>Pajak ({{ $data['sale']->tax_value }}%)</span>
                    <span>Rp {{ number_format($data['tax_amount'], 0, ',', '.') }}</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>Rp {{ number_format($data['final_amount'], 0, ',', '.') }}</span>
                </div>
            </div>
            
            <!-- Payment Details -->
            @if($data['payment_method'] === 'cash' && isset($data->payment_details['cash_amount']))
            <div class="payment-info">
                <div class="payment-method-label">Pembayaran Tunai</div>
                <div class="payment-method-value">Tunai</div>
                <div class="payment-details">
                    <span>Dibayar</span>
                    <span>Rp {{ number_format($data->payment_details['cash_amount'], 0, ',', '.') }}</span>
                </div>
                <div class="payment-details">
                    <span>Kembalian</span>
                    <span>Rp {{ number_format($data->payment_details['cash_amount'] - $data->final_amount, 0, ',', '.') }}</span>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Footer -->
        <div class="footer">
            @if(isset($data['footer_store_message']) && $data['footer_store_message'])
            <div class="footer-message">
                {!! $data['footer_store_message'] !!}
            </div>
            @endif
            
            <div class="divider"></div>
            
            <div class="footer-notes">
                <p>Simpan email ini sebagai bukti pembelian Anda</p>
                <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
                <p style="margin-top: 16px; color: #94a3b8; font-size: 12px;">
                    Email otomatis dari {{ $data['store_name'] }}<br>
                    Jika ada pertanyaan, silakan hubungi toko kami
                </p>
            </div>
        </div>
    </div>
</body>
</html>