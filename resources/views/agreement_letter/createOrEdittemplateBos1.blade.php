@extends('adminlte::page')

@section('content')
<div class="container mt-3">
    <div class="card">
        <div class="card-body">
            @if(@$agreementLetter)
            <form method="post" action="{{ route('agreement-letter.update',$agreementLetter) }}">
                @method('put')
            @else
            <form method="post" action="{{ route('agreement-letter.store') }}">
            @endif
                @csrf
                <div class="form-group row">
                    <div class="col-md-6">
                        <h2>Surat Perjanjian</h2>
                        <div class="mt-5">No Surat Perjanjain: {{ $nomorAgreementLetter ?? '' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label for="date" class="col-sm-8 col-form-label text-right">Tanggal:</label>
                            <div class="col-sm-4">
                                <input type="date" name="date" class="form-control" id="date" value="{{ old('date') ?? @$agreementLetter->date }}" required>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <label class="col-sm-8 col-form-label text-right">Finance:</label>
                            <div class="col-sm-4">
                                <span class="form-control-plaintext">{{ $userCreate ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
        
                <div class="form-group row">
                    <label for="quote" class="col-sm-2 col-form-label">Pilih No. Quote:</label>
                    <div class="col-sm-5">
                        <input type="hidden" name="quote_id" value="{{ old('quote') ?? @$agreementLetter->quote_id }}">
                        <select class="form-control select2" name="quote" id="quote">
                           
                        </select>
                    </div>
                </div>
        
                <div class="form-group row">
                    <label for="customer" class="col-sm-2 col-form-label">Customer:</label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" id="customer" value="" placeholder="Pilih Quote" readonly>
                    </div>
                </div>
                
                <div class="row">

                    <!-- English -->
                    <div class="col-md-5 mt-3">
                        <div class="form-group">
                            <label for="pembayaran">Payment Term Clause </label>            
                            <input class="thriveEditor form-control" id="description_payment_term_english" data-ids="payment_term_english" name="payment_term_english" rows="3" placeholder="yang akan dicetak di perjanjian" value="{{ old('payment_term_english') ?? @$agreementLetter->payment_term_english }}"/>
                        </div>
                
                        <div class="form-group">
                            <label for="periode">Agreement Period Clause</label>
                            <input class="thriveEditor form-control" id="description_period_term_english" data-ids="period_term_english" name="period_term_english" rows="3" placeholder="yang akan dicetak di perjanjian" value="{{ old('period_term_english') ?? @$agreementLetter->period_term_english }}"/>
                        </div>
                
                        <div class="form-group">
                            <label for="tambahan">Other Additional Clause</label>
                            <input class="thriveEditor form-control" id="description_other_term_english" data-ids="other_term_english" rows="3" name="other_term_english" placeholder="yang akan dicetak di perjanjian" value="{{ old('other_term_english') ?? @$agreementLetter->other_term_english }}" />
                        </div>
                    </div>

                    <!-- Indonesia -->
                    <div class="offset-1 col-md-5 mt-3">
                        <div class="form-group">
                            <label for="pembayaran">Klausul Termin Pembayaran</label>
                            <input class="thriveEditor form-control" id="description_payment_term" data-ids="payment_term"  id="payment_term" name="payment_term" rows="3" placeholder="yang akan dicetak di perjanjian" value="{{ old('payment_term') ?? @$agreementLetter->payment_term }}"/>
                        </div>
                
                        <div class="form-group">
                            <label for="periode">Klausul Periode Perjanjian</label>
                            <input class="thriveEditor form-control" id="description_period_term" data-ids="period_term" id="period_term" name="period_term" rows="3" placeholder="yang akan dicetak di perjanjian" value="{{ old('period_term') ?? @$agreementLetter->period_term }}"/>
                        </div>
                
                        <div class="form-group">
                            <label for="tambahan">Klausul Tambahan Lain</label>
                            <input class="thriveEditor form-control" id="description_other_term" data-ids="other_term"  id="other_term" rows="3" name="other_term" placeholder="yang akan dicetak di perjanjian" value="{{ old('other_term') ?? @$agreementLetter->other_term }}" />
                        </div>
                    </div>
    
                </div>
        
                <div class="form-group text-right">
                    @if(@$agreementLetter)
                    <button type="submit" class="btn btn-primary">Ubah</button>
                    @else
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
    $(document).ready(function () 
    {
        $('#quote').select2({
            placeholder: 'Pilih Nomor Quote Baru',
            ajax: 
            {
                url: "{{ route('quote.select2') }}",
                dataType: 'json',
                data: function(params) {
                    return {
                        number_result: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(quote) {
                            console.log(quote.customer.name);
                            return {
                                id: quote.id,
                                text: quote.number_result,
                                data: 
                                {
                                    customer: quote.customer ? quote.customer.name : ''
                                }
                            };
                        })
                    };
                }
            }
        });

        
        $('#quote').on('select2:select', function(e) {
            // Ambil data-customer dari opsi yang dipilih
            // console.log(e);
            var customerName = e.params.data.data.customer;
            // Menampilkan nilai tersebut di elemen dengan id "customer"
            $("#customer").val(customerName);
        });

        var selectedValueQuote = "{{ @$agreementLetter->quote_id }}";
        if(selectedValueQuote)
        {
            title = "{{ @$agreementLetter->quote->number_result }}";
            customerName = "{{ @$agreementLetter->quote->customer->name }}";
            // Create an option element with the selected value
            var newOption = new Option(title, selectedValueQuote, true, true);
    
            // Append the option to the select2 element and trigger change
            $('#quote').append(newOption).trigger('change');
            $("#customer").val(customerName);
        }
        
        // updateCustomerField();

        // $('.select2').select2({
        //     width: '100%',
        //     placeholder: 'Pilih Quote'
        // });

        // $(".select2").on("change", updateCustomerField);


    });


    // function updateCustomerField() 
    // {
    //     // Mendapatkan nilai dari atribut data-customer
    //     var customerName = $(".select2").find("option:selected").data("customer");
        
    //     // Menampilkan nilai tersebut di elemen dengan id "customer"
    //     $("#customer").val(customerName);
    // }
</script>
@stop
@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<style>
   body 
   {
        font-family: Arial, sans-serif;
        /* padding: 20px; */
        background-color: #f4f4f4;
    }
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
