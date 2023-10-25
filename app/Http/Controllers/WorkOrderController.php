<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Carbon\Carbon;
use App\Http\Requests\WorkOrderRequest;

use App\Models\WorkOrder;
use App\Models\WorkOrderProduct;
use App\Models\Product;
use App\Models\Quote;

class WorkOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $workOrder = WorkOrder::
        // byUser($request->user)
        // where('name','like', '%' . $request->get('quote') . '%')
        OrderBy('created_at','asc')->paginate(10);

        $totalWorkOrder = count(WorkOrder::get());

        return view('work_order.index',compact('workOrder','totalWorkOrder'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $product = Product::all();
        $quote = Quote::all();

        $userCreate = Auth::user()->name;
        $nomorWorkOrder = $this->workOrderNumber();

        return view('work_order.createOrEdit',compact('product','quote','userCreate','nomorWorkOrder'));
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
            $workOrderNumber = WorkOrder::withTrashed()->max('work_order_number') + 1;

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
                    $workOrderProduct->work_order_id = $workOrder->id;
                    $workOrderProduct->product_id = $request->post('product')[$i];
                    $workOrderProduct->description = $request->post('description')[$i];
                    $workOrderProduct->qty = $request->post('qty')[$i];
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
        $product = Product::all();
        $quote = Quote::all();
        
        $workOrder = WorkOrder::where('slug', $slug)->firstOrFail();
        $userCreate = $workOrder->userCreate ? $workOrder->userCreate->name : '';
        $nomorWorkOrder = $workOrder->number_result ?? '';

        return view('work_order.createOrEdit',compact('product','quote','userCreate','nomorWorkOrder','workOrder'));
    }
    
    /**
     * Suggetion When Choose Qute
     */
    public function suggestionQuote($id)
    {
        $quote = Quote::find($id);
        $quoteProduct = $quote->quoteProduct 
        ? $quote->quoteProduct()
                ->select('product_id', 'qty', 'description')
                ->get()
                ->map(function($item) {
                    return [
                        'product_id' => $item->product_id,
                        'qty' => $item->qty,
                        'description' => $item->description
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
        $product = Product::all();
        $quote = Quote::all();
        
        $workOrder = WorkOrder::where('slug', $slug)->firstOrFail();
        $userCreate = $workOrder->userCreate ? $workOrder->userCreate->name : '';
        $nomorWorkOrder = $workOrder->number_result ?? '';

        return view('work_order.pdf',compact('product','quote','userCreate','nomorWorkOrder','workOrder'));
    }


     /* Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WorkOrder  $workOrder
     * @return \Illuminate\Http\Response
     */
    public function update(WorkOrderRequest $request, WorkOrder $workOrder)
    {
        DB::beginTransaction();
        try {
            // Simpan data WorkOrder
            $workOrder->date = $request->post('date');
            $workOrder->quote_id = $request->post('quote');
            $workOrder->number_result = $this->workOrderNumber();
            $workOrder->user_created_id = Auth::user()->id;
            $workOrder->user_updated_id = Auth::user()->id;
            
            $workOrder->save();

            // Jika WorkOrder berhasil disimpan, simpan produk terkait
            if ($workOrder) 
            {
                for ($i = 0; $i < count($request->post('product')); $i++) 
                {
                    $ids = $request->post('ids')[$i];

                    if(!$ids)
                    {
                        $workOrderProduct = new WorkOrderProduct();
                        $workOrderProduct->work_order_id = $workOrder->id;
                        $workOrderProduct->product_id = $request->post('product')[$i];
                        $workOrderProduct->description = $request->post('description')[$i];
                        $workOrderProduct->qty = $request->post('qty')[$i];
                        $workOrderProduct->sub_total = $request->post('sub_total')[$i];
                       
                        $workOrder->workOrderProduct()->save($workOrderProduct);
                    }else
                    {
                        $workOrderProduct = WorkOrderProduct::find($ids);
                        $workOrderProduct->work_order_id = $workOrder->id;
                        $workOrderProduct->product_id = $request->post('product')[$i];
                        $workOrderProduct->description = $request->post('description')[$i];
                        $workOrderProduct->qty = $request->post('qty')[$i];
                        $workOrderProduct->sub_total = $request->post('sub_total')[$i];
                        
                        $workOrderProduct->save();
                    }
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
    public function destroy(WorkOrder $workOrder)
    {
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
     * Product Counting QTY
     */
    public function productCounting(Request $request)
    {
        $productId = $request->get('product');
        $qty = $request->get('qty');

        $product = Product::find($productId);
        $result = $product->price_buy * $qty;

        return [
            'status' => 200,
            'message' => 'okay',
            'data' => $result
        ];
    }

    private function workOrderNumber()
    {
        $date = Carbon::now()->format('m/Y');
        $nomor = WorkOrder::withTrashed()->max('work_order_number') + 1;

        return $nomor.'/'.$date;

    }

    public function dataTableJson()
    {
        // Fetch data for the DataTable
        $query = WorkOrder::query();

        // Map column indexes to column names (this may vary based on your table structure)
        $columnNames = ['number_result', 'total'];

        // Define searchable columns
        $searchable = [
            0 => 'number_result',
            1 => 'total',
        ];

        // define your bootstrap version (4 or 5)
        $bootstrap = 4;

        // Add action buttons to each row
        $actionButtons = [
            [
                'name' => 'Pdf',
                'route' => 'work-order.download.pdf',
                'id' => true,
            ],
            [
                'name' => 'Edit',
                'route' => 'work-order.edit',
                'id' => true,
            ],
            [
                'name' => 'Delete',
                'route' => 'work-order.destroy',
                'id' => true,
            ],
        ];

        $response =  datatablesFormater($query, $columnNames, $actionButtons, $searchable, $bootstrap, true);

        $data = $response->getData();
        foreach ($data->data as $index => &$item) 
        {
            $item->total = 'Rp. '.number_format($item->total, 0,',','.'); // Format angka dengan 2 desimal
        }

        return response()->json($data);
    }
}
