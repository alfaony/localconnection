<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 30px; }
        body { font-family: sans-serif; font-size: 12px; line-height: 1.2; color: #333; }
        
        /* Helper Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-weight-bold { font-weight: bold; }
        .mt-3 { margin-top: 15px; }
        .mt-4 { margin-top: 20px; }
        .mt-5 { margin-top: 30px; }
        
        /* Layout Grid menggunakan Table (Paling aman buat DomPDF) */
        .full-width { width: 100%; }
        .header-section { margin-bottom: 20px; }
        
        /* Card Style */
        .card { border: 1px solid #dee2e6; margin-bottom: 10px; width: 100%; }
        .card-header { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 8px 12px; }
        .card-header h4, .card-header h5 { margin: 0; font-size: 14px; font-weight: bold; }
        .card-body { padding: 12px; }

        /* Table Style */
        table { width: 100%; border-collapse: collapse; }
        .table-bordered th, .table-bordered td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        .bg-danger { background-color: #dc3545 !important; color: #ffffff !important; }
        
        /* Signature Area */
        .signature-container { margin-top: 50px; width: 100%; }
        .signature-box { width: 100%; }
        .signature-img { height: 120px; width: auto; }
        .line { border-bottom: 1px solid black; width: 160px; display: inline-block; }
        
        #spkBorder { margin-top: 80px; border-top: 1px solid #333; padding-top: 10px; font-size: 18px; }
        .list-group { padding-left: 0; list-style: none; margin-top: 15px; }
        .list-group-item { border: 1px solid #dee2e6; padding: 10px; margin-bottom: -1px; text-align: justify; }
    </style>
</head>
<body>

    <div class="header-section text-center">
        <h1 style="margin-bottom: 5px;">QUOTATION {{ $quote->number_result }}</h1>
        <h2 style="margin-top: 0;">{{ $company['name'] ?? '' }}</h2>
    </div>

    <div class="card">
        <div class="card-header"><h4>Quote Details</h4></div>
        <div class="card-body">
            <table class="full-width">
                <tr>
                    <td width="60%" class="text-left">Create Date: {{ $quote->date }}</td>
                    <td width="40%" class="text-left">
                        Sales Name: {{ $userCreate ?? '' }}<br>
                        Status: Existing Client
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5>To:</h5></div>
        <div class="card-body">
            <table class="full-width">
                <tr>
                    <td width="20%" class="font-weight-bold">Account Name:</td>
                    <td width="80%">{{ $quote->customer->name ?? '' }}</td>
                </tr>
                <tr>
                    <td class="font-weight-bold">Contact Name:</td>
                    <td>{{ $quote->customer->director ?? '' }}</td>
                </tr>
            </table>
            <table class="full-width" style="margin-top: 5px;">
                <tr>
                    <td width="20%" class="font-weight-bold">Billing Address:</td>
                    <td width="30%">{{ $quote->customer->address ?? '' }}</td>
                    <td width="15%" class="font-weight-bold">Email:</td>
                    <td width="35%">{{ $quote->customer->email ?? '' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="table-bordered mt-3">
        <thead>
            <tr class="bg-danger">
                <th colspan="4" class="text-left" style="color: white;">Important Information</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="30%">Payment Terms</td>
                <td colspan="3">30D After Invoice</td>
            </tr>
            <tr>
                <td>Reference Third Party Docs</td>
                <td colspan="3">-</td>
            </tr>
        </tbody>
    </table>

    <p class="font-weight-bold mt-4">QUOTATION DETAIL:</p>
    <table class="table-bordered">
        <thead>
            <tr class="bg-danger">
                <th width="30%" style="color: white;">Product/Service</th>
                <th width="30%" style="color: white;">Description</th>
                <th width="10%" style="color: white;" class="text-center">Qty</th>
                <th width="15%" style="color: white;">Price</th>
                <th width="15%" style="color: white;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->quoteProduct->sortBy('sort') as $a)
            <tr>
                <td>{{ $a->product->name ?? '' }}</td>
                <td>{!! $a->description ?? '' !!}</td>
                <td class="text-center">{{ $a->qty }}</td>
                <td>Rp. {{ number_format($a->price_sell,0,',','.') }}</td>
                <td>Rp. {{ number_format($a->sub_total,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="full-width mt-4">
        <tr>
            <td width="65%"></td>
            <td width="35%">
                <table>
                    <tr>
                        <td class="text-left">Total:</td>
                        <td class="text-right font-weight-bold">{{ $counting['total'] }}</td>
                    </tr>
                    <tr>
                        <td class="text-left">Discount:</td>
                        <td class="text-right font-weight-bold">{{ $counting['discount'] }}</td>
                    </tr>
                    <tr>
                        <td class="text-left">Other Tax/Charges:</td>
                        <td class="text-right font-weight-bold">{{ $counting['charges'] ?? 'Rp. 0' }}</td>
                    </tr>
                    <tr>
                        <td class="text-left">Service Fee: {{ $counting['service_fee_percentage'] }}</td>
                        <td class="text-right font-weight-bold">{{ $counting['service_fee'] }}</td>
                    </tr>
                    <tr>
                        <td class="text-left">PPN: {{ $counting['tax_percentage'] }}</td>
                        <td class="text-right font-weight-bold">{{ $counting['ppn'] }}</td>
                    </tr>
                    <tr><td colspan="2"><hr></td></tr>
                    <tr>
                        <td class="text-left"><strong>Grand Total:</strong></td>
                        <td class="text-right"><strong>{{ $counting['grand_total'] }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <h2 class="text-center" id="spkBorder">Kesepakatan Surat Pesanan</h2>
    <div class="list-group">
        <li class="list-group-item">1. Pihak Penjual adalah <strong>{{ $company['name'] ?? '' }}</strong> dan perusahaan afiliasinya. Dan Pihak Pembeli adalah <strong>perusahaan / perorangan</strong> penerima surat penawaran ini (Quotation). Pihak Pembeli sepakat untuk membeli sesuai pesanan yang tertera diatas kepada Pihak Penjual.</li>
                  <li class="list-group-item">2. Pihak Pembeli sepakat untuk melakukan pembayaran 14 hari sejak diterimanya invoice ( surat tagihan ) dari Pihak Penjual.</li>
                  <li class="list-group-item">3. Bukti Pemotongan Pajak PPH23, agar dilampirkan kepada Finance02@brightcorporation.biz</li>
                  <li class="list-group-item">4. Pembatalan Faktur Pajak / perubahan faktur pajak maksimal dilakukan 1 minggu sejak diterimanya faktur tersebut oleh pihak pembeli.</li>
                  <li class="list-group-item">5. Pesanan ini bersifat final dan tidak dapat dibatalkan secara sepihak oleh Pihak Pembeli, dan akan tetap ditagihkan 100% sesuai nominal pesanan.</li>
                  <li class="list-group-item">6. Surat pesanan ( Quotation ) ini dapat dilanjutkan dengan Surat Perjanjian Kerja sama, yang mencantumkan secara detil pekerjaan, biaya pekerjaan dan waktu pengerjaan bila dibutuhkan. Apabila pekerjaan ini tanpa perjanjian, maka surat pesanan ini disepakati sebagai dokumen pesanan yang sah sesuai UU yang berlaku di Indonesia.</li>
                  <li class="list-group-item">7. Komunikasi mengenai legal dapat dilakukan di <a href="mailto:legal@brightcorporation.biz">legal@brightcorporation.biz</a> dan komunikasi finance dapat dilakukan di <a href="mailto:finance02@brightcorporation.biz">finance02@brightcorporation.biz</a></li>
                  <li class="list-group-item">8. Surat penawaran ini disiapkan secara digital, dan memiliki fungsi pencatatan digital. Untuk penawaran bernilai diatas Rp 100.000.000 ( Seratus juta ) akan dibubuhi tanda tangan digital dari pihak Penjual, untuk penawaran dibawah Rp 100.000.000 maka tidak dibubuhi tanda tangan, tanpa mengurangi kekuatan pengikatan hukum dokumen ini.</li>
            </div>
            <div class="signature-container">
                <table class="full-width">
                    <tr>
                        <td width="50%" class="text-left" style="vertical-align: top;">
                            <p>Jakarta, {{ $today }}</p>
                            <p>Disepakati oleh Pihak Pembeli</p>
                            <div style="margin-top: 100px;" class="line"></div>
                        </td>
                            <td width="50%" class="text-right" style="vertical-align: top;">
            <p style="margin-right: 40px;">Pihak Penjual</p>
            
            <img src="{{ $base64 }}" class="signature-img">
            
            <p class="font-weight-bold" style="margin-top: 10px;">{{ $company['director'] ?? '' }}</p>
        </td>
                    
            </tr>
        </table>
    </div>

</body>
</html>