<?php

namespace App\Http\Controllers\API;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


use App\Http\Requests\QuoteRequest;
use Carbon\Carbon;

use App\Helpers\Access;

use App\Schemas\ParamSchema;
use App\Models\Quote;
use App\Models\QuoteProduct;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SettingCompany;

class QuoteController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paginate = 10;
        if ($request->per_page) {
            $paginate = $request->per_page;
        }
        $order = 'asc';
        if ($request->order == 'desc') 
        {
            $order = 'desc';
        }
        $quote = Quote::byCompany(auth()->user()->company_id)
        ->where('number_result','like', '%' . $request->get('quote') . '%')
        ->orderBy('quote_number',$order)->paginate($paginate)->toArray();

        return $this->sendResponse($quote,'success');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer' => 'required|exists:customers,id',
            'date' => 'required|date',
            'tax' => 'nullable|numeric|min:0|max:100',
            'service_fee' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric',
            'charges' => 'nullable|numeric',
            'products' => 'required|array',
            'products.*.product_id' => 'required|uuid|exists:products,id',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.qty' => 'required|integer|min:1',
            'products.*.description' => 'required|string',
        ], [
            'customer.required' => 'Customer ID diperlukan.',
            'customer.exists' => 'Customer tidak ditemukan.',
            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'tax.numeric' => 'Pajak harus dalam format angka.',
            'tax.min' => 'Pajak minimal 0%.',
            'tax.max' => 'Pajak maksimal 100%.',
            'service_fee.numeric' => 'Biaya layanan harus dalam format angka.',
            'service_fee.min' => 'Biaya layanan minimal 0%.',
            'service_fee.max' => 'Biaya layanan maksimal 100%.',
            'discount.numeric' => 'Diskon harus dalam format angka.',
            'charges.numeric' => 'Biaya tambahan harus dalam format angka.',
            'products.required' => 'Produk diperlukan.',
            'products.array' => 'Produk harus berupa array.',
            'products.*.product_id.required' => 'ID Produk diperlukan.',
            'products.*.product_id.uuid' => 'ID Produk harus berupa UUID.',
            'products.*.product_id.exists' => 'Produk tidak ditemukan.',
            'products.*.price.required' => 'Harga produk diperlukan.',
            'products.*.price.numeric' => 'Harga produk harus dalam format angka.',
            'products.*.price.min' => 'Harga produk minimal adalah 0.',
            'products.*.qty.required' => 'Kuantitas produk diperlukan.',
            'products.*.qty.integer' => 'Kuantitas produk harus berupa angka bulat.',
            'products.*.qty.min' => 'Kuantitas produk minimal adalah 1.',
            'products.*.description.required' => 'Deskripsi produk diperlukan.',
            'products.*.description.string' => 'Deskripsi produk harus berupa string.',
        ]);
        
    
        if($validator->fails())
        {
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        try 
        {
            DB::beginTransaction();
            $no = $request->post('nomor') ?? 0; 
            $date = Carbon::now()->format('m/Y');
            $quoteNumber = Quote::byCompany(auth()->user()->company_id)->withTrashed()->max('quote_number') + 1;
            $numberResult = $quoteNumber.'/'.$date;

            $quote = new Quote();
            $quote->quote_number = $quoteNumber;
            $quote->number_result = $numberResult;
            $quote->customer_id = $request->post('customer');
            $quote->date = $request->post('date');
            $quote->tax = $request->post('tax');
            $quote->service_fee = $request->post('service_fee');
            $quote->discount = $request->post('discount');
            $quote->charges = $request->post('charges');
            $quote->total = $request->post('total');
            
            $quote->user_created_id = auth()->user()->id;
            $quote->user_updated_id = auth()->user()->id;
            $quote->save();

            $i = 1;
            foreach ($request->post('products') as $a) 
            {
                $quoteProduct = new QuoteProduct;
                $quoteProduct->sort = $i + 1;
                $quoteProduct->product_id = $a['product_id'];
                $quoteProduct->description = $a['description'];
                $quoteProduct->qty = $a['qty'];
                $quoteProduct->price_sell = $a['price'];
                $quoteProduct->sub_total = $a['price'] ? $a['price'] * $a['qty'] : 0;

                $quote->quoteProduct()->save($quoteProduct);
                $i++;
            }

            $this->grandTotal($quote);
            DB::commit();
            
            $data= $quote;
            $data['url'] =  url("/quote/downloadPdf/pdf/".$quote->slug);
    
            return $this->sendResponse($data,'success');

        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);

            DB::rollback();
            Log::error($th);
            return $this->sendError("error",$th->getMessage());

        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['product'] = Product::byCompany(auth()->user()->company_id)->get()->toArray();
        $data['customer'] = Customer::byCompany(auth()->user()->company_id)->orderBy('created_at','desc')->get()->toArray();
        $data['userCreate'] = auth()->user()->name;
        $date = Carbon::now()->format('m/Y');
        $nomor = Quote::byCompany(auth()->user()->company_id)->withTrashed()->max('quote_number') + 1;
        $data['nomorQuote'] = $nomor.'/'.$date;
        $data['dateNow'] = Carbon::now();
        
        return $this->sendResponse($data,'success');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $quote = Quote::byCompany(auth()->user()->company_id)->with('quoteProduct')->where('id',$id)->first();
        if(empty($quote))
        {
            return $this->sendError('Quote Not Found');
        }else
        {
            return $this->sendResponse($quote,"Success");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $quote = Quote::byCompany(auth()->user()->company_id)->where('id',$id)->first();
        if(empty($quote))
        {
            return $this->sendError('Quote Not Found');
        }else
        {
            $data['quote'] = $quote;
            $data['product'] = Product::byCompany(auth()->user()->company_id)->get()->toArray();
            $data['customer'] = Customer::byCompany(auth()->user()->company_id)->orderBy('created_at','desc')->get()->toArray();
            $data['userCreate'] = auth()->user()->name;
            $date = Carbon::now()->format('m/Y');
            $nomor = Quote::byCompany(auth()->user()->company_id)->withTrashed()->max('quote_number') + 1;
            $data['nomorQuote'] = $nomor.'/'.$date;
            $data['dateNow'] = Carbon::now();

            return $this->sendResponse($data,"Success");
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $quote = Quote::byCompany(auth()->user()->company_id)
                ->where('id',$id)->first();
        if(empty($quote))
        {
            return $this->sendError('Product Not Found');
        }
        
        $validator = Validator::make($request->all(), [
            'customer' => 'required|exists:customers,id',
            'date' => 'required|date',
            'tax' => 'nullable|numeric|min:0|max:100',
            'service_fee' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric',
            'charges' => 'nullable|numeric',
            'products' => 'required|array',
            'products.*.product_id' => 'required|uuid|exists:products,id',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.qty' => 'required|integer|min:1',
            'products.*.description' => 'required|string',
        ], [
            'customer.required' => 'Customer ID diperlukan.',
            'customer.exists' => 'Customer tidak ditemukan.',
            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'tax.numeric' => 'Pajak harus dalam format angka.',
            'tax.min' => 'Pajak minimal 0%.',
            'tax.max' => 'Pajak maksimal 100%.',
            'service_fee.numeric' => 'Biaya layanan harus dalam format angka.',
            'service_fee.min' => 'Biaya layanan minimal 0%.',
            'service_fee.max' => 'Biaya layanan maksimal 100%.',
            'discount.numeric' => 'Diskon harus dalam format angka.',
            'charges.numeric' => 'Biaya tambahan harus dalam format angka.',
            'products.required' => 'Produk diperlukan.',
            'products.array' => 'Produk harus berupa array.',
            'products.*.product_id.required' => 'ID Produk diperlukan.',
            'products.*.product_id.uuid' => 'ID Produk harus berupa UUID.',
            'products.*.product_id.exists' => 'Produk tidak ditemukan.',
            'products.*.price.required' => 'Harga produk diperlukan.',
            'products.*.price.numeric' => 'Harga produk harus dalam format angka.',
            'products.*.price.min' => 'Harga produk minimal adalah 0.',
            'products.*.qty.required' => 'Kuantitas produk diperlukan.',
            'products.*.qty.integer' => 'Kuantitas produk harus berupa angka bulat.',
            'products.*.qty.min' => 'Kuantitas produk minimal adalah 1.',
            'products.*.description.required' => 'Deskripsi produk diperlukan.',
            'products.*.description.string' => 'Deskripsi produk harus berupa string.',
        ]);
        
    
        if($validator->fails())
        {
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        try 
        {
            DB::beginTransaction();

            $quote->customer_id = $request->post('customer');
            $quote->date = $request->post('date');
            $quote->tax = $request->post('tax');
            $quote->service_fee = $request->post('service_fee');
            $quote->discount = $request->post('discount');
            $quote->charges = $request->post('charges');
            $quote->total = $request->post('total');
            
            $quote->user_created_id = auth()->user()->id;
            $quote->user_updated_id = auth()->user()->id;
            $quote->save();

            // Destroy Product Quote
            $quote->quoteProduct()->delete();

            $i = 1;
            foreach ($request->post('products') as $a) 
            {
                $quoteProduct = new QuoteProduct;
                $quoteProduct->sort = $i + 1;
                $quoteProduct->product_id = $a['product_id'];
                $quoteProduct->description = $a['description'];
                $quoteProduct->qty = $a['qty'];
                $quoteProduct->price_sell = $a['price'];
                $quoteProduct->sub_total = $a['price'] ? $a['price'] * $a['qty'] : 0;

                $quote->quoteProduct()->save($quoteProduct);
                $i++;
            }

            $this->grandTotal($quote);
            DB::commit();
            
            return $this->sendMessage("Success");

        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);

            DB::rollback();
            Log::error($th);
            return $this->sendError("error",$th->getMessage());

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try 
        {
            // Delete
            $quote = Quote::byCompany(auth()->user()->company_id)->where('id', $id)->first();
            if(empty($quote))
            {
                return $this->sendError('Quote Not Found');
            }
            $quote->quoteProduct()->delete();
            
            $quote->delete();


            DB::commit();
            return $this->sendMessage('success');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError("error",$e->getMessage());

        }
    }

    /**
     * Download PDF
     */
    public function downloadPdf($slug)
    {
        $quote = Quote::where('slug', $slug)->first();
        if(empty($quote))
        {
            return $this->sendError('Quote Not Found');
        }

        $product = Product::byCompany(auth()->user()->company_id)->get();
        $company = SettingCompany::byCompany(auth()->user()->company_id)->get()->pluck('field_value','field_title');
        $customer = Customer::byCompany(auth()->user()->company_id)->get();
        $date = Carbon::parse($quote->created_at)->format('m/Y');
        $nomorQuote = $quote->number_result;
        $today = Carbon::now()->format('d / m / Y');

        $userCreate = $quote->userCreate ? $quote->userCreate->name : '';
        $counting = $this->counting($quote);

        return view('quote.pdfApi',compact('product','customer','nomorQuote','quote','userCreate','company','today','counting'));
    }
    /**
     * Total After PPN dll
     */
    private function grandTotal($quote)
    {
        $service_fee = $quote->service_fee ?? 0;
        $tax = $quote->tax ?? 0;

        $total =  $quote->quoteProduct() ? $quote->quoteProduct()->sum('sub_total') : 0;
        $charges = $quote->charges ?? 0;
        $discount = $quote->discount ?? 0;

        // return $tax;
        $totalAll = ($total + $charges) - $discount;
        $serviceFee = $service_fee != 0 ? round(($totalAll * $service_fee) / ParamSchema::PERCENTAGE) : 0 ;
        
        $totalAfterServiceFee = $totalAll + $serviceFee;
        $ppn = $tax != 0 ? round(($totalAfterServiceFee * $tax) / ParamSchema::PERCENTAGE) : 0 ;
        
        $grandTotal = $totalAfterServiceFee + $ppn;

        $quote->total = $grandTotal;
        $quote->save();
    }

    public function counting($quote)
    {
        $service_fee = $quote->service_fee ?? 0;
        $tax = $quote->tax ?? 0;

        $total =  $quote->quoteProduct() ? $quote->quoteProduct()->sum('sub_total') : 0;
        $charges = $quote->charges ?? 0;
        $discount = $quote->discount ?? 0;

        // return $tax;
        $totalAll = ($total + $charges) - $discount;
        $serviceFee = $service_fee != 0 ? round(($totalAll * $service_fee) / ParamSchema::PERCENTAGE) : 0 ;
        
        $totalAfterServiceFee = $totalAll + $serviceFee;
        $ppn = $tax != 0 ? round(($totalAfterServiceFee * $tax) / ParamSchema::PERCENTAGE) : 0 ;
        
        $grandTotal = $totalAfterServiceFee + $ppn;

        return 
        [
            'tax_percentage' => $tax ? $tax.'%' : '0%',
            'service_fee_percentage' => $service_fee ? $service_fee.'%' : '0%',
            'discount' => $discount ? 'Rp. '.number_format($discount,0,',','.') : 'Rp. 0',
            'charges' => $charges ? 'Rp. '.number_format($charges,0,',','.') : 'Rp. 0',
            'total' => $total ? 'Rp. '.number_format($total,0,',','.') : 'Rp. 0',
            'service_fee' => $serviceFee ?  'Rp. '.number_format($serviceFee,0,',','.') : 'Rp. 0',
            'ppn' => $ppn ? 'Rp. '.number_format($ppn,0,',','.') : 'Rp. 0',
            'grand_total' => $grandTotal ? 'Rp. '.number_format($grandTotal,0,',','.') : 'Rp 0',
        ];
    }
}
