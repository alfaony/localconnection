<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Form</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <!-- <div class="card">
        <div class="card-body"> -->
            <div class="row">
                <div class="offset-md-8 col-4">
                    <div class="row">
                        <label for="date" class="col-sm-4 col-form-label text-right">Tanggal:</label>
                        <div class="col-sm-8">
                            <span class="form-control-plaintext">{{ $quote->date }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <label for="sales" class="col-sm-4 col-form-label text-right">Sales:</label>
                        <div class="col-sm-8">
                            <span class="form-control-plaintext">{{ $userCreate ?? '' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="mt-3">
                <div class="form-group row">
                    <label class="col-2 col-form-label">No Quotation:</label>
                    <div class="col-6">
                        <span class="form-control-plaintext">{{ $nomorQuote }}</span>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-2 col-form-label">Nama Customer:</label>
                    <div class="col-6">
                        <span class="form-control-plaintext">{{ $quote->customer ? $quote->customer->name : '' }}</span>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-2 col-form-label">Pajak:</label>
                    <div class="col-6">
                        <span class="form-control-plaintext">{{ $quote->tax."%" ?? "" }}</span>
                        <input type="hidden" name="tax" id="tax" class="form-control calculation minNol" min="0" placeholder="10" value="{{ old('tax') ?? @$quote->tax }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-2 col-form-label">Service Fee:</label>
                    <div class="col-6">
                        <span class="form-control-plaintext">{{ $quote->service_fee."%" ?? "" }}</span>
                        <input type="hidden" name="service_fee" id="service_fee" class="form-control calculation minNol" min="0" placeholder="10" value="{{ old('service_fee') ?? @$quote->service_fee }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-2 col-form-label">Discount:</label>
                    <div class="col-6">
                        <span class="form-control-plaintext">{{ 'Rp. '.number_format($quote->discount,0,',','.') ?? "" }}</span>
                        <input type="hidden" class="form-control" name="discount" id="discount" value="{{ old('discount') ?? @$quote->discount }}" />
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-2 col-form-label">Other Tax/Charges:</label>
                    <div class="col-6">
                        <span class="form-control-plaintext">{{ 'Rp. '.number_format($quote->charges,0,',','.') ?? "" }}</span>
                        <input type="hidden" class="form-control" name="charges" id="charges" value="{{ old('charges') ?? @$quote->charges }}" />
                    </div>
                </div>
            </div>


            <table class="table table-bordered mt-3" id="tableQuote">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Produk/Jasa</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @if(@$quote)
                    @php $nomorBaris = 1; @endphp
                    @foreach($quote->quoteProduct as $a)
                    <tr data-key="{{ $a->id }}">
                        <td>
                            {{ $nomorBaris++ }}
                            </td>
                        <td>
                            {{ $a->product ? $a->product->name : '' }}
                            <input type="hidden" class="form-control" placeholder="Total" id="" name="ids[]" value="{{ $a->id }}">
                            <input type="hidden" class="form-control" placeholder="Total" id="sub_total_{{ $a->id }}" name="sub_total[]" value="{{ $a->sub_total }}">
                        </td>
                        <td>
                            {{ $a->description ?? '' }}
                            <input type="hidden" name="description[]" id="description_{{ $a->id }}" class="form-control" placeholder="Description" value="{{ old('description') ?? @$a->description }}" required>
                            </td>
                        <td>
                            {{ $a->qty ?? '' }}
                            <input type="hidden" id="qty_{{ $a->id }}" name="qty[]" data-key="{{ $a->id }}" min="1" class="form-control qtyChange" placeholder="Quantity" value="{{ old('qty') ?? @$a->qty }}" required>
                        </td>
                        <td id="sub_total_show_{{ $a->id }}">
                            {{ 'Rp. '.number_format($a->sub_total,0,',','.') }}
                        </td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        
            <div class="row mt-3">
                <div class="col-4 offset-8 mt-4">
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
                        <div>Service Fee:</div>
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
                    </div>
                </div>
            </div>
        <!-- </div>
    </div> -->
</div>
</body>
</html>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<script>
    $(document).ready(function () 
    {
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
        var sub_total = 0;
        $('#tableQuote tbody tr').each(function() 
        {
            // console.log($(this).find('input[name="sub_total[]"]'));
            var subTotal = parseFloat($(this).find('input[name="sub_total[]"]').val() || 0);
            console.log(subTotal);
            sub_total += subTotal;
        });


        $.ajax({
            type: "GET",
            url: "{{ route('quote.counting') }}",
            data: {total:sub_total,tax:tax,service_fee:service_fee,discount:discount,charges:charges},
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

                prinsts();
            }
        });

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
        name = "{{ $nomorQuote }}"

        window.addEventListener("beforeprint", (event) => 
        {
            document.title=name;
        });
        window.print();
    }
</script>
