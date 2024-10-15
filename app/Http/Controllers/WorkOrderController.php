<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Helpers\Access;

use Carbon\Carbon;
use App\Http\Requests\WorkOrderRequest;

use App\Models\WorkOrder;
use App\Models\WorkOrderProduct;
use App\Models\Product;
use App\Models\Quote;
use App\Models\SettingCompany;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('work_order.index');
    }

    /**
     * Create Suggestion 
     */
    public function createsuggest($slug)
    {   
        $quoteSuggestion = Quote::ByCompany(Auth::user()->company_id)->whereDoesntHave('workOrder')->where('slug', $slug)->first();
        if(!$quoteSuggestion)
        {
            return redirect()->to(route('work_order.index'))->with('datanotfound',true);
        }

        $product = Product::with('category')->byCompany(Auth::user()->company_id)->get();
        $quote = Quote::ByCompany(Auth::user()->company_id)->whereDoesntHave('workOrder')->orderBy('created_at','desc')->get();

        $userCreate = Auth::user()->name;
        $nomorWorkOrder = $this->workOrderNumber();

        return view('work_order.createOrEdit',compact('product','userCreate','nomorWorkOrder','quote','quoteSuggestion'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $product = Product::with('category')->byCompany(Auth::user()->company_id)->get();
        $quote = Quote::ByCompany(Auth::user()->company_id)->whereDoesntHave('workOrder')->orderBy('created_at','desc')->get();

        $userCreate = Auth::user()->name;
        $nomorWorkOrder = $this->workOrderNumber();

        return view('work_order.createOrEdit',compact('product','userCreate','nomorWorkOrder','quote'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WorkOrderRequest $request)
    {
        DB::beginTransaction();
        try {
            $workOrderNumber = WorkOrder::byCompany(Auth::user()->company_id)->withTrashed()->max('work_order_number') + 1;

            // Simpan data WorkOrder
            $workOrder = new WorkOrder();
            $workOrder->date = $request->post('date');
            $workOrder->quote_id = $request->post('quote');
            $workOrder->work_order_number = $workOrderNumber;
            $workOrder->number_result = $this->workOrderNumber();
            $workOrder->user_created_id = Auth::user()->id;
            $workOrder->user_updated_id = Auth::user()->id;
            
            $workOrder->save();

            // Jika WorkOrder berhasil disimpan, simpan produk terkait
            if ($workOrder) 
            {
                for ($i = 0; $i < count($request->post('product')); $i++) {
                    $workOrderProduct = new WorkOrderProduct();
                    $workOrderProduct->sort = $i + 1;
                    $workOrderProduct->work_order_id = $workOrder->id;
                    $workOrderProduct->product_id = $request->post('product')[$i];
                    $workOrderProduct->description = $request->post('description')[$i];
                    $workOrderProduct->qty = $request->post('qty')[$i];
                    $workOrderProduct->price_buy = $request->post('price')[$i];
                    $workOrderProduct->sub_total = $request->post('sub_total')[$i];
                   
                    $workOrder->workOrderProduct()->save($workOrderProduct);
                }
            }

            if ($request->hasFile('quote_file')) 
            {
                $file = $request->file('quote_file');
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);  // Dapatkan nama file tanpa ekstensi
                $extension = $file->getClientOriginalExtension();  // Dapatkan ekstensi file
                $filenameToStore = $filename.'_'.time().'_'.Str::uuid().'.'.$extension;  // Hasilkan nama file yang unik

                $filePath = $file->storeAs('quotes', $filenameToStore, 'public');
            }
            
            $workOrder->quote_file = $filePath;  // Menyimpan path dari file yang disimpan
            $workOrder->total = $workOrder->workOrderProduct()->sum('sub_total');

            $workOrder->save();

            DB::commit();
            // return redirect()->to(route('work-order.index'))->with('store',true);
            return redirect()->to(route('work-order.download.pdf', ['slug' => $workOrder->slug]))->with('store',true);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            Log::error($th);
            // dd($th);
            return redirect()->to(route('work-order.index'))->with('store',false);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WorkOrder  $workOrder
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        // dd($slug);
        $product = Product::with('category')->byCompany(Auth::user()->company_id)->get();
        
        $workOrder = WorkOrder::where('slug', $slug)->firstOrFail();
        $quote = Quote::ByCompany(Auth::user()->company_id)->whereDoesntHave('workOrder')->orWhere('id',$workOrder->quote_id)->orderBy('created_at','desc')->get();
        $userCreate = $workOrder->userCreate ? $workOrder->userCreate->name : '';
        $nomorWorkOrder = $workOrder->number_result ?? '';

        return view('work_order.createOrEdit',compact('product','userCreate','nomorWorkOrder','workOrder','quote'));
    }
    
    /**
     * Suggetion When Choose Qute
     */
    public function suggestionQuote($id)
    {
        $quote = Quote::find($id);
        $quoteProduct = $quote->quoteProduct 
        ? $quote->quoteProduct()
                ->select('product_id', 'qty', 'description', 'sort')
                ->orderBy('sort')
                ->get()
                ->map(function($item) {
                    return [
                        'product_id' => $item->product_id,
                        'qty' => $item->qty,
                        'description' => $item->description,
                    ];
                })
        : collect();
        $quoteCustomer = $quote->customer ? $quote->customer->name : '' ;
        
        $data = 
        [
            'customer' => $quoteCustomer,
            'product' => $quoteProduct
        ];
        return $data;
    }
     /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WorkOrder  $workOrder
     * @return \Illuminate\Http\Response
     * Download Pdf
     */
    public function downloadPdf($slug)
    {
        // dd($slug);
        $product = Product::byCompany(Auth::user()->company_id)->get();
        $quote = Quote::byCompany(Auth::user()->company_id)->get();
        
        $workOrder = WorkOrder::where('slug', $slug)->firstOrFail();
        $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
        $userCreate = $workOrder->userCreate ? $workOrder->userCreate->name : '';
        $nomorWorkOrder = $workOrder->number_result ?? '';

        return view('work_order.pdf',compact('product','quote','userCreate','nomorWorkOrder','workOrder' ,'company'));
    }


     /* Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WorkOrder  $workOrder
     * @return \Illuminate\Http\Response
     */
    public function update(WorkOrderRequest $request, $slug)
    {
        DB::beginTransaction();
        try {
            // Simpan data WorkOrder
            $workOrder = WorkOrder::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            $workOrder->date = $request->post('date');
            $workOrder->quote_id = $request->post('quote');
            $workOrder->user_created_id = Auth::user()->id;
            $workOrder->user_updated_id = Auth::user()->id;
            
            $workOrder->save();

            // Destroy workOrder
            $workOrder->workOrderProduct()->delete();
            
            // Jika WorkOrder berhasil disimpan, simpan produk terkait
            if ($workOrder) 
            {
                for ($i = 0; $i < count($request->post('product')); $i++) 
                {
                    $workOrderProduct = new WorkOrderProduct();
                    $workOrderProduct->sort = $i + 1;
                    $workOrderProduct->work_order_id = $workOrder->id;
                    $workOrderProduct->product_id = $request->post('product')[$i];
                    $workOrderProduct->description = $request->post('description')[$i];
                    $workOrderProduct->qty = $request->post('qty')[$i];
                    $workOrderProduct->price_buy = $request->post('price')[$i];
                    $workOrderProduct->sub_total = $request->post('sub_total')[$i];
                   
                    $workOrder->workOrderProduct()->save($workOrderProduct);

                    // $ids = $request->post('ids')[$i];

                    // if(!$ids)
                    // {
                    //     $workOrderProduct = new WorkOrderProduct();
                    //     $workOrderProduct->sort = $i + 1;
                    //     $workOrderProduct->work_order_id = $workOrder->id;
                    //     $workOrderProduct->product_id = $request->post('product')[$i];
                    //     $workOrderProduct->description = $request->post('description')[$i];
                    //     $workOrderProduct->qty = $request->post('qty')[$i];
                    //     $workOrderProduct->price_buy = $request->post('price')[$i];
                    //     $workOrderProduct->sub_total = $request->post('sub_total')[$i];
                       
                    //     $workOrder->workOrderProduct()->save($workOrderProduct);
                    // }else
                    // {
                    //     $workOrderProduct = WorkOrderProduct::find($ids);
                    //     $workOrderProduct->sort = $i + 1;
                    //     $workOrderProduct->work_order_id = $workOrder->id;
                    //     $workOrderProduct->product_id = $request->post('product')[$i];
                    //     $workOrderProduct->description = $request->post('description')[$i];
                    //     $workOrderProduct->price_buy = $request->post('price')[$i];
                    //     $workOrderProduct->qty = $request->post('qty')[$i];
                    //     $workOrderProduct->sub_total = $request->post('sub_total')[$i];
                        
                    //     $workOrderProduct->save();
                    // }
                }
            }

            if ($request->hasFile('quote_file')) 
            {
                $file = $request->file('quote_file');
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);  // Dapatkan nama file tanpa ekstensi
                $extension = $file->getClientOriginalExtension();  // Dapatkan ekstensi file
                $filenameToStore = $filename.'_'.time().'_'.Str::uuid().'.'.$extension;  // Hasilkan nama file yang unik

                $filePath = $file->storeAs('quotes', $filenameToStore, 'public');
                $workOrder->quote_file = $filePath;  // Menyimpan path dari file yang disimpan
            }
            
            $workOrder->total = $workOrder->workOrderProduct()->sum('sub_total');

            $workOrder->save();

            DB::commit();

            return redirect()->to(route('work-order.download.pdf', ['slug' => $workOrder->slug]))->with('update',true);
            // return redirect()->to(route('work-order.index'))->with('update',true);
            // return redirect()->to(route('quote.download.pdf', ['slug' => $quote->slug, 'nomor' => $no]))->with('store',true);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            Log::error($th);
            // dd($th);
            return redirect()->to(route('work-order.index'))->with('update',false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WorkOrder  $workOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $workOrder = WorkOrder::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $workOrder->workOrderProduct()->delete();
        $workOrder->delete();

        return redirect()->back()->with('delete', true);
    }

     /**
     * 
     * Destroy Project Save
     */
    public function destroyProduct($workOrderProductId)
    {
        $workOrderProduct = WorkOrderProduct::find($workOrderProductId);
        $workOrderProduct->delete();
        
        $workOrder = WorkOrder::find($workOrderProduct->work_order_id);
        $workOrder->total = $workOrder->workOrderProduct()->sum('sub_total');
        $workOrder->save();

        return true;
    }

    /**
     * Get Product Price
     */
    public function productPrice(Request $request)
    {
        $workOrderProductId = $request->get('workOrderProductId');
        $productId = $request->get('product');

        $workOrderProduct = WorkOrderProduct::find($workOrderProductId);

        if($workOrderProduct)
        {
            if($workOrderProduct && ($workOrderProduct->product_id == $productId))
            {
                $price = $workOrderProduct->price_buy;
            }else
            {
                $product = Product::find($productId);
                $price = $product->price_buy;
            }
        }else
        {
            $product = Product::find($productId);
            $price = $product->price_buy;
        }

        return 
        [
            'status' => 200,
            'message' => 'okay',
            'data' => $price
        ];
    }
    /**
     * Product Counting QTY
     */
    public function productCounting(Request $request)
    {
        // $productId = $request->get('product');
        $qty = $request->get('qty');
        $price = $request->get('price');

        // $product = Product::find($productId);
        $result = $price * $qty;

        return [
            'status' => 200,
            'message' => 'okay',
            'data' => $result
        ];
    }

    private function workOrderNumber()
    {
        $date = Carbon::now()->format('m/Y');
        $nomor = WorkOrder::byCompany(Auth::user()->company_id)->withTrashed()->max('work_order_number') + 1;

        return $nomor.'/'.$date;

    }
    
    public function dataTableJson()
    {
        // Fetch data for the DataTable
        $query = WorkOrder::query();
        $query->byCompany(Auth::user()->company_id)->with('quote')->orderBy('work_order_number', 'desc');

        // OrderBy
        $query->orderBy('work_order_number', 'desc');
        
        
        // Map column indexes to column names (this may vary based on your table structure)
        $columnNames = ['number_result', 'total', 'slug'];

        // Define searchable columns
        $searchable = [
            0 => 'number_result',
            1 => 'total',
            2 => 'quote.number_result'
        ];

        // define your bootstrap version (4 or 5)
        $bootstrap = 4;

        // Add action buttons to each row
        $actionButtons = [
        ];

        if(Access::can('downloadPdf','work_orders'))
        {
            $pdf = 
            [
                'name' => 'Pdf',
                'route' => 'work-order.download.pdf',
                'id' => true,
            ];

            array_push($actionButtons,$pdf);
        }

        if(Access::can('edit','work_orders'))
        {
            $edit = 
            [
                'name' => 'Edit',
                'route' => 'work-order.edit',
                'id' => true,
            ];

            array_push($actionButtons,$edit);
        }

        if(Access::can('destroy','work_orders'))
        {
            $destroy = 
            [
                'name' => 'Delete',
                'route' => 'work-order.destroy',
                'id' => true,
            ];

            array_push($actionButtons,$destroy);
        }

        $response =  datatablesFormaterWithSearchRelasion($query, $columnNames, $actionButtons, $searchable, $bootstrap);

        $data = $response->getData();
        foreach ($data->data as $index => $item) 
        {
            $item->total = 'Rp. '.number_format($item->total, 0,',','.'); // Format angka dengan 2 desimal
        }

        return response()->json($data);
    }

    /**
     * WOrk Order
     */

     public function dataTableJsonQuoteWithoutWorkOrder()
    {
        // Fetch data for the DataTable
        $query = Quote::query();
        $query->byCompany(Auth::user()->company_id)->whereDoesntHave('workOrder')->orderBy('quote_number', 'desc');
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

        if(Access::can('createsuggest','work_orders'))
        {
            $destroy = [
                'name' => 'Membuat SPK',
                'route' => 'work-order.createsuggest',
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
     * Select 
     */
    public function select2(Request $request)
    {
    $workOrder = WorkOrder::byCompany(Auth::user()->company_id)->byNumberResult($request->get('number_result'))
            ->orderBy('created_at','desc')
            ->limit(6)
            ->get();
            
    return response()->json($workOrder);
    }
}
