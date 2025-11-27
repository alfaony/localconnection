@extends('adminlte::page')
@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Quote Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Quote Berhasil Diperbarui</div>
    @endif
</div>

<div class="container">
  <div class="card scrollable-div" id="printThis">
    <div class="card-body">
      <div class="row mt-4">
          <div class="col-12 d-flex justify-content-center align-items-center">
              <h1 >QUOTATION {{ $quote->budget_transition ? ' PERALIHAN ' : ' ' }} {{ $quote->number_result }}</h1>
          </div>
      </div>
      <div class="row">
          <div class="col-12 d-flex justify-content-center align-items-center">
              <h2 class="pt-aida">{{ $company['name'] }}</h2>
          </div>
      </div>
      <div class="card">
          <div class="card-header">
              <h4 class="title">Quote Details</h4>
          </div>
          <div class="card-body">
              <div class="row">
                  <div class="col-md-8">
                      <p>Create Date: {{ $quote->date }}</p>
                  </div>
                  <div class="col-md-4">
                      <p>Sales Name: {{ $userCreate ?? '' }}</p>
                      <p>Status: Existing Client</p>
                  </div>
              </div>
          </div>
      </div>

      <!-- hidden value -->
      <input type="hidden" name="tax" id="tax" class="form-control calculation minNol" min="0" placeholder="10" value="{{ old('tax') ?? @$quote->tax }}" required>
      <input type="hidden" name="service_fee" id="service_fee" class="form-control calculation minNol" min="0" placeholder="10" value="{{ old('service_fee') ?? @$quote->service_fee }}" required>
      <input type="hidden" class="form-control" name="discount" id="discount" value="{{ old('discount') ?? @$quote->discount }}" />
      <input type="hidden" class="form-control" name="charges" id="charges" value="{{ old('charges') ?? @$quote->charges }}" />

      <!-- End Hidden Value -->
      <!-- Card for To -->
      <div class="card">
          <div class="card-header">
              <h5 class="title">To:</h5>
          </div>
          <div class="card-body">
              <div class="row">
                <div class="col-2">Account Name: </div>
                <div class="col-10"><p>{{ $quote->customer ? $quote->customer->name : '' }}</p></div>
                <div class="col-2">Contact Name: </div>
                <div class="col-4"><p>{{ $quote->customer ? $quote->customer->director : '' }}</p></div>
                <div class="col-2">Billing Address:</div>
                <div class="col-4">
                  <p>{{ $quote->customer ? $quote->customer->address : '' }}</p>
                </div>
                <div class="col-2 offset-6">Email :</div>
                <div class="col-4">
                  <p>{{ $quote->customer ? $quote->customer->email : '' }}</p>
                </div>
              </div>
          </div>
      </div>
  
      <table class="table table-bordered">
          <thead class="bg-danger text-white">
              <tr>
                  <th class="bg-danger text-white" colspan="4">Important Information</th>
              </tr>
          </thead>
          <tbody>
            @if($quote->budget_transition)
                <tr>
                  <td>Quotation / PO</td>
                  <td colspan="3">{{ $quote->quote_transition ?? '' }}</td>
              </tr>
            @endif
              <tr>
                  <td>Payment Terms</td>
                  <td colspan="3">{{ $quote->payment_term ?? '30D After Invoice' }}</td>
              </tr>
              <tr>
                  <td>Reference Third Party Docs</td>
                  <td colspan="3">{{ $quote->third_party_docs ?? '-' }}</td>
              </tr>
          </tbody>
      </table>
      <p class="font-weight-bold mt-4 md-4">QUOTATION DETAIL:</p>
      <table class="table table-bordered mt-3" id="tableQuote">
          <thead class="bg-primary text-white">
              <tr>
                  <th class="bg-danger text-white" width="30%">Product/Service</th>
                  <th class="bg-danger text-white" width="30%">Description</th>
                  <th class="bg-danger text-white" width="10%">Qty</th>
                  <th class="bg-danger text-white" width="15%">Price</th>
                  <th class="bg-danger text-white" width="15%">Total</th>
              </tr>
          </thead>
          <tbody>
              @if(@$quote)
              @php $nomorBaris = 1; @endphp
              @foreach($quote->quoteProduct->sortBy('sort') as $a)
              <tr data-key="{{ $a->id }}">
                  <td>
                      {{ $a->product ? $a->product->name : '' }}
                      <input type="hidden" class="form-control" placeholder="Total" id="" name="ids[]" value="{{ $a->id }}">
                      <input type="hidden" class="form-control" placeholder="Total" id="sub_total_{{ $a->id }}" name="sub_total[]" value="{{ $a->sub_total }}">
                      <input type="hidden" class="is-taxable-input" id="is_taxable_{{ $a->id }}" value="{{ $a->is_taxable ? '1' : '0' }}">
                  </td>
                  <td>
                    {!! $a->description ?? '' !!}
                  </td>
                  <td>
                      {{ $a->qty ?? '' }}
                      <input type="hidden" id="qty_{{ $a->id }}" name="qty[]" data-key="{{ $a->id }}" min="1" class="form-control qtyChange" placeholder="Quantity" value="{{ old('qty') ?? @$a->qty }}" required>
                  </td>
                  <td>
                    {{ $a->product ? 'Rp. '.number_format($a->price_sell,0,',','.') : 'Rp. 0' }}
                  </td>
                  <td id="sub_total_show_{{ $a->id }}">
                      {{ 'Rp. '.number_format($a->sub_total,0,',','.') }}
                  </td>
              </tr>
              @endforeach
              @endif
          </tbody>
      </table>
  
      <div class="row">
          <div class="col-4 offset-8 mt-4">
              <div class="d-flex justify-content-between mb-2">
                  <div>Total:</div>
                  <div class="strongText" id="sub_total_result">Rp 0</div>
              </div>
              <div class="d-flex justify-content-between mb-2">
                  <div>Discount: -</div>
                  <div class="strongText" id="discount_result">Rp 0</div>
              </div>
              <div class="d-flex justify-content-between mb-2">
                  <div>Other Tax/Charges:</div>
                  <div class="strongText" id="charges_result">Rp 0</div>
              </div>
              <div class="d-flex justify-content-between mb-2">
                  <div id="service_fee_title">Service Fee: 0%</div>
                  <div class="strongText" id="service_fee_result">Rp 0</div>
              </div>
              <div class="d-flex justify-content-between mb-2">
                  <div id="ppn_title">PPN: 0%</div>
                  <div class="strongText" id="ppn_result">Rp 0</div>
              </div>
              <hr>
              <div class="d-flex justify-content-between mb-2">
                  <strong>Grand Total:</strong>
                  <strong id="grand_total_result">Rp 0</strong>
              </div>
          </div>
      </div>
  
      <h1 class="text-center" id="spkBorder">Kesepakatan Surat Pesanan</h1>

      <ul class="list-group mt-4">
          <li class="list-group-item">1. Pihak Penjual adalah <strong>{{ $company['name'] ?? '' }}</strong> dan {{ isset($company['affiliate_company']) ? $company['affiliate_company'] : 'perusahaan afiliasinya' }}. Dan Pihak Pembeli adalah <strong>perusahaan / perorangan</strong> penerima surat penawaran ini (Quotation). Pihak Pembeli sepakat untuk membeli sesuai pesanan yang tertera diatas kepada Pihak Penjual dan silahkan melakukan pembayaran{{ isset($company['rekening_number']) ? " nomor rekening ".$company['rekening_number'].',' : ''  }} {{ isset($company['nama_bank']) ? " Bank ".$company['nama_bank'].',' : ''  }} {{ isset($company['cabang_bank']) ? "Cabang ".$company['cabang_bank'] : ''  }}.</li>
          <li class="list-group-item">2. Pihak Pembeli sepakat untuk melakukan pembayaran 14 hari sejak diterimanya invoice ( surat tagihan ) dari Pihak Penjual.</li>
          <li class="list-group-item">3. Bukti Pemotongan PPH 4(2), Pajak PPH23, agar dilampirkan kepada Finance02@brightcorporation.biz</li>
          <li class="list-group-item">4. Pembatalan Faktur Pajak / perubahan faktur pajak maksimal dilakukan 1 minggu sejak diterimanya faktur tersebut oleh pihak pembeli.</li>
          <li class="list-group-item">5. Pesanan ini bersifat final dan tidak dapat dibatalkan secara sepihak oleh Pihak Pembeli, dan akan tetap ditagihkan 100% sesuai nominal pesanan.</li>
          <li class="list-group-item">6. Surat pesanan ( Quotation ) ini dapat dilanjutkan dengan Surat Perjanjian Kerja sama, yang mencantumkan secara detil pekerjaan, biaya pekerjaan dan waktu pengerjaan bila dibutuhkan. Apabila pekerjaan ini tanpa perjanjian, maka surat pesanan ini disepakati sebagai dokumen pesanan yang sah sesuai UU yang berlaku di Indonesia.</li>
          <li class="list-group-item">7. Komunikasi mengenai legal dapat dilakukan di <a href="mailto:legal@brightcorporation.biz">legal@brightcorporation.biz</a> dan komunikasi finance dapat dilakukan di <a href="mailto:finance02@brightcorporation.biz">finance02@brightcorporation.biz</a></li>
          <li class="list-group-item">8. Surat penawaran ini disiapkan secara digital, dan memiliki fungsi pencatatan digital. Untuk penawaran bernilai diatas Rp 100.000.000 ( Seratus juta ) akan dibubuhi tanda tangan digital dari pihak Penjual, untuk penawaran dibawah Rp 100.000.000 maka tidak dibubuhi tanda tangan, tanpa mengurangi kekuatan pengikatan hukum dokumen ini.</li>
      </ul>

      <div class="mt-5">
        <div class="row">
          <div class="offset-1 col-4">
            <span style="margin-bottom: 0;">Jakarta, {{ $today }}</span>
            <p style="margin-top: 0;">Disepakati oleh Pihak Pembeli</p>
          </div>
          <div class="col-6 text-right">
            <p>Pihak Penjual</p>
          </div>
          
          <div class="col-11 text-right">
            <img src="{{ asset('logo/paraf.png') }}" alt="Signature" style="with:auto; height:150px">
          </div>

          <div class="offset-1 col-3">
            <hr style="border: none; height: 1px; color: black; background-color: black;"> <!-- Signature line -->
          </div>
          <div class="col-7 text-right">
            <p class="mt-2"><strong>{{ $company['director'] ?? '' }}</strong></p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-12 text-center mt-3"> <!-- Penambahan class text-center dan mt-3 -->
        @canAccess('edit','quotes')
        <a href="{{ route('quote.edit',$quote->slug) }}" class="btn btn-primary"><i class="fa fa-edit"></i>Edit</a>
        @endcanAccess
        <button type="button" id="downloadQuote" class="btn btn-success"><i class="fa fa-file-pdf"></i> {{__('Download')}}</button>
    </div>
</div>

@stop
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<script>
    $(document).ready(function () 
    {
        $("#downloadQuote").click(function (e) 
        { 
            e.preventDefault();
            prinsts();
            
        });

        calculation();
        let discount = document.getElementById("discount").value;
        if (discount) 
        {
            document.getElementById("discount_show").value = discount;
            formatRupiahFormat(document.getElementById("discount_show"),"discount"); // Format default value
        }

        let charges = document.getElementById("charges").value;
        if (charges) 
        {
            document.getElementById("charges_show").value = charges;
            formatRupiahFormat(document.getElementById("charges_show"),"charges"); // Format default value
        }

        $(".minNol").on("change", function () 
        {
            let nol = $(this).val();
            if(nol <= 0)
            {
                $(this).val(0);
            }
        });

        $(".calculation").change(function (e) 
        { 
            e.preventDefault();
            calculation();
        });

        $('#tableQuote').on('change', '.productChange', function (e) { 
            e.preventDefault();

            var key = $(this).find(':selected').data('key');

            var productSelected = $(this).val();
            var qty = $("#qty_"+key).val();

            if(productSelected && key && qty)
            {
                countProduct(productSelected, key, qty);
            }

        });

        $('#tableQuote').on('change', '.qtyChange', function (e) { 
            e.preventDefault();

            var key = $(this).data('key');

            var productSelected = $("#product_"+key).find(':selected').val();
            var qty = $("#qty_"+key).val();

            if(qty <= 0)
            {
                $("#qty_"+key).val(1);
            }

            if(productSelected && key && qty)
            {
                countProduct(productSelected, key, qty);
            }
        });
    });
</script>
<script>
    function calculation()
    {
        console.log("----CALCULATION WORKING-----");

        var tax = parseInt($("#tax").val()) || 0;
        var service_fee = parseInt($("#service_fee").val()) || 0;
        var discount = parseInt($("#discount").val()) || 0;
        var charges = parseInt($("#charges").val()) || 0;

        var ppn_title = tax <= 0 ? 'PPN: 0%' : 'PPN: '+tax+'%';
        var service_fee_title = service_fee <= 0 ? 'Service Fee: 0%' : 'Service Fee: '+service_fee+'%';

        var sub_total = 0;
        var taxable_total = 0; // Total yang kena pajak
        
        $('#tableQuote tbody tr').each(function() 
        {
            var subTotal = parseFloat($(this).find('input[name="sub_total[]"]').val() || 0);
            var isTaxable = $(this).find('input.is-taxable-input').val() === '1';
            
            sub_total += subTotal;
            
            // Hitung total yang kena pajak
            if (isTaxable) {
                taxable_total += subTotal;
            }
        });

        console.log('Total:', sub_total);
        console.log('Taxable Total:', taxable_total);

        $.ajax({
            type: "GET",
            url: "{{ route('quote.counting') }}",
            data: {
                total: sub_total,
                taxable_total: taxable_total, // Kirim taxable_total ke backend
                tax: tax,
                service_fee: service_fee,
                discount: discount,
                charges: charges
            },
            success: function (response) 
            {
                if(response.data)
                {
                    console.log(response.data.total);
                    values = response.data;
                    console.log(values);

                    $("#service_fee_result").html(values.service_fee);
                    $("#ppn_result").html(values.ppn);
                    $("#grand_total_result").html(values.grand_total);
                }  
            }
        });

        $("#service_fee_title").html(service_fee_title);
        $("#ppn_title").html(ppn_title);
        $("#discount_result").html(formatRupiah(discount,'Rp. '));
        $("#charges_result").html(formatRupiah(charges,'Rp. '));
        $("#sub_total_result").html(formatRupiah(sub_total,'Rp. '));
    }


    function formatRupiahFormat(input, inputNonFormat) 
    {

        let numStr = input.value.toString().replace(/[^,\d]/g, '');
        let split = numStr.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;

        if (numStr === "" || parseInt(numStr) === 0) {
            input.value = '';
            numStr = '';
        } else {
            // Menghapus angka 0 di depan jika input diawali dengan 0
            rupiah = rupiah.replace(/^0+/, '');
            input.value = 'Rp '+rupiah;
        }

        // Update 'salary' input with non-formatted number
        document.getElementById(inputNonFormat).value = parseInt(numStr);
    }

    function formatRupiah(angka, prefix)
    {
        var number_string = angka.toString().replace('/[^,\d]/g', '').toString(),
        split   		= number_string.split(','),
        sisa     		= split[0].length % 3,
        rupiah     		= split[0].substr(0, sisa),
        ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);

        // tambahkan titik jika yang di input sudah menjadi angka ribuan
        if(ribuan){
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }

    function countProduct(productId,key,qty)
    {        
        $.ajax({
            type: "GET",
            url: "{{ route('quote.productCounting') }}",
            data: {product:productId,qty:qty},
            success: function (response) 
            {
                if(response.data)
                {
                    console.log(response.data );

                    $("#sub_total_"+key).val(response.data);
                    $("#sub_total_show_"+key).html(formatRupiah(response.data || 0,'Rp. '));
                }

                calculation();
            },
        });
    }

    function prinsts() 
    {
        let name = "{{ $nomorQuote }}"+"_quote"+"{{ $quote->budget_transition ? ' Peralihan ' : '' }}"+" {{ $quote->customer ? $quote->customer->name : '' }}";
        let printContents = document.getElementById("printThis").innerHTML;
        let originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.addEventListener("beforeprint", (event) => {
            document.title = name;
        });

        window.print();
        document.body.innerHTML = originalContents;
    }
</script>
@section('css')
<style>
  @media print 
  {
    table { page-break-inside:auto }
    tr    { page-break-inside:auto; page-break-after:auto }

    thead.bg-danger 
    {
        background-color: red !important;
    }
    .title {
      font-weight: bold;
    }
    .card + .card {
      margin-top: 0; /* Menghilangkan margin atas pada card kedua */
    }
    .card:first-child {
      margin-bottom: 0; /* Menghilangkan margin bawah pada card pertama */
    }
    #spkBorder 
    {
      margin-top : 8rem!important;
    }
    .strongText 
    {
      font-weight: bold;  /* Membuat teks menjadi tebal */
      color: #000000;    /* Warna teks, ganti dengan warna yang diinginkan */
    }
  }

  .signature p {
    margin: 0; /* No margin for paragraphs */
  }
  .strongText 
  {
    font-weight: bold;  /* Membuat teks menjadi tebal */
      color: #000000;    /* Warna teks, ganti dengan warna yang diinginkan */
  }
  #spkBorder 
    {
      margin-top : 8rem!important;
    }
    .scrollable-div {
      max-height: 600px; /* Tinggi maksimum yang diinginkan untuk container */
      overflow-y: auto; /* Menambahkan scrollbar vertikal jika konten melebihi 800px */
    }
</style>
@stop
  
