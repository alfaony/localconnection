<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\QuoteRequest;
use Carbon\Carbon;

use App\Helpers\Access;

use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;

use App\Models\Quote;
use App\Models\QuoteProduct;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SettingCompany;
use App\Models\DivisionBudget;


class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('quote.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $product = Product::with('category')->byCompany(Auth::user()->company_id)->get();
        $customer = Customer::byCompany(Auth::user()->company_id)->orderBy('created_at','desc')->get();
        $userCreate = Auth::user()->name;
        $date = Carbon::now()->format('m/Y');
        $nomor = Quote::byCompany(Auth::user()->company_id)->withTrashed()->max('quote_number') + 1;
        $nomorQuote = $nomor.'/'.$date;
        $date = Carbon::now();

        $leadsFrom  = config('custom.leads_from');
        $user = Auth::user();
        $divisionIds = $user->divisions->pluck('id');

        if ($divisionIds->isEmpty()) {
            // Handle the case where the user does not belong to any divisions
            // You can return an empty collection or a message, or redirect
            return redirect()->route('quote.index')->with('error', 'Anda tidak tergabung dalam divisi manapun. Hubungi admin atau manager Anda.');
        } else {
            // Proceed with fetching objectives related to the user's divisions

            $divisionBudget = DivisionBudget::whereHas('division', function ($query) use ($divisionIds) 
            {
                $query->whereIn('id', $divisionIds);
            })
            ->where('is_approved', true)
            ->get();
        }
        
        return view('quote.createOrEdit',compact('product','customer','nomorQuote','userCreate','nomor','leadsFrom','divisionBudget'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(QuoteRequest $request)
    {
        DB::beginTransaction();
        try 
        {
            $no = $request->post('nomor') ?? 0; 
            $date = Carbon::now()->format('m/Y');
            $quoteNumber = Quote::byCompany(Auth::user()->company_id)->withTrashed()->max('quote_number') + 1;
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
            $quote->budget_transition = $request->post('budget_transition') ? true : false;
            $quote->quote_transition = $request->post('quote_transition');
            $quote->payment_term = $request->post('payment_term');
            $quote->third_party_docs = $request->post('third_party_docs');
            $quote->leads_from = $request->leads_from ?? NULL;
            $quote->division_budget_id = $request->division_budget ?? NULL;
            
            $quote->user_created_id = Auth::user()->id;
            $quote->user_updated_id = Auth::user()->id;
            $quote->save();

            $product = $request->post('product');
            $description = $request->post('description');
            $qty = $request->post('qty');
            $price = $request->post('price');
            $sub_total = $request->post('sub_total');

            for ($i = 0; $i < count($product); $i++) 
            {
                $quoteProduct = new QuoteProduct;
                $quoteProduct->sort = $i + 1;
                $quoteProduct->product_id = $product[$i];
                $quoteProduct->qty = $qty[$i];
                $quoteProduct->price_sell = $price[$i];
                $quoteProduct->sub_total = $sub_total[$i];
                $quoteProduct->description = $description[$i];

                $quote->quoteProduct()->save($quoteProduct);
            }

            $grandTotal = $this->grandTotal($quote);
            // Kurangi jumlah budget
            if ($request->division_budget) {
                $this->adjustBudget($request->division_budget, -$grandTotal);
            }

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
        $product = Product::with('category')->byCompany(Auth::user()->company_id)->get();
        $customer = Customer::byCompany(Auth::user()->company_id)->orderBy('created_at','desc')->get();
        $quote = Quote::where('slug', $slug)->firstOrFail();
        $leadsFrom  = config('custom.leads_from');
        $user = Auth::user();
        $divisionIds = $user->divisions->pluck('id');

        if ($divisionIds->isEmpty()) {
            // Handle the case where the user does not belong to any divisions
            // You can return an empty collection or a message, or redirect
            return redirect()->route('quote.index')->with('error', 'Anda tidak tergabung dalam divisi manapun. Hubungi admin atau manager Anda.');
        } else {
            // Proceed with fetching objectives related to the user's divisions

            $divisionBudget = DivisionBudget::whereHas('division', function ($query) use ($divisionIds) 
            {
                $query->whereIn('id', $divisionIds);
            })
            ->where('is_approved', true)
            ->get();
        }

        $date = Carbon::parse($quote->created_at)->format('m/Y');
        $nomor = $request->get('nomor') ?? 0;
        $nomorQuote = $quote->quote_number_result ?? '';
        $userCreate = $quote->userCreate ? $quote->userCreate->name : '';

        return view('quote.createOrEdit',compact('product','customer','nomorQuote','quote','userCreate','nomor','leadsFrom','divisionBudget'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\Http\Response
     */
    public function update(QuoteRequest $request, $slug)
    {
        DB::beginTransaction();
        try 
        {
            $quote = Quote::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            
            // Jika ada perubahan pada division_budget_id, kembalikan anggaran yang lama terlebih dahulu
            if ($quote->division_budget_id) 
            {
                $this->adjustBudget($quote->division_budget_id, $quote->total);
            }
    
            // Perbarui data quote
            $quote->customer_id = $request->post('customer');
            $quote->date = $request->post('date');
            $quote->tax = $request->post('tax');
            $quote->service_fee = $request->post('service_fee');
            $quote->discount = $request->post('discount');
            $quote->charges = $request->post('charges');
            $quote->total = $request->post('total');
            $quote->budget_transition = $request->post('budget_transition') ? true : false;
            $quote->quote_transition = $request->post('quote_transition');
            $quote->payment_term = $request->post('payment_term');
            $quote->third_party_docs = $request->post('third_party_docs');
            $quote->user_updated_id = Auth::user()->id;
            $quote->leads_from = $request->leads_from ?? NULL;
            $quote->division_budget_id = $request->division_budget ?? NULL;
            $quote->save();

            // Hapus produk quote sebelumnya dan tambahkan yang baru
            $quote->quoteProduct()->delete();
            
            $product = $request->post('product');
            $description = $request->post('description');
            $qty = $request->post('qty');
            $price = $request->post('price');
            $sub_total = $request->post('sub_total');

            for ($i = 0; $i < count($product); $i++) 
            {
                $quoteProduct = new QuoteProduct;
                $quoteProduct->sort = $i + 1;
                $quoteProduct->product_id = $product[$i];
                $quoteProduct->price_sell = $price[$i];
                $quoteProduct->qty = $qty[$i];
                $quoteProduct->sub_total = $sub_total[$i];
                $quoteProduct->description = $description[$i];

                $quote->quoteProduct()->save($quoteProduct);
            }

            // Hitung grand total
            $grandTotal = $this->grandTotal($quote);

            // Kurangi anggaran dengan grand total baru
            if ($request->division_budget) {
                $this->adjustBudget($request->division_budget, -$grandTotal);
            }

            DB::commit();

            return redirect()->to(route('quote.download.pdf', ['slug' => $quote->slug]))->with('store',true);
        } catch (\Throwable $th) {
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
    public function destroy($slug)
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
            $quote = Quote::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            if ($quote->division_budget_id) 
            {
                $this->adjustBudget($quote->division_budget_id, $quote->total);
            }
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
        $division_budget_id = $request->division_budget ?? NULL;
        $quote_id = $request->quote_id ?? NULL;

        // Hitung total setelah diskon dan biaya tambahan
        $totalAll = ($total + $charges) - $discount;
        $serviceFee = $service_fee != 0 ? round(($totalAll * $service_fee) / 100) : 0;
        $totalAfterServiceFee = $totalAll + $serviceFee;
        $ppn = $tax != 0 ? round(($totalAfterServiceFee * $tax) / 100) : 0;
        $grandTotal = $totalAfterServiceFee + $ppn;

        // Ambil jumlah budget berdasarkan ID division_budget
        $budgetAmount = 0;
        $calculationExternal = false;
        $budgetSisa = 0;
        if (isset($division_budget_id)) 
        {
            $calculationExternal = true;
            $budget = DivisionBudget::find($division_budget_id);
            if ($budget) {
                $budgetAmount = $budget->amount;

                // Jika ini adalah update dan division_budget_id sama dengan yang ada di quote, tambahkan kembali grandTotal sebelumnya
                if ($quote_id) 
                {
                    $quote = Quote::find($quote_id);
                    if ($quote && ($quote->division_budget_id == $division_budget_id)) {
                        $budgetAmount += $quote->total;
                    }
                }

                $budgetSisa = $budgetAmount - $grandTotal;
            }
        }

        $save = true;
        $result = [
            'total' => $total,
            'service_fee' => 'Rp. '.number_format($serviceFee, 0, ',', '.'),
            'ppn' => 'Rp. '.number_format($ppn, 0, ',', '.'),
            'grand_total' => 'Rp. '.number_format($grandTotal, 0, ',', '.'),
            'grand_total_raw' => $grandTotal,
            'budget_amount' => $budgetAmount,
            'calculationExternal' => $calculationExternal,
            'remaining_budget' => 'Rp. '.number_format($budgetSisa, 0, ',', '.'),
        ];

        // Periksa apakah grand total melebihi division budget
        if ( isset($division_budget_id) && $grandTotal > $budgetAmount) {
            $save = false;
        }

        return [
            'status' => 200,
            'message' => 'okay',
            'save' => $save,
            'data' => $result
        ];
    }
    /**
     * productPrice
     */
    public function productPrice(Request $request)
    {
        $quoteProductId = $request->get('quoteProductId');
        $productId = $request->get('product');

        $quoteProduct = QuoteProduct::find($quoteProductId);

        if($quoteProduct)
        {
            if($quoteProduct && ($quoteProduct->product_id == $productId))
            {
                $price = $quoteProduct->price_sell;
            }else
            {
                $product = Product::find($productId);
                $price = $product->price_sell;
            }
        }else
        {
            $product = Product::find($productId);
            $price = $product->price_sell;
        }

        return 
        [
            'status' => 200,
            'message' => 'okay',
            'data' => $price
        ];
    }
    /**
     * Product Countring
     */
    public function productCounting(Request $request)
    {
        $productId = $request->get('product');
        $qty = $request->get('qty');
        $price = $request->get('price');

        $result = $price * $qty;

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
        $product = Product::byCompany(Auth::user()->company_id)->get();
        $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
        $customer = Customer::byCompany(Auth::user()->company_id)->get();
        $quote = Quote::where('slug', $slug)->firstOrFail();
        $date = Carbon::parse($quote->created_at)->format('m/Y');
        $nomorQuote = $quote->number_result;
        $today = Carbon::now()->format('d / m / Y');

        $userCreate = $quote->userCreate ? $quote->userCreate->name : '';
        // $no = $;

        return view('quote.pdf',compact('product','customer','nomorQuote','quote','userCreate','company','today'));
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

        return $grandTotal;
    }

    /**
     * Data Table Quote
     */
    public function dataTableJson()
    {
        // Fetch data for the DataTable
        $query = Quote::query();
        $query->byCompany(Auth::user()->company_id)->orderBy('quote_number', 'desc');
        // Map column indexes to column names (this may vary based on your table structure)
        $columnNames = ['number_result', 'total', 'budget_transition', 'slug'];

        // Define searchable columns
        $searchable = 
        [
            0 => 'number_result',
            1 => 'total',
        ];

        // define your bootstrap version (4 or 5)
        $bootstrap = 4;

        // Add action buttons to each row
        $actionButtons = [];

        if(Access::can('downloadPdf','quotes'))
        {
            $pdf = [
                'name' => 'Pdf',
                'route' => 'quote.download.pdf',
                'id' => true,
            ];

            array_push($actionButtons,$pdf);
        }

        if(Access::can('edit','quotes'))
        {
            $edit = [
                'name' => 'Edit',
                'route' => 'quote.edit',
                'id' => true,
            ];

            array_push($actionButtons,$edit);
        }

        if(Access::can('destroy','quotes'))
        {
            $destroy = [
                'name' => 'Delete',
                'route' => 'quote.destroy',
                'id' => true,
            ];

            array_push($actionButtons,$destroy);
        }


        $response =  datatablesFormater($query, $columnNames, $actionButtons, $searchable, $bootstrap);

        $data = $response->getData();
        foreach ($data->data as $index => $item) 
        {
            $item->total = 'Rp. '.number_format($item->total, 0,',','.'); // Format angka dengan 2 desimal
            $color = $item->budget_transition ? 'badge badge-success' : 'badge badge-primary';
            $item->budget_transition = $item->budget_transition ? "<span class='badge $color'>Peralihan</span>" : "<span class='badge $color'>Baru</span>";
        }

        return response()->json($data);
    }

    /**
     * 
     * Select2 Quote
     */

     public function select2(Request $request)
     {
        $quote = Quote::byCompany(Auth::user()->company_id)->with('customer')->byNumberResult($request->get('number_result'))
                ->orderBy('created_at','desc')
                ->limit(6)
                ->get();
                
        return response()->json($quote);
     }

     private function adjustBudget($division_budget_id, $amount)
    {
        $budget = DivisionBudget::find($division_budget_id);
        if ($budget) {
            $budget->amount += $amount;
            $budget->save();
        }
    }
}
