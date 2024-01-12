<?php

namespace App\Http\Controllers\API;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
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

class WorkOrderController extends BaseController
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
        $workOrder = WorkOrder::byCompany(auth()->user()->company_id)
        ->where('number_result','like', '%' . $request->get('quote') . '%')
        ->orderBy('work_order_number',$order)->paginate($paginate)->toArray();

        return $this->sendResponse($workOrder,'success');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['product'] = Product::byCompany(auth()->user()->company_id)->get();
        // $quote = Quote::orderBy('created_at','desc')->get();

        $data['userCreate'] = auth()->user()->name;
        $data['nomorWorkOrder'] = $this->workOrderNumber();

        return $this->sendResponse($data,'Success');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [
            'date' => 'required|date',
            'quote' => 'required|uuid|exists:quotes,id',
            'product.*' => 'required|string|max:255',
            'description.*' => 'required|string',
            'products' => 'required|array',
            'products.*.product_id' => 'required|uuid|exists:products,id',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.qty' => 'required|integer|min:1',
            'products.*.description' => 'required|string',
            // 'quote_file' => 'required|file|mimes:pdf', // Ini adalah contoh validasi untuk file PDF dengan maksimum 2MB.
        ], 
        [
            'date.required' => 'Tanggal harus diisi.',
            'quote.required' => 'Quote harus dipilih.',
            'quote_file.file' => 'Harap unggah file yang valid.',
            'quote_file.mimes' => 'File harus dalam format PDF.',
            'quote_file.max' => 'Ukuran file maksimal adalah 2MB.',
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

        DB::beginTransaction();
        try {
            $workOrderNumber = WorkOrder::byCompany(auth()->user()->company_id)->withTrashed()->max('work_order_number') + 1;

            // Simpan data WorkOrder
            $workOrder = new WorkOrder();
            $workOrder->date = $request->post('date');
            $workOrder->quote_id = $request->post('quote');
            $workOrder->work_order_number = $workOrderNumber;
            $workOrder->number_result = $this->workOrderNumber();
            $workOrder->user_created_id = auth()->user()->id;
            $workOrder->user_updated_id = auth()->user()->id;
            
            $workOrder->save();

            // Jika WorkOrder berhasil disimpan, simpan produk terkait
            $i = 0;
            if ($workOrder) 
            {
                foreach ($request->post('products') as $a) 
                {
                    $workOrderProduct = new WorkOrderProduct();
                    $workOrderProduct->sort = $i + 1;
                    $workOrderProduct->work_order_id = $workOrder->id;
                    $workOrderProduct->product_id = $a['product_id'];
                    $workOrderProduct->description = $a['description'];
                    $workOrderProduct->qty = $a['qty'];
                    $workOrderProduct->price_buy = $a['price'];
                    $workOrderProduct->sub_total = $a['qty'] * $a['price'];
                   
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
                $workOrder->quote_file = $filePath;  // Menyimpan path dari file yang disimpan
            }
            
            $workOrder->total = $workOrder->workOrderProduct()->sum('sub_total');

            $workOrder->save();

            DB::commit();

            return $this->sendMessage("berhasil");
            // return redirect()->to(route('work-order.index'))->with('store',true);
            // return redirect()->to(route('work-order.download.pdf', ['slug' => $workOrder->slug]))->with('store',true);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            Log::error($th);

            return $this->sendMessage($th->getMessage());
            // return redirect()->to(route('work-order.index'))->with('store',false);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $workOrder = WorkOrder::with('workOrderProduct')->where('slug', $slug)->first();
        if(empty($workOrder))
        {
            return $this->sendError('Work Order Not Found');
        }

        return $this->sendResponse($workOrder,'Success');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $data['product'] = Product::byCompany(auth()->user()->company_id)->get();
        
        $workOrder = WorkOrder::where('slug', $slug)->first();
        if(empty($workOrder))
        {
            return $this->sendError('Work Order Not Found');
        }
        $data['workOrder'] = $workOrder;
        $data['userCreate'] = $workOrder->userCreate ? $workOrder->userCreate->name : '';
        $data['nomorWorkOrder'] = $workOrder->number_result ?? '';

        return $this->sendResponse($data,'Success');
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
        $workOrder = WorkOrder::where('slug', $slug)->first();
        if(empty($workOrder))
        {
            return $this->sendError("Work Order Not Found");
        }

        $product = Product::byCompany(auth()->user()->company_id)->get();
        $quote = Quote::byCompany(auth()->user()->company_id)->get();
        
        $company = SettingCompany::byCompany(auth()->user()->company_id)->get()->pluck('field_value','field_title');
        $userCreate = $workOrder->userCreate ? $workOrder->userCreate->name : '';
        $nomorWorkOrder = $workOrder->number_result ?? '';

        return view('work_order.pdfApi',compact('product','quote','userCreate','nomorWorkOrder','workOrder' ,'company'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $validator = Validator::make($request->all(), 
        [
            'date' => 'required|date',
            'quote' => 'required|uuid|exists:quotes,id',
            'product.*' => 'required|string|max:255',
            'description.*' => 'required|string',
            'products' => 'required|array',
            'products.*.product_id' => 'required|uuid|exists:products,id',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.qty' => 'required|integer|min:1',
            'products.*.description' => 'required|string',
            // 'quote_file' => 'required|file|mimes:pdf', // Ini adalah contoh validasi untuk file PDF dengan maksimum 2MB.
        ], 
        [
            'date.required' => 'Tanggal harus diisi.',
            'quote.required' => 'Quote harus dipilih.',
            'quote_file.file' => 'Harap unggah file yang valid.',
            'quote_file.mimes' => 'File harus dalam format PDF.',
            'quote_file.max' => 'Ukuran file maksimal adalah 2MB.',
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

        DB::beginTransaction();
        try {
            $workOrder = WorkOrder::byCompany(auth()->user()->company_id)->where('slug', $slug)->first();
            if(empty($workOrder))
            {
                return $this->sendError('Work Order Not Found');
            }
            $workOrderNumber = WorkOrder::byCompany(auth()->user()->company_id)->withTrashed()->max('work_order_number') + 1;

            // Simpan data WorkOrder

            $workOrder->date = $request->post('date');
            $workOrder->quote_id = $request->post('quote');
            $workOrder->work_order_number = $workOrderNumber;
            $workOrder->number_result = $this->workOrderNumber();
            $workOrder->user_created_id = auth()->user()->id;
            $workOrder->user_updated_id = auth()->user()->id;
            
            $workOrder->save();

            // Destroy Work Order transaction
            $workOrder->workOrderProduct()->delete();
            // Jika WorkOrder berhasil disimpan, simpan produk terkait
            $i = 0;
            if ($workOrder) 
            {
                foreach ($request->post('products') as $a) 
                {
                    $workOrderProduct = new WorkOrderProduct();
                    $workOrderProduct->sort = $i + 1;
                    $workOrderProduct->work_order_id = $workOrder->id;
                    $workOrderProduct->product_id = $a['product_id'];
                    $workOrderProduct->description = $a['description'];
                    $workOrderProduct->qty = $a['qty'];
                    $workOrderProduct->price_buy = $a['price'];
                    $workOrderProduct->sub_total = $a['qty'] * $a['price'];
                   
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
                $workOrder->quote_file = $filePath;  // Menyimpan path dari file yang disimpan
            }
            
            $workOrder->total = $workOrder->workOrderProduct()->sum('sub_total');

            $workOrder->save();

            DB::commit();

            return $this->sendMessage("berhasil");
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            Log::error($th);

            return $this->sendMessage($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $workOrder = WorkOrder::byCompany(auth()->user()->company_id)->where('slug', $slug)->first();
        if(empty($workOrder))
        {
            return $this->sendError('SPK Not Found');
        }
        $workOrder->workOrderProduct()->delete();
        $workOrder->delete();

        return $this->sendMessage('Success');
    }

    private function workOrderNumber()
    {
        $date = Carbon::now()->format('m/Y');
        $nomor = WorkOrder::byCompany(auth()->user()->company_id)->withTrashed()->max('work_order_number') + 1;

        return $nomor.'/'.$date;

    }
}
