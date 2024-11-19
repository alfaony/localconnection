@extends('adminlte::page')

@section('content_header')
    <h1 id="quote_title">Quote Baru</h1>
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
<div class="card">
    @if(@$quote)
    <form method="post" action="{{ route('quote.update',$quote) }}">
    @method('put')
    @else
    <form method="post" action="{{ route('quote.store') }}">
    @endif
    @csrf
    <div class="card-body">
        <div class="card-body">
            <div class="row mt-3">
                <div class="offset-md-6 col-6">
                    <div class="form-group row">
                        <label for="date" class="col-sm-4 col-form-label text-right">Tanggal:</label>
                        <div class="col-sm-8">
                            <input type="date" id="date" name="date" class="form-control" placeholder="2023-03-10" value="{{ old('date') ?? @$quote->date ?? \Carbon\Carbon::today()->format('Y-m-d') }}" required>
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
                    <p>No Quotation:</p>
                </div>
                <div class="col-6">
                    <p>{{ $nomorQuote }}</p>
                    <input type="hidden" name="nomor" value="{{ $nomor ?? '' }}">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-2">
                    <label>Quote Peralihan:</label>
                </div>
                <div class="col-6">
                    <input type="checkbox" name="budget_transition" id="budget_transition" {{ @$quote->budget_transition ? 'checked' : ''}} >
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-2">
                    <label>Nama Customer:</label>
                </div>
                <div class="col-6">
                    <select name="customer" class="form-control select2" required>
                        <option value="" selected disabled>Customer</option>
                        @foreach($customer as $a)
                        <option value="{{ $a->id }}" {{ @$quote->customer_id == $a->id ? 'selected' : '' }}>{{ $a->name .' - '. $a->pic}}</option>
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
                        <input type="number" name="tax" id="tax" class="form-control calculation minNol" min="0" placeholder="10" value="{{ old('tax') ?? @$quote->tax }}">
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
                        <input type="number" name="service_fee" id="service_fee" class="form-control calculation minNol" min="0" placeholder="10" value="{{ old('service_fee') ?? @$quote->service_fee }}">
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
                        <input type="text" class="form-control calculation" id="discount_show"  oninput="formatRupiahFormat(this,'discount')" />
                        <input type="hidden" class="form-control" name="discount" id="discount" value="{{ old('discount') ?? @$quote->discount }}" />
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
                        <input type="text" class="form-control calculation" id="charges_show"  oninput="formatRupiahFormat(this,'charges')"  />
                        <input type="hidden" class="form-control" name="charges" id="charges" value="{{ old('charges') ?? @$quote->charges }}" />
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-2">
                    <label>Lead From:</label>
                </div>
                <div class="col-6">
                    <select id="leads_from" name="leads_from" class="form-control select2" required>
                        <option value="" selected disabled>Leads From</option>
                        @foreach($leadsFrom as $id => $a)
                        <option value="{{ $id }}" {{ @$quote->leads_from == $id ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- DivisionBudget select field, hidden by default -->
            <div class="row mt-3" id="division_budget_row" style="display: none;">
                <div class="col-2">
                    <label>Division Budget:</label>
                </div>
                <div class="col-6">
                    <select name="division_budget" id="division_budget" class="form-control select2">
                        <option value="" selected disabled>Division Budget</option>
                        @foreach($divisionBudget as $a)
                        <option value="{{ $a->id }}" data-budget="{{ $a->amount }}" {{ @$quote->division_budget_id == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="row mt-2" id="budget_amount_row" style="display: none;">
                <div class="col-2">
                    <label>Anggaran:</label>
                </div>
                <div class="col-6">
                    <p id="budget_amount"></p>
                </div>
            </div>

            <div class="row mt-2" id="budget_usage_row" style="display: none;">
                <div class="col-2">
                    <label>Penggunaan Anggaran:</label>
                </div>
                <div class="col-6">
                    <p id="budget_usage"></p>
                </div>
            </div>

            <div class="row mt-2" id="remaining_budget_row" style="display: none;">
                <div class="col-2">
                    <label>Sisa Anggaran:</label>
                </div>
                <div class="col-6">
                    <p id="remaining_budget"></p>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-2">
                    <p>Important Information</p>
                </div>
            </div>

            <div id="quote_transition" style="display:none;">
                <div class="row mt-3">
                    <div class="col-2">
                        <label for="transition_text">Quotation / PO:</label>
                    </div>
                    <div class="col-6">
                        <input type="text" class="form-control" name="quote_transition" value="{{ @$quote->quote_transition ??  old('quote_transition') }}">
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-2">
                    <label for="transition_text">Payment Terms:</label>
                </div>
                <div class="col-6">
                    <input type="text" class="form-control" name="payment_term" value="{{ @$quote->payment_term ? @$quote->payment_term : '30D After Invoice' }}">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-2">
                    <label for="transition_text">Reference Third Party Docs:</label>
                </div>
                <div class="col-6">
                    <input type="text" class="form-control" name="third_party_docs" value="{{ @$quote->third_party_docs ? @$quote->third_party_docs : '-' }}">
                </div>
            </div>

            <table class="table table-bordered mt-3" id="tableQuote">
                <thead>
                    <tr class="d-flex">
                        <th class="col-auto">#</th>
                        <th class="col-3">Produk/Jasa</th>
                        <th class="col-1">Satuan</th>
                        <th class="col-3">Description</th>
                        <th class="col-2">Qty</th>
                        <th class="col-2">Total</th>
                        <th class="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @if(@$quote)
                    @php $nomorBaris = 1; @endphp
                    @foreach($quote->quoteProduct->sortBy('sort') as $a)
                    <tr class="d-flex" data-key="{{ $a->id }}">
                        <td class="col-auto">
                            {{ $nomorBaris++ }}
                        </td>
                        <td class="col-3">
                            <select class="form-control productChange select2" name="product[]" id="product_{{ $a->id }}" required>
                                <option value="" selected disabled>Pilih</option>
                                @foreach($product->groupBy('category.name') as $category => $group)
                                    <optgroup class="select2-result-selectable" label="{{ $category ?? 'Other' }}">
                                        @foreach($group as $item)
                                            <option value="{{ $item->id }}" data-key="{{ $a->id }}" data-methodcount="{{ $a->method_count }}" {{ $a->product_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </td>

                        <td class="col-1" id="method_count_${key}">
                            {{ $a->product->method_count ?? "" }}
                        </td>
                        <td class="col-3">
                            <input type="hidden" class="thriveEditor" data-ids="{{ $a->id }}" id="description_{{ $a->id }}"  name="description[]" value="{{ old('description') ?? @$a->description }}" required>
                            <div id="editor_{{ $a->id }}" style="min-height: 120px;">{!! old('description') ?? @$a->description !!}</div>
                        </td>
                        <td class="col-2">
                            <input type="hidden" id="price_{{ $a->id }}" name="price[]" data-key="{{ $a->id }}" min="1" class="form-control" value="{{ $a->price_sell }}" required>
                            <input type="number" id="qty_{{ $a->id }}" name="qty[]" data-key="{{ $a->id }}" min="1" class="form-control qtyChange" placeholder="Quantity" value="{{ old('qty') ?? @$a->qty }}" required>
                        </td>
                        <td class="col-2" id="sub_total_show_{{ $a->id }}">
                            {{ 'Rp. '.number_format($a->sub_total,0,',','.') }}
                        </td>
                        <td class="col">
                            <input type="hidden" class="form-control" placeholder="Total" id="" name="ids[]" value="{{ $a->id }}">
                            <input type="hidden" class="form-control" placeholder="Total" id="sub_total_{{ $a->id }}" name="sub_total[]" value="{{ $a->sub_total }}">
                            <button class="btn btn-danger btn-sm btnHapusData" data-id="{{ $a->id }}"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
            <div class="row mt-3">
                <div class="col-md-8">
                    <button type="button" class="btn btn-primary mb-2 allowSubmit" id="btnTambahBarisProduct"><i class="fa fa-plus"></i> Product</button>
                </div>
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

            
            <div class="row mt-3">
                <div class="offset-md-11">
                @if(@$quote)
                <button type="button" id="submit" class="btn btn-primary">Ubah</button>
                @else
                <button type="button" id="submit"class="btn btn-primary">Simpan</button>
                @endif
                <button type="submit" id="btnSubmit" style="display:none;"></button>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>
@stop
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        quoteTransition();
        // Dapatkan semua elemen textarea
        var ckeditorInputs = document.querySelectorAll('input.thriveEditor');

        // Loop melalui setiap textarea dan pasangkan CKEditor
        ckeditorInputs.forEach(function (textarea) 
        {
            id = textarea.getAttribute('data-ids');
            generateCkEditor(id);
        });

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
            var grandTotal = parseFloat("{{ @$quote->total ?? 0 }}");
            var currentQuoteId = "{{ @$quote->id ?? '' }}";
            var currentDivisionBudgetId = "{{ @$quote->division_budget_id ?? '' }}";
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
        quoteTransition();
        $("#submit").click(function (e) 
        { 
            e.preventDefault();
            let submited = true;
            var input = document.querySelectorAll('input.thriveEditor');
            input.forEach(function (textarea) 
            {
                console.log(textarea);
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
                // let url = "{{ route('quote.destroy.product',':id') }}";
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

    // Funciton
    function quoteTransition()
    {
        var checkbox = document.getElementById('budget_transition');
        // Memilih elemen teks bebas
        var freeText = document.getElementById('quote_transition');

        if (checkbox.checked) {
            freeText.style.display = 'block';
            $("#quote_title").text("Quote Peralihan");
        } else {
            freeText.style.display = 'none';
            $("#quote_title").text("Quote Baru");
        }

        
        // Menambahkan event listener ke checkbox
        checkbox.addEventListener('change', function() {
            // Jika kotak centang tercentang, tampilkan teks bebas
            if (checkbox.checked) {
                freeText.style.display = 'block';
                $("#quote_title").text("Quote Peralihan");
            } else {
                // Jika tidak, sembunyikan teks bebas
                $("#quote_title").text("Quote Baru");
                freeText.style.display = 'none';
            }
        });
    }

    function productPrice(product,quoteProductId,callback)
    {
        $.ajax({
            type: "GET",
            url: "{{ route('quote.productPrice') }}",
            data: {product:product,quoteProductId:quoteProductId},
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
        var quote_id = "{{ @$quote->id ?? '' }}";

        $('#tableQuote tbody tr').each(function() 
        {
            var subTotal = parseFloat($(this).find('input[name="sub_total[]"]').val() || 0);
            total += subTotal;
        });

        $.ajax({
            type: "GET",
            url: "{{ route('quote.counting') }}",
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
            url: "{{ route('quote.productCounting') }}",
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

