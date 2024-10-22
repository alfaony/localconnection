@extends('adminlte::page')

@section('content_header')
    <h1 id="quote_title">Detail Invoice {{ $invoice->number_result }}</h1>
@stop

@section('content')
<div class="col-md-12">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<div id="accordion">
  <div class="card">
    <div class="card-header" id="headingOne">
      <h5 class="mb-0">
        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Invoice {{ $invoice->number_result }}
        </button>
      </h5>
    </div>

    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
      <div class="card-body">
        <div class="row mt-3">
            <div class="offset-md-6 col-6">
                <div class="form-group row">
                    <label for="date" class="col-sm-4 col-form-label text-right">Start Date:</label>
                    <div class="col-sm-8">
                        <input type="date" id="date" readonly class="form-control" placeholder="2023-03-10" value="{{ @$invoice ? @$invoice->start_date->format('Y-m-d') : old('start_date') }}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="due_date" class="col-sm-4 col-form-label text-right">Due Date:</label> <!-- Added due date field -->
                    <div class="col-sm-8">
                        <input type="date" id="date" readonly class="form-control" placeholder="2023-03-10" value="{{ @$invoice ? @$invoice->end_date->format('Y-m-d') : old('start_date') }}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="sales" class="col-sm-4 col-form-label text-right">Sales:</label>
                    <div class="col-sm-8">
                        <input type="text" id="sales" class="form-control" value="{{ $userCreate ?? '' }}" readonly>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-2">
                <label>No Invoice:</label>
            </div>
            <div class="col-6">
                <input class="form-control" disabled  value="{{ $invoice->number_result ?? '' }}">
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-2">
                <label>BAST</label>
            </div>
            <div class="col-6">
                <select class="form-control" id="" readonly disabled>
                    <option value="" selected disabled>--Pilih--</option>
                    @foreach($basts as $a)
                        <option value="{{ $a->id }}"  {{ @$invoice->bast_id == $a->id ? 'selected' : '' }}>{{ $a->number_result }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-2">
                <label>Nama Customer:</label>
            </div>
            <div class="col-6">
                <input type="text" class="form-control" value="{{ @$invoice->quote->customer->name }}" id="customer" placeholder="Pilih Nomor Quote" required="" readonly="">
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-2">
                <label>Status</label>
            </div>
            <div class="col-6">
                <select class="form-control" id="" readonly disabled>
                    <option value="" selected disabled>--Pilih--</option>
                    @foreach($status as $id => $index)
                        <option value="{{ $id }}"  {{ @$invoice->status == $id ? 'selected' : '' }}>{{ $index }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-2">
                <label>Pajak:</label>
            </div>
            <div class="col-6">
                <div class="input-group">
                    <input type="number" readonly id="tax" class="form-control calculation minNol" min="0" placeholder="10" value="{{ old('tax') ?? @$invoice->tax }}">
                    <div class="input-group-prepend">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-2">
                <label>Service Fee:</label>
            </div>
            <div class="col-6">
                <div class="input-group">
                    <input type="number" readonly id="service_fee" class="form-control calculation minNol" min="0" placeholder="10" value="{{ old('service_fee') ?? @$invoice->service_fee }}">
                    <div class="input-group-prepend">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="row mt-3">
            <div class="col-2">
                <label>Discount:</label>
            </div>
            <div class="col-6">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input type="text" readonly class="form-control calculation" id="discount_show"  oninput="formatRupiahFormat(this,'discount')" />
                    <input type="hidden" class="form-control" name="discount" id="discount" value="{{ old('discount') ?? @$invoice->discount }}" />
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-2">
                <label>Other Tax/Charges:</label>
            </div>
            <div class="col-6">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Rp</span>
                    </div>
                    <input type="text" readonly class="form-control calculation" id="charges_show"  oninput="formatRupiahFormat(this,'charges')"  />
                    <input type="hidden" class="form-control" name="charges" id="charges" value="{{ old('charges') ?? @$invoice->charges }}" />
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-2">
                <p>Important Information</p>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-2">
                <label for="transition_text">Payment Terms:</label>
            </div>
            <div class="col-6">
                <input type="text" class="form-control" readonly value="{{ @$invoice->payment_term ? @$invoice->payment_term : '30D After Invoice' }}">
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-2">
                <label for="transition_text">Reference Third Party Docs:</label>
            </div>
            <div class="col-6">
                <input type="text" class="form-control" readonly value="{{ @$invoice->third_party_docs ? @$invoice->third_party_docs : '-' }}">
            </div>
        </div>

        <table class="table table-striped mt-3" id="tableQuote">
            <thead>
                <tr>
                    <th class="col-auto">#</th>
                    <th class="col-3">Produk/Jasa</th>
                    <th class="col-1">Satuan</th>
                    <th class="col-3">Description</th>
                    <th class="col-2">Qty</th>
                    <th class="col-2">Total</th>
                </tr>
            </thead>
            <tbody>
                @if(@$invoice)
                @php $nomorBaris = 1; @endphp
                @foreach($invoice->invoiceProducts->sortBy('sort') as $a)
                <tr data-key="{{ $a->id }}">
                    <td class="col-auto">
                        {{ $nomorBaris++ }}
                    </td>
                    <td class="col-3">
                        {{ $a->product->name }}
                        <input type="hidden" class="form-control" id="product_name_{{ $a->id }}" name="product_name[]" value="{{ $a->product->id ?? '' }}" readonly>
                    </td>

                    <td class="col-1" id="method_count_${key}">
                        {{ $a->product->method_count ?? "" }}
                    </td>
                    <td class="col-3">
                        {!! $a->description !!}
                    </td>
                    <td class="col-2">
                        <input type="hidden" id="price_{{ $a->id }}" name="price[]" data-key="{{ $a->id }}" min="1" class="form-control" value="{{ $a->price_sell }}" required>
                        <input type="hidden" id="qty_{{ $a->id }}" name="qty[]" data-key="{{ $a->id }}" min="1" class="form-control qtyChange" placeholder="Quantity" value="{{ old('qty') ?? @$a->qty }}" required>
                        {{ $a->qty }}
                    </td>
                    <td class="col-2" id="sub_total_show_{{ $a->id }}">
                        {{ 'Rp. '.number_format($a->sub_total,0,',','.') }}
                        <input type="hidden" class="form-control" placeholder="Total" id="" name="ids[]" value="{{ $a->id }}">
                        <input type="hidden" class="form-control" placeholder="Total" id="sub_total_{{ $a->id }}" name="sub_total[]" value="{{ $a->sub_total }}">
                    </td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
        <div class="row mt-3">
            <div class="col-4 offset-8">
                <div class="d-flex justify-content-between mb-2">
                    <div>Total:</div>
                    <div id="sub_total_result">Rp 0</div>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <div>Discount: -</div>
                    <div id="discount_result">Rp 0</div>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <div>Other Tax/Charges:</div>
                    <div id="charges_result">Rp 0</div>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <div id="service_fee_title">Service Fee:</div>
                    <div id="service_fee_result">Rp 0</div>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <div id="ppn_title">PPN: 0%</div>
                    <div id="ppn_result">Rp 0</div>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <strong>Grand Total:</strong>
                    <strong id="grand_total_result">Rp 0</strong>
                    <input type="hidden" id="grand_total_result_raw" value="70000">
                </div>
            </div>
        </div>

        
        <div class="mt-3">
            <div class="d-flex justify-content-center align-items-center">
                @canAccess('downloadPdf','invoices')
                <a href="{{ route('invoice.download.pdf', ['slug' => $invoice->slug]) }}" class="btn btn-success"><i class="fa fa-file-pdf"></i> Download</a>
                @endcanAccess
            </div>
        </div>
      </div>
    </div>
  </div>
  @if($bast)
  <div class="card">
    <div class="card-header" id="headingTwo">
      <h5 class="mb-0">
        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
            BAST
        </button>
      </h5>
    </div>
    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
      <div class="card-body">
        <div class="card" id="printThis">
            <div class="card-body" id="printItem">
                <div class="row">
                    <div class="col-md-12 text-center">
                    <h1>Berita Acara Serah Terima</h1>
                    <p>No. {{ $bast->number ?? '' }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                    <table class="table table-bordered">
                        <tr>
                        <th>Nomor</th>
                        <td>{{ $bast->number }}</td>
                        </tr>
                        <tr>
                        <th>Tanggal</th>
                        <td>{{ $today  ?? ''}}</td>
                        </tr>
                        <tr>
                        <th>No. Purchase Order</th>
                        <td>{{ $bast->number_purchase ?? '' }}</td>
                        </tr>
                        <tr>
                        <th>Penanggung Jawab</th>
                        <td>
                            {{ $bast->pic ?? '' }}
                        </td>
                        </tr>
                        <tr>
                        <th>Perusahaan</th>
                        <td>{{ $bast->workOrder ? $bast->workOrder->quote->customer->name : '' }}</td>
                        </tr>
                    </table>
                    </div>
                    <div class="col-md-12">
                    <p>Bersamaan dengan surat pernyataan ini, pekerjaan dengan nomor purchase order diatas dengan rincian pekerjaan:</p>
                    <p><strong>{{ $bast->project ? $bast->project->title : '' }}</strong></p>
                    <p>Telah diselesaikan dengan baik. Laporan bisa di unduh di link berikut ini</p>
                    <ul>
                        @if($bast->project)
                        @if($bast->project->reportProject->reportProjectDetail)
                        @php $detail = $bast->project->reportProject->reportProjectDetail; @endphp
                        @foreach($detail as $a)
                        <li>
                            {{ $a->name .' - ' }}  <a href="{{ $a->url }}" class="text-primary">{{ $a->url }}</a>
                        </li>
                        @endforeach
                        @endif
                        @endif
                    </ul>
                    <ul>
                    </ul>
                    </div>
                </div>
                <div class="mt-5">
                    <div class="row">
                        <div class="offset-1 col-3">
                            <span style="margin-bottom: 0;">TTD</span>
                        </div>
                        <div class="offset-5 text-left">
                            <p>Diterima,</p>
                        </div>
                        
                        <div class="offset-1 col-2 mb-3 mt-3" id="space">
                            <img src="{{ asset('logo/paraf.png') }}" alt="Signature" style="with:auto; height:150px">
                        </div>
                        <div class="col-8">

                        </div>

                        <div class="offset-1 col-3">
                            {{ $company['director'] ?? '' }}
                        </div>
                        <div class="offset-5 text-left">
                            <p class="noMargin">{{ $bast->workOrder ? $bast->workOrder->quote->customer->{$bast->customer_signature} : '' }}</p>
                        </div>
                        <div class="offset-9 text-left">
                            <p>{{ $bast->workOrder ? $bast->workOrder->quote->customer->name : '' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 text-center mt-3"> <!-- Penambahan class text-center dan mt-3 -->
            <button type="button" id="downloadBast" class="btn btn-success"><i class="fa fa-file-pdf"></i> {{__('Download')}}</button>
        </div>
      </div>
    </div>
  </div>
  @endif
  @if($bast->project && $bast->project->reportProject)
  @php $reportProject = $bast->project->reportProject; @endphp  
  <div class="card">
    <div class="card-header" id="headingThree">
      <h5 class="mb-0">
        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
            Laporan Proyek
        </button>
      </h5>
    </div>
    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
      <div class="card-body">
        <div class="form-group row">
            <div class="col-md-6">
                <h2>Laporan Proyek</h2>
                <div class="mt-5">No Report: {{ $reportProject->number_result }}</div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <label for="date" class="col-sm-8 col-form-label text-right">Tanggal:</label>
                    <div class="col-sm-4">
                        <input type="date" class="form-control" id="date" value="{{ old('date') ?? @$reportProject->date }}" readonly>
                    </div>
                </div>
                <div class="row mt-2">
                    <label class="col-sm-8 col-form-label text-right">PM:</label>
                    <div class="col-sm-4">
                        <span class="form-control-plaintext">{{ $userCreate ?? '' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label>Data Proyek</label>
                <input type="text" class="form-control" id="date" value="{{ old('date') ?? @$reportProject->project->title }}" readonly>
            </div>
        </div>
        <table class="table table-bordered mt-3" id="tableReport">
            <thead>
                <tr>
                    <th >No</th>
                    <th >Nama</th>
                    <th >Link</th>
                    <th >File</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach(@$reportProject->reportProjectDetail as $a)
                <tr>
                    <td>
                        {{ $no++ }}
                    </td>
                    <td width="30%">
                        {{ $a->name }}
                    </td>
                    <td width="30%">
                        {{ $a->link }}
                    </td>
                    <td width="20%">
                        <a href="{{ Storage::url('reports/' . $a->file) }}" class="btn btn-sm btn-primary" download title="{{ $a->file }}"><i class="fa fa-download"></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
      </div>
      @canAccess('downloadall','report_projects')
      <div class="mt-3 mb-2">
            <div class="d-flex justify-content-center align-items-center">
                <a href="{{ route('report-project.downloadall', ['slug' => $reportProject->slug]) }}" class="btn btn-success"><i class="fa fa-download"></i> Download All</a>
            </div>
        </div>
        @endcanAccess
    </div>
  </div>
  @endif
</div>
<div class="mt-3">
    <div class="d-flex justify-content-center align-items-center">
        <a href="{{ url()->previous() }}" class="btn btn-secondary mr-2"><i class="fa fa-arrow-left"></i>Kembali</a>
    </div>
</div>
@stop
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Dapatkan semua elemen textarea

        // Loop melalui setiap textarea dan pasangkan CKEditor

        // Event listener for leads_from select change
        $('#leads_from').change(function () {
            var selectedValue = $(this).val();
            if (selectedValue == '1') 
            {
                $('#division_budget_row').show();
                $('#division_budget').attr('required', 'required');
                $("#submit").attr('disabled', 'disabled');

            } else {
                $('#division_budget_row').hide();
                $('#division_budget').removeAttr('required');
                $('#budget_amount_row').hide();
                $('#budget_usage_row').hide();
                $('#remaining_budget_row').hide();
                $('#budget_amount').text('');

                $('#division_budget').val('').trigger('change');
                $("#submit").removeAttr('disabled');
            }
        });

        // Check initial value on page load (for edit scenario)
        var initialLeadsFrom = $('#leads_from').val();
        if (initialLeadsFrom == '1') {
            $('#division_budget_row').show();
            $('#division_budget').attr('required', 'required');
        }

        // Event listener for division_budget select change
        $('#division_budget').change(function () {
            var selectedBudget = parseFloat($(this).find(':selected').data('budget'));
            var grandTotal = parseFloat("{{ @$invoice->total ?? 0 }}");
            var currentQuoteId = "{{ @$invoice->id ?? '' }}";
            var currentDivisionBudgetId = "{{ @$invoice->division_budget_id ?? '' }}";
            var selectedDivisionBudgetId = $(this).val();

            if (currentQuoteId && currentDivisionBudgetId == selectedDivisionBudgetId) {
                // If the selected budget matches the quote's division_budget_id, add the total
                selectedBudget += grandTotal;
            }

            if (selectedBudget || selectedBudget === 0) 
            {
                $('#budget_amount_row').show();
                if (selectedBudget == 0) {
                    $('#budget_amount').text('Rp 0');
                } else {
                    selectedBudget ; // Tambahkan grand total saat ini jika ada
                    $('#budget_amount').text('Rp ' + selectedBudget.toLocaleString('id-ID'));
                }
                calculation();
            } else {
                $('#budget_amount_row').hide();
                $('#budget_amount').text('');
            }
        });

        // Trigger change event if there's an initial value for division_budget
        if ($('#division_budget').val()) {
            $('#division_budget').trigger('change');
        }
    });

    function generateCkEditor(id)
    {
        // var editors = document.querySelectorAll('.quilljs-editor');
        var quill = new Quill('#editor_'+id, {
            theme: 'snow',
            modules: {
                toolbar: [
                        [{ header: [1, 2, 3, 4, 5, 6, false] }],
                        ["bold", "italic"],
                        [{ list: "ordered" }, { list: "bullet" }],
                        [{ color: [] }, { background: [] }],
                ]
        },
        });
        quill.on('text-change', function(delta, oldDelta, source) {
            document.getElementById("description_"+id).value = quill.root.innerHTML;
        });
    }
</script>
<script>
    $(document).ready(function () 
    {
        calculation();
        $("#submit").click(function (e) 
        { 
            e.preventDefault();
            let submited = true;
            var input = document.querySelectorAll('input.thriveEditor');
            input.forEach(function (textarea) 
            {
                val = textarea.value;
                if(!val)
                {
                    Swal.fire({
                        title: 'Warning!',
                        text: 'Deskripsi Harus Diisi.',
                        icon: 'warning',
                        showConfirmButton: false,
                        timer: 1000
                    });

                    submited = false;
                }
            });

            if(submited)
            {
                $("#btnSubmit").click();
            }
            
        });
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
            var methodcount = $(this).find(':selected').data('methodcount') ?? "";

            var productSelected = $(this).val();
            var qty = $("#qty_"+key).val();

            productPrice(productSelected, key, function(price) 
            {
                // This code runs after the price is updated
                $("#price_"+key).val(price).change();

                if(productSelected && key && qty && price) {
                    countProduct(productSelected, key, qty, price);
                    $("#method_count_"+key).html(methodcount);
                }
            });
        });

        $('#tableQuote').on('change', '.qtyChange', function (e) { 
            e.preventDefault();
            console.log("QTY CHANGE=====");

            var key = $(this).data('key');

            var productSelected = $("#product_"+key).find(':selected').val();
            var qty = $("#qty_"+key).val();
            var price = $("#price_"+key).val();

            if(qty <= 0)
            {
                $("#qty_"+key).val(1);
            }

            if(productSelected && key && qty && price)
            {
                countProduct(productSelected, key, qty, price);
            }
        });

        $(".quoteSuggestion").change(function (e) 
        { 
            e.preventDefault();
            var id = $(this).find(':selected').val();
            if(id)
            {
                let url = "{{ route('invoice.suggestionQuote',':id') }}";
                url = url.replace(":id",id);

                $.ajax({
                    type: "GET",
                    url: url,
                    success: function (response) 
                    {
                        if(response.product)
                        {
                            $('#tableQuote tbody').empty();
                            
                            // addQuote(tax = 0, service_fee = 0, discount = 0)
                            if(response.quote)
                            {
                                addQuote(response.quote.tax, response.quote.service_fee, response.quote.discount, response.quote.charges);
                            }
                            

                            $.each(response.product, function (index, value) 
                            { 
                                addForm(value.product_id,value.price_buy,value.qty,value.description)    
                            });
                            
                            if(response.customer)
                            {
                                $("#customer").val(response.customer);
                            }
                        }  
                    }
                });
            }
            
        });
        
    });
</script>
<script>
    $(document).ready(function() 
    {
        
        $('.select2').select2({
            width: '100%',
            placeholder: '-- Pilih --'
        });

        // change
        

        $('#btnTambahBarisProduct').click(function(e) {  
            e.preventDefault();

            var key = generateRandomString(4);
            var noBaris = $('#tableQuote tbody tr').length + 1; // Menghitung jumlah baris untuk nomor baris selanjutnya
            var dataSelect = @json($product);

            if (!Array.isArray(dataSelect)) 
            {
                console.error("dataSelect is not an array");
                return;
            }

            var projectOptions = '';
            var groupedProducts = {};

            // Group products by category
            dataSelect.forEach(function (product) {
                console.log(product.category);
                var category = product.category ? product.category.name : 'Other';
                if (!groupedProducts[category]) {
                    groupedProducts[category] = [];
                }
                groupedProducts[category].push(product);
            });

            // Generate options with optgroup
            $.each(groupedProducts, function (category, products) {
                projectOptions += `<optgroup label="${category}">`;
                products.forEach(function (product) {
                    projectOptions += `<option value="${product.id}" data-methodcount="${product.method_count}" data-key="${key}">${product.name}</option>`;
                });
                projectOptions += `</optgroup>`;
            });

            const row = `
                <tr class="d-flex" data-key="${key}">
                    <td class="col">
                        ${noBaris}
                    </td>
                    <td class="col-3">
                        <select class="form-control productChange select2" name="product[]" id="product_${key}" required>
                            <option value="" selected disabled>Pilih</option>
                            ${projectOptions}
                        </select>
                    </td>
                    <td class="col-1" id="method_count_${key}">
                    </td>
                    <td class="col-3">
                        <input type="hidden" class="thriveEditor" data-ids="${key}" id="description_${key}" name="description[]" required>
                        <div id="editor_${key}" style="min-height: 120px;"></div>
                    </td>
                    <td class="col-2">
                        <input type="hidden" id="price_${key}" name="price[]" data-key="${key}" min="1" class="form-control" value="" required>
                        <input type="number" id="qty_${key}" name="qty[]" data-key="${key}" min="1" class="form-control qtyChange" placeholder="Quantity" value="1" required>
                    </td>
                    <td class="col-2" id="sub_total_show_${key}">
                        Rp 0
                    </td>
                    <td class="col">
                        <input type="hidden" class="form-control" placeholder="Total" id="" name="ids[]">
                        <input type="hidden" class="form-control" placeholder="Total" id="sub_total_${key}" name="sub_total[]">
                        <button class="btn btn-danger btn-sm btnHapus"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            `;
            
            $('#tableQuote tbody').append(row);

            $('#product_' + key).select2({
                width: '100%'
            });

            generateCkEditor(key);
        });

        
        $('#tableQuote').on('click','.btnHapus', function(event) 
        {
            event.preventDefault(); // Menghentikan default behavior dari tombol submit jika ada
            // Tampilkan konfirmasi penghapusan
            var userConfirmation = confirm("Apakah anda yakin untuk menghapus data ini?");
            
            if(userConfirmation) 
            {
                $(this).closest('tr').remove();
                updateNomorBaris();
                calculation();
            }
            
        });

        $('.btnHapusData').click(function(event) {
            event.preventDefault(); // Menghentikan default behavior dari tombol submit jika ada

            var dataId = $(this).data('id');
            
            // Tampilkan konfirmasi penghapusan
            var userConfirmation = confirm("Apakah anda yakin untuk menghapus data ini?");
            
            if(userConfirmation) 
            {
                // let url = "{{ route('invoice.destroy.product',':id') }}";
                // url = url.replace(':id',dataId);
                // // $(this).closest('tr').remove(); // Hapus baris yang berisi tombol yang diklik
                // // updateNomorBaris(); // Perbarui nomor baris
                // // Jika user mengonfirmasi, lakukan request AJAX untuk menghapus data
                // $.ajax({
                //     type: "POST", // atau "DELETE" sesuai dengan metode yang Anda gunakan
                //     url: url, // Gantikan dengan endpoint Anda
                //     data: 
                //     {
                //         id: dataId,
                //         _token: "{{ csrf_token() }}", // Untuk Laravel, tambahkan CSRF token
                //         _method: "DELETE" // Untuk Laravel, tambahkan CSRF token
                //     },
                //     success: function(response) 
                //     {
                //         Swal.fire(
                //             {
                //             title: 'Berhasil!',
                //             text: 'Berhasil Menghapus Data',
                //             icon: 'success',
                //             timer: 1500, // 3 detik
                //             timerProgressBar: true,
                //             showConfirmButton: false,
                //             showConfirmButton: false, // Menghilangkan tombol OK/Confirm
                //         });
                //     },
                //     error: function(jqXHR, textStatus, errorThrown) 
                //     {
                //         alert("Terjadi kesalahan saat menghapus data");
                //     }
                // });

                $(this).closest('tr').remove(); // Hapus baris yang berisi tombol yang diklik
                updateNomorBaris(); // Perbarui nomor baris
                calculation();
            }
        });
    });

    function productPrice(product,invoiceProductId,callback)
    {
        $.ajax({
            type: "GET",
            url: "{{ route('invoice.productPrice') }}",
            data: {product:product,invoiceProductId:invoiceProductId},
            success: function (response) 
            {
                var price = response.data;
                callback(price); // Pass the price to the callback function
            },
        });
    }
    
    function calculation()
    {
        var tax = parseInt($("#tax").val()) || 0;
        var service_fee = parseInt($("#service_fee").val()) || 0;
        var discount = parseInt($("#discount").val()) || 0;
        var charges = parseInt($("#charges").val()) || 0;
        var divisionBudget = $("#division_budget").val();
        var total = 0;
        var quote_id = "{{ @$invoice->id ?? '' }}";

        $('#tableQuote tbody tr').each(function() 
        {
            var subTotal = parseFloat($(this).find('input[name="sub_total[]"]').val() || 0);
            total += subTotal;
        });

        $.ajax({
            type: "GET",
            url: "{{ route('invoice.counting') }}",
            data: {
                total: total,
                tax: tax,
                service_fee: service_fee,
                discount: discount,
                charges: charges,
                division_budget: divisionBudget,
                quote_id: quote_id
            },
            success: function (response) 
            {
                if(response.status === 200) {
                    values = response.data;
                    $("#service_fee_result").html(values.service_fee);
                    $("#ppn_result").html(values.ppn);
                    $("#ppn_title").html('PPN: ' +tax+ '%');
                    
                    $("#grand_total_result").html(values.grand_total);
                    
                    $('#budget_usage_row').hide();
                    $('#remaining_budget_row').hide();
                    
                    if(response.data.calculationExternal)
                    {
                        console.log("here");
                        $('#budget_usage_row').show();
                        $('#remaining_budget_row').show();

                        $('#budget_usage').html(values.grand_total);
                        $('#remaining_budget').html(values.remaining_budget);
                    }
                    // Check if grand total exceeds division budget
                    if (!response.save) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Grand Total Melebihi Anggaran Divisi!',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });

                        // Disable submit button
                        $("#submit").attr('disabled', 'disabled');
                    } else {
                        // Enable submit button if grand total is within the budget
                        $("#submit").removeAttr('disabled');
                    }
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });

        $("#discount_result").html(formatRupiah(discount,'Rp. '));
        $("#charges_result").html(formatRupiah(charges,'Rp. '));
        $("#sub_total_result").html(formatRupiah(total,'Rp. '));
    }

    function updateNomorBaris() {
        $('#tableQuote tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    function generateRandomString(length) 
    {
        var result = '';
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
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
            input.value = rupiah;
        }

        // Update 'salary' input with non-formatted number
        let parsedValue = parseInt(numStr);
        document.getElementById(inputNonFormat).value = isNaN(parsedValue) ? 0 : parsedValue;
    }

    function formatRupiah(angka, prefix)
    {
        var number_string = angka.toString().replace('/[^,\d]/g', '').toString(),
        split           = number_string.split(','),
        sisa            = split[0].length % 3,
        rupiah          = split[0].substr(0, sisa),
        ribuan          = split[0].substr(sisa).match(/\d{3}/gi);

        // tambahkan titik jika yang di input sudah menjadi angka ribuan
        if(ribuan){
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }

    function countProduct(productId,key,qty,price)
    {        
        $.ajax({
            type: "GET",
            url: "{{ route('invoice.productCounting') }}",
            data: {product:productId,qty:qty,price:price},
            success: function (response) 
            {
                if(response.data)
                {
                    console.log(response);

                    // die;
                    $("#sub_total_"+key).val(response.data);
                    $("#sub_total_show_"+key).html(formatRupiah(response.data || 0,'Rp. '));
                }

                calculation();
            },
        });
    }

    function addForm(defaultProductId = null, price = null, defaultQty = 1,defaultDescription = null) 
    {
        var key = generateRandomString(4);
        var noBaris = $('#tableQuote tbody tr').length + 1; // Menghitung jumlah baris untuk nomor baris selanjutnya
        var dataSelect = @json($product);

        if (!Array.isArray(dataSelect)) 
        {
            console.error("dataSelect is not an array");
            return;
        }

        var projectOptions = '';
        var groupedProducts = {};

        // Group products by category
        dataSelect.forEach(function (product) {
            var category = product.category ? product.category.name : 'Other';
            if (!groupedProducts[category]) {
                groupedProducts[category] = [];
            }
            groupedProducts[category].push(product);
        });

        // Generate options with optgroup
        $.each(groupedProducts, function (category, products) {
            projectOptions += `<optgroup label="${category}">`;
            products.forEach(function (product) {
                var isSelected = product.id == defaultProductId ? 'selected' : '';
                projectOptions += `<option value="${product.id}" data-methodcount="${product.method_count}" data-key="${key}" ${isSelected}>${product.name}</option>`;
            });
            projectOptions += `</optgroup>`;
        });

        const row = `
            <tr class="d-flex" data-key="${key}">
                <td class="col">
                    ${noBaris}
                </td>
                <td class="col-3">
                    <select class="form-control productChange select2" name="product[]" id="product_${key}" required>
                        <option value="" selected disabled>Pilih</option>
                        ${projectOptions}
                    </select>
                </td>
                <td class="col-1" id="method_count_${key}">
                </td>
                <td class="col-3">
                    <input type="hidden" class="thriveEditor" data-ids="${key}" id="description_${key}" name="description[]" required>
                    <div id="editor_${key}" style="min-height: 120px;"></div>
                </td>
                <td class="col-2">
                    <input type="hidden" id="price_${key}" name="price[]" data-key="${key}" min="1" class="form-control" value="" required>
                    <input type="number" id="qty_${key}" name="qty[]" data-key="${key}" min="1" class="form-control qtyChange" placeholder="Quantity" value="${defaultQty}" required>
                </td>
                <td class="col-2" id="sub_total_show_${key}">
                    Rp 0
                </td>
                <td class="col">
                    <input type="hidden" class="form-control" placeholder="Total" id="" name="ids[]">
                    <input type="hidden" class="form-control" placeholder="Total" id="sub_total_${key}" name="sub_total[]">
                    <button class="btn btn-danger btn-sm btnHapus"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
        `;
        
        $('#tableQuote tbody').append(row);

        $('#product_' + key).select2({
            width: '100%'
        });

        generateThriveEditor(key,defaultDescription);
        
        $('#product_' + key).trigger('change');
    }

    function addQuote(tax = 0, service_fee = 0, discount = 0, charges = 0)
    {
        $('#tax').val(tax);
        $('#service_fee').val(service_fee);
        $('#discount').val(discount);
        $("#charges").val(charges);
        if(discount > 0)
        {
            let discountProduct = document.getElementById("discount").value;
            if (discountProduct) 
            {
                document.getElementById("discount_show").value = discountProduct;
                formatRupiahFormat(document.getElementById("discount_show"),"discount"); // Format default value
            }
        }
        
        console.log(charges);
        
        if(charges > 0)
        {
            let chargesProduct = document.getElementById("charges").value;
            if (chargesProduct) 
            {
                document.getElementById("charges_show").value = chargesProduct;
                formatRupiahFormat(document.getElementById("charges_show"),"charges"); // Format default value
            }
        }

        calculation();
    }
</script>

<!-- Bast -->
<script>
    $(document).ready(function () 
    {
        $("#downloadBast").click(function (e) 
        { 
            e.preventDefault();
            prinsts();
            
        });

        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih Quote'
        });
    });

    function prinsts() 
    {
        let name = "{{ $nomorBast }}"+"_bast";;
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
@stop
@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .select2-selection__rendered 
    {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single 
    {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }
    .ql-container 
    {
        min-height: 150px;
        height: auto;
    }
</style>
@stop

