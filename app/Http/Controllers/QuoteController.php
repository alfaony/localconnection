<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\QuoteRequest;
use Carbon\Carbon;

use App\Schemas\ParamSchema;
use App\Models\Quote;
use App\Models\QuoteProduct;
use App\Models\Product;
use App\Models\Customer;

class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $quote = Quote::byUser($request->user)
        ->OrderBy('created_at','asc')->paginate(10);
        // where('name','like', '%' . $request->get('manager') . '%')

        $totalQuote = count(Quote::get());
        return view('quote.index',compact('quote','totalQuote'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $product = Product::all();
        $customer = Customer::orderBy('created_at','desc')->get();
        $userCreate = Auth::user()->name;
        $date = Carbon::now()->format('m/Y');
        $nomor = Quote::withTrashed()->max('quote_number') + 1;
        $nomorQuote = $nomor.'/'.$date;
        
        return view('quote.createOrEdit',compact('product','customer','nomorQuote','userCreate','nomor'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(QuoteRequest $request)
    {
        try 
        {
            DB::beginTransaction();
            $no = $request->post('nomor') ?? 0; 
            $date = Carbon::now()->format('m/Y');
            $quoteNumber = Quote::withTrashed()->max('quote_number') + 1;
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
            
            $quote->user_created_id = Auth::user()->id;
            $quote->user_updated_id = Auth::user()->id;
            $quote->save();

            $product = $request->post('product');
            $description = $request->post('description');
            $qty = $request->post('qty');
            $sub_total = $request->post('sub_total');

            for ($i = 0; $i < count($product); $i++) 
            {
                $quoteProduct = new QuoteProduct;
                $quoteProduct->product_id = $product[$i];
                $quoteProduct->qty = $qty[$i];
                $quoteProduct->sub_total = $sub_total[$i];
                $quoteProduct->description = $description[$i];

                $quote->quoteProduct()->save($quoteProduct);
            }

            $this->grandTotal($quote);
            DB::commit();
            // return redirect()->to(route('quote.index'))->with('store',true);
            return redirect()->to(route('quote.download.pdf', ['slug' => $quote->slug]))->with('store',true);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);

            DB::rollback();
            Log::error($th);

            return redirect()->to(route('quote.index'))->with('false',true);

        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\Http\Response
     */
    public function edit($slug,Request $request)
    {
        $product = Product::all();
        $customer = Customer::orderBy('created_at','desc')->get();
        $quote = Quote::where('slug', $slug)->firstOrFail();

        $date = Carbon::parse($quote->created_at)->format('m/Y');
        $nomor = $request->get('nomor') ?? 0;
        $nomorQuote = $quote->quote_number_result ?? '';
        $userCreate = $quote->userCreate ? $quote->userCreate->name : '';

        return view('quote.createOrEdit',compact('product','customer','nomorQuote','quote','userCreate','nomor'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\Http\Response
     */
    public function update(QuoteRequest $request, Quote $quote)
    {
        try 
        {
            DB::beginTransaction();
            $no = $request->post('nomor') ?? 0; 
            $quote->customer_id = $request->post('customer');
            $quote->date = $request->post('date');
            $quote->tax = $request->post('tax');
            $quote->service_fee = $request->post('service_fee');
            $quote->discount = $request->post('discount');
            $quote->charges = $request->post('charges');
            $quote->total = $request->post('total');
            
            $quote->user_updated_id = Auth::user()->id;
            $quote->save();

            $product = $request->post('product');
            $description = $request->post('description');
            $qty = $request->post('qty');
            $sub_total = $request->post('sub_total');
            $ids = $request->input('ids');

            for ($i = 0; $i < count($product); $i++) 
            {

                $id = $ids[$i];
                if(!$id)
                {
                    $quoteProduct = new QuoteProduct;
                    $quoteProduct->product_id = $product[$i];
                    $quoteProduct->qty = $qty[$i];
                    $quoteProduct->sub_total = $sub_total[$i];
                    $quoteProduct->description = $description[$i];
    
                    $quote->quoteProduct()->save($quoteProduct);
                }else
                {
                    $quoteProduct = QuoteProduct::find($id);
                    $quoteProduct->product_id = $product[$i];
                    $quoteProduct->qty = $qty[$i];
                    $quoteProduct->sub_total = $sub_total[$i];
                    $quoteProduct->description = $description[$i];
                    $quoteProduct->save();
                }

            }

            $this->grandTotal($quote);
            DB::commit();

            return redirect()->to(route('quote.download.pdf', ['slug' => $quote->slug]))->with('store',true);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);

            DB::rollback();
            Log::error($th);
            return redirect()->to(route('quote.index'))->with('false',true);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\Http\Response
     */
    public function destroy(Quote $quote)
    {
        DB::beginTransaction();

        try 
        {
            // $deletedQuoteNumber = $quote->quote_number;
            
            // NULL quote
            // $quote->quote_number = NULL;
            // $quote->save();
            
            // Delete
            // Menghapus relasi terlebih dahulu
            $quote->quoteProduct()->delete();
            
            $quote->delete();

            // Mengurutkan ulang nomor quote
            // Quote::where('quote_number', '>', $deletedQuoteNumber)->decrement('quote_number');

            DB::commit();
            return redirect()->back()->with('delete', true);

        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus quote.');
        }
    }

    /**
     * Countring All
     */
    public function counting(Request $request)
    {
        $service_fee = $request->service_fee ?? 0;
        $tax = $request->tax ?? 0;

        $total = $request->total ?? 0;
        $charges = $request->charges ?? 0;
        $discount = $request->discount ?? 0;

        // return $tax;
        $totalAll = ($total + $charges) - $discount;
        $serviceFee = $service_fee != 0 ? ($totalAll * $service_fee) / ParamSchema::PERCENTAGE : 0 ;
        
        $totalAfterServiceFee = $totalAll + $serviceFee;
        $ppn = $tax != 0 ? ($totalAfterServiceFee * $tax) / ParamSchema::PERCENTAGE : 0 ;
        
        $grandTotal = $totalAfterServiceFee + $ppn;

        $result = 
        [
            'total' => $total,
            'service_fee' =>'Rp. '.number_format($serviceFee,0,',','.'),
            'ppn' => 'Rp. '.number_format($ppn,0,',','.'),
            'grand_total' => 'Rp. '.number_format($grandTotal,0,',','.'),
            // 'test' => $request->all()
        ];

        return [
            'status' => 200,
            'message' => 'okay',
            'data' => $result
        ];
    }
    /**
     * Product Countring
     */
    public function productCounting(Request $request)
    {
        $productId = $request->get('product');
        $qty = $request->get('qty');

        $product = Product::find($productId);
        $result = $product->price_sell * $qty;

        return [
            'status' => 200,
            'message' => 'okay',
            'data' => $result
        ];
    }

    /**
     * 
     * Destroy Project Save
     */
    public function destroyProduct($quoteProductId)
    {
        $quoteProduct = QuoteProduct::find($quoteProductId);

        $quote = Quote::find($quoteProduct->quote_id);
        $quoteProduct->delete();

        $this->grandTotal($quote);

        return true;
    }

    /**
     * Download PDF
     */
    public function downloadPdf($slug)
    {
        $product = Product::all();
        $customer = Customer::all();
        $quote = Quote::where('slug', $slug)->firstOrFail();
        $date = Carbon::parse($quote->created_at)->format('m/Y');
        $nomorQuote = $quote->number_result;

        $userCreate = $quote->userCreate ? $quote->userCreate->name : '';
        // $no = $;

        return view('quote.pdf',compact('product','customer','nomorQuote','quote','userCreate'));
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
        $serviceFee = $service_fee != 0 ? ($totalAll * $service_fee) / ParamSchema::PERCENTAGE : 0 ;
        
        $totalAfterServiceFee = $totalAll + $serviceFee;
        $ppn = $tax != 0 ? ($totalAfterServiceFee * $tax) / ParamSchema::PERCENTAGE : 0 ;
        
        $grandTotal = $totalAfterServiceFee + $ppn;

        $quote->total = $grandTotal;
        $quote->save();
    }

    /**
     * Data Table Quote
     */
    public function dataTableJson()
    {
        // Fetch data for the DataTable
        $query = Quote::query();

        // Map column indexes to column names (this may vary based on your table structure)
        $columnNames = ['number_result', 'total', 'slug'];

        // Define searchable columns
        $searchable = 
        [
            0 => 'number_result',
            1 => 'total',
        ];

        // define your bootstrap version (4 or 5)
        $bootstrap = 4;

        // Add action buttons to each row
        $actionButtons = [
            [
                'name' => 'Pdf',
                'route' => 'quote.download.pdf',
                'id' => true,
            ],
            [
                'name' => 'Edit',
                'route' => 'quote.edit',
                'id' => true,
            ],
            [
                'name' => 'Delete',
                'route' => 'quote.destroy',
                'id' => true,
            ],
        ];

        $response =  datatablesFormater($query, $columnNames, $actionButtons, $searchable, $bootstrap);

        $data = $response->getData();
        foreach ($data->data as $index => $item) 
        {
            $item->total = 'Rp. '.number_format($item->total, 0,',','.'); // Format angka dengan 2 desimal
        }

        return response()->json($data);
    }

    /**
     * 
     * Select2 Quote
     */

     public function select2(Request $request)
     {
        $quote = Quote::with('customer')->byNumberResult($request->get('number_result'))
                ->orderBy('created_at','desc')
                ->limit(6)
                ->get();
                
        return response()->json($quote);
     }
}
