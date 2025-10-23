<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


use App\Http\Requests\InvoiceRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoiceExport;
use Carbon\Carbon;

use App\Helpers\Access;
use App\Helpers\EmailNotifHelper;

use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;

use App\Models\Quote;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\QuoteProduct;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SettingCompany;
use App\Models\DivisionBudget;
use App\Models\Bast;
use App\Models\Company;
use App\Models\InvoiceEmailRecord;

use App\Services\XeroService;
use App\Jobs\ExportInvoiceJob;
use App\Jobs\ConvertInvoiceToPdfAJob;

class InvoiceController extends Controller
{
    /**
     * 
     * Xero For Service
     * xero service
     */
    protected $xeroService;

    public function __construct(XeroService $xeroService)
    {
        $this->xeroService = $xeroService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // Ambil input pencarian dari request
        $search = $request->input('search');
        $order = $request->input('order') ?? 'desc';
        $status = $request->input('status');
        $start_date = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null; // Parse tanggal dari string ke Carbon
        $end_date = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;
        
        $isConnect = $this->xeroService->isConnected();

        $invoice = Invoice::byCompany(Auth::user()->company_id)->byDateRange($start_date,$end_date)
        ->when($search, function ($query, $search) {
            return $query->where('number_result', 'LIKE', "%{$search}%")
                         ->orWhereHas('bast', function ($q) use ($search) {
                             $q->where('number_result', 'LIKE', "%{$search}%");
                         });
        })
        ->when($status, function ($query, $status) {
            return $query->where('status', $status);
        })
        ->orderBy('created_at', $order)
        ->paginate(10);

        $searchByStatus = config('custom.status_invoice_search');

        return view('invoice.index',compact('invoice','searchByStatus','isConnect'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $product = Product::withTrashed()->with('category')->byCompany(Auth::user()->company_id)->get();
        $customer = Customer::byCompany(Auth::user()->company_id)->orderBy('created_at','desc')->get();
        $userCreate = Auth::user()->name;
        $date = Carbon::now()->format('m/Y');
        $nomor = Invoice::byCompany(Auth::user()->company_id)->withTrashed()->max('invoice_number') + 1;
        $nomorQuote = $nomor.'/'.$date;
        $bast = Bast::byCompany(Auth::user()->company_id)
        ->whereDoesnthave('invoice')
        ->orderBy('created_at','desc')
        ->get();
        $status = config('custom.status_invoice');

        $date = Carbon::now();
        
        return view('invoice.createOrEdit',compact('product','customer','userCreate','nomorQuote','bast','date','status'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(InvoiceRequest $request)
    {
        DB::beginTransaction();
        try 
        {
            activity()->disableLogging();
            if($request->number_result)
            {
                $invoiceNumber = Invoice::byCompany(Auth::user()->company_id)->where('number_result',$request->number_result)->first();
                if($invoiceNumber)
                {
                    return redirect()->back()->with('InvoiceNumber',true);
                }

                $checkInvoice = $this->xeroService->findNumberInvoice($request->number_result);
                if($checkInvoice == false)
                {
                    return redirect()->back()->with('InvoiceNumber',true);
                }
            }


            // if($request->reference)
            // {
            //     $invoiceRef = Invoice::byCompany(Auth::user()->company_id)->where('reference',$request->reference)->first();
            //     if($invoiceRef)
            //     {
            //         return redirect()->back()->with('Reference',true);
            //     }

            //     $checkRef = $this->xeroService->findReferenceInvoice($request->reference);
            //     if($checkRef == false)
            //     {
            //         return redirect()->back()->with('Reference',true);
            //     }
            // }

            $date = Carbon::now()->format('m/Y');
            $invoiceNumber = Invoice::byCompany(Auth::user()->company_id)->withTrashed()->max('invoice_number') + 1;
            $bast = Bast::byCompany(Auth::user()->company_id)->where('id',$request->post('bast'))->firstOrFail();
            $quote = Quote::byCompany(Auth::user()->company_id)->where('id',$bast->project->workOrder->quote_id)->firstOrFail();

            $invoice = new Invoice();
            $invoice->number_result = $request->post('number_result');
            $invoice->reference = $request->post('reference');
            $invoice->date = Carbon::now()->format('Y-m-d');
            $invoice->bast_id = $request->post('bast');
            $invoice->start_date = $request->post('start_date') ?? Carbon::now();
            $invoice->end_date = $request->post('end_date') ?? Carbon::now();
            $invoice->quote_id = $quote->id;
            $invoice->customer_id = $quote->customer_id;        
            $invoice->tax = $request->post('tax');
            $invoice->service_fee = $request->post('service_fee');
            $invoice->discount = $request->post('discount');
            $invoice->charges = $request->post('charges');
            $invoice->total = $request->post('total');
            $invoice->payment_term = $request->post('payment_term');
            $invoice->third_party_docs = $request->post('third_party_docs');
            $invoice->status = $request->post('status') ?? ParamSchema::DRAFT;
            
            $invoice->user_created_id = Auth::user()->id;
            $invoice->user_updated_id = Auth::user()->id;
            $invoice->save();

            $product = $request->post('product');
            $description = $request->post('description');
            $qty = $request->post('qty');
            $price = $request->post('price');
            $sub_total = $request->post('sub_total');

            for ($i = 0; $i < count($product); $i++) 
            {
                $invoiceProduct = new InvoiceProduct();
                $invoiceProduct->sort = $i + 1;
                $invoiceProduct->product_id = $product[$i];
                $invoiceProduct->qty = $qty[$i];
                $invoiceProduct->price_sell = $price[$i];
                $invoiceProduct->sub_total = $sub_total[$i];
                $invoiceProduct->description = $description[$i];

                $invoice->invoiceProducts()->save($invoiceProduct);
            }

            $this->grandTotal($invoice);
            $this->updateQuote($invoice->quote_id);
            $this->generateXeroInvoice($invoice);

            if ($invoice->bast) 
            {
                // Gabungkan file BAST dengan invoice dari Xero
                if ($invoice->bast->file_merge_path) 
                {
                    $mergedFilePath = $this->mergePdf($invoice, $invoice->bast->file_merge_path);
                    
                    // Simpan path hasil gabungan ke database
                    $invoice->file_merge_path = $mergedFilePath;
                    $invoice->save();
                }
            }
            activity()->enableLogging();
            activity()
                ->performedOn($invoice)
                ->withProperties(['attributes' => $request->only([
                    'start_date', 'end_date', 'total', 'tax', 'service_fee', 'discount', 'charges', 'status'
                ])])
                ->log('Invoice Inserted');
            DB::commit();
            return redirect()->to(route('invoice.show', $invoice->slug))->with('store',true);
        } catch (\Throwable $th) {
            //throw $th;
            dd($th);

            DB::rollback();
            Log::error($th);

            return redirect()->to(route('invoice.index'))->with('false',true);

        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Quote  $invoice
     * @return \Illuminate\Http\Response
     */
    public function edit($slug,Request $request)
    {
        $product = Product::withTrashed()->with('category')->byCompany(Auth::user()->company_id)->get();
        $invoice = Invoice::where('slug', $slug)->firstOrFail();

        if(($invoice->status == 'PAID') || ($invoice->status == 'DELETED') || ($invoice->status == 'VOID') || ($invoice->status == 'AUTHORISED'))
        {
            return redirect()->to(route('invoice.index'))->with('AUTHORISED',true);
        }
        $bast = Bast::byCompany(Auth::user()->company_id)
        ->whereDoesnthave('invoice')
        ->orWhere('id', $invoice->bast_id)
        ->orderBy('created_at','desc')
        ->get();

        $date = Carbon::parse($invoice->created_at)->format('m/Y');

        $nomor = $request->get('nomor') ?? 0;
        $nomorQuote = $invoice->quote_number_result ?? '';
        $userCreate = $invoice->userCreate ? $invoice->userCreate->name : '';
        $status = config('custom.status_invoice');

        return view('invoice.createOrEdit',compact('product','userCreate','nomorQuote','bast','date','invoice','nomor','status'));
    }

    /**
     * 
     * SHow Product
     */

     public function show($slug)
    {
        $product = Product::withTrashed()->with('category')->byCompany(Auth::user()->company_id)->get();
        $invoice = Invoice::where('slug', $slug)->firstOrFail();

        $basts = Bast::byCompany(Auth::user()->company_id)
        ->whereDoesnthave('invoice')
        ->orWhere('id', $invoice->bast_id)
        ->orderBy('created_at','desc')
        ->get();

        $date = Carbon::parse($invoice->created_at)->format('m/Y');

        $nomorQuote = $invoice->quote_number_result ?? '';
        $userCreate = $invoice->userCreate ? $invoice->userCreate->name : '';
        $status = config('custom.status_invoice');


        $invoiceEmailRecords = InvoiceEmailRecord::where('invoice_id', $invoice->id)
        ->orderBy('created_at', 'desc')
        ->paginate(2);
        // Bast
        
        $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');

        $bast = $invoice->bast;
        $userCreate = $bast->userCreate ? $bast->userCreate->name : '';
        $nomorBast = $bast->number_result ?? '';
        $today = Carbon::now()->format('d M Y');
        return view('invoice.show',compact('product','userCreate','nomorQuote','basts','date','invoice','status','nomorBast','company','bast','today','invoiceEmailRecords'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Quote  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(InvoiceRequest $request, $slug)
    {
        DB::beginTransaction();
        try 
        {
            activity()->disableLogging();

            $status = $request->post('status');

            $invoice = Invoice::byCompany(Auth::user()->company_id)->where('id', $slug)->first();

            if($request->number_result != $invoice->number_result)
            {
                $invoiceCheck = Invoice::byCompany(Auth::user()->company_id)->where('number_result',$request->number_result)->first();
                if($invoiceCheck)
                {
                    return redirect()->back()->with('InvoiceNumber',true);
                }

                $checkInvoice = $this->xeroService->findNumberInvoice($request->number_result);
                if($checkInvoice == false)
                {
                    return redirect()->back()->with('InvoiceNumber',true);
                }
            }

            // if($request->reference != $invoice->reference)
            // {
            //     $invoiceCheckRef = Invoice::byCompany(Auth::user()->company_id)->where('reference',$request->reference)->first();
            //     if($invoiceCheckRef)
            //     {
            //         return redirect()->back()->with('Reference',true);
            //     }

            //     $checkRef = $this->xeroService->findReferenceInvoice($request->reference);
            //     if($checkRef == false)
            //     {
            //         return redirect()->back()->with('Reference',true);
            //     }
            // }
            
            $bast = Bast::byCompany(Auth::user()->company_id)->where('id',$request->post('bast'))->firstOrFail();
            $quote = Quote::byCompany(Auth::user()->company_id)->where('id',$bast->project->workOrder->quote_id)->firstOrFail();
            
            if(($invoice->status == 'PAID') || ($invoice->status == 'DELETED') || ($invoice->status == 'VOID') || ($invoice->status == 'AUTHORISED'))
            {
                return redirect()->to(route('invoice.index'))->with('AUTHORISED',true);
            }
            $invoice->number_result = $request->post('number_result');
            $invoice->reference = $request->post('reference');
            $invoice->date = Carbon::now()->format('Y-m-d');
            $invoice->bast_id = $bast->id;
            $invoice->start_date = $request->post('start_date') ?? Carbon::now();
            $invoice->end_date = $request->post('end_date') ?? Carbon::now();
            $invoice->quote_id = $quote->id;
            $invoice->customer_id = $quote->customer_id;        
            $invoice->tax = $request->post('tax');
            $invoice->service_fee = $request->post('service_fee');
            $invoice->discount = $request->post('discount');
            $invoice->charges = $request->post('charges');
            $invoice->total = $request->post('total');
            $invoice->payment_term = $request->post('payment_term');
            $invoice->third_party_docs = $request->post('third_party_docs');
            $invoice->status = $status;
        
            $invoice->user_updated_id = Auth::user()->id;
            $invoice->save();

            $invoice->invoiceProducts()->delete();

            $product = $request->post('product');
            $description = $request->post('description');
            $qty = $request->post('qty');
            $price = $request->post('price');
            $sub_total = $request->post('sub_total');

            for ($i = 0; $i < count($product); $i++) 
            {
                $invoiceProduct = new InvoiceProduct();
                $invoiceProduct->sort = $i + 1;
                $invoiceProduct->product_id = $product[$i];
                $invoiceProduct->qty = $qty[$i];
                $invoiceProduct->price_sell = $price[$i];
                $invoiceProduct->sub_total = $sub_total[$i];
                $invoiceProduct->description = $description[$i];

                $invoice->invoiceProducts()->save($invoiceProduct);
            }

            $this->grandTotal($invoice);
            $this->xeroService->updateInvoice($invoice,$request->post('status'));
            
            if ($invoice->bast) 
            {
                if ($invoice->bast->file_merge_path) 
                {
                    // Gabungkan file BAST dengan invoice dari Xero
                    $mergedFilePath = $this->mergePdf($invoice, $invoice->bast->file_merge_path);
                    
                    // Simpan path hasil gabungan ke database
                    $invoice->file_merge_path = $mergedFilePath;
                    $invoice->save();
                }
            }

            activity()->enableLogging();
            activity()
                ->performedOn($invoice)
                ->withProperties(['attributes' => $request->only([
                    'start_date', 'end_date', 'total', 'tax', 'service_fee', 'discount', 'charges', 'status'
                ])])
                ->log('Invoice updated');
            DB::commit();
            return redirect()->to(route('invoice.show',$invoice->slug))->with('update',true);
        } catch (\Throwable $th) {
            // dd($th);    
            DB::rollback();
            Log::error($th);
            return redirect()->to(route('invoice.index'))->with('false',false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Quote  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        DB::beginTransaction();

        try 
        {
            $invoice = Invoice::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            if(($invoice->status == 'PAID') || ($invoice->status == 'DELETED') || ($invoice->status == 'VOID') || ($invoice->status == 'AUTHORISED'))
            {
                return redirect()->to(route('invoice.index'))->with('AUTHORISED',true);
            }
            $this->xeroService->deleteInvoice($invoice);
            $invoice->invoiceProducts()->delete();
            
            $invoice->delete();

            DB::commit();
            return redirect()->back()->with('delete', true);

        } catch (\Exception $e) {
            // dd($e->getMessage());
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus invoice.');
        }
    }

    /**
     * Hisotyr
     */

     public function history($slug)
    {
        $invoice = Invoice::where('slug', $slug)->firstOrFail();
        // Mengambil semua aktivitas yang terkait dengan invoice ini
        $activities = $invoice->activities()->orderBy('created_at', 'desc')->get();
        
        return view('invoice.history', compact('invoice', 'activities'));
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
        $invoice_id = $request->quote_id ?? NULL;

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
                if ($invoice_id) 
                {
                    $invoice = Invoice::find($invoice_id);
                    if ($invoice && ($invoice->division_budget_id == $division_budget_id)) {
                        $budgetAmount += $invoice->total;
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
                $product = Product::withTrashed()->find($productId);
                $price = $product->price_sell;
            }
        }else
        {
            $product = Product::withTrashed()->find($productId);
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
    public function destroyProduct($invoiceProductId)
    {
        $invoiceProduct = QuoteProduct::find($invoiceProductId);

        $invoice = Invoice::find($invoiceProduct->quote_id);
        $invoiceProduct->delete();

        $this->grandTotal($invoice);

        return true;
    }

    /**
     * Download PDF
     */
    public function downloadPdf($slug)
    {
        $invoice = Invoice::where('slug', $slug)->firstOrFail();
        if(!$invoice->invoice_xero_id)
        {
            $this->generateXeroInvoice($invoice);
        }

        return $this->xeroService->getInvoice($invoice->invoice_xero_id);

    }

    /**
     * Download PDF A
     */
    public function downloadPdfA($slug)
    {
        $gs = config('services.path.ghost_script');

        dispatch(new ConvertInvoiceToPdfAJob($slug, $gs));

        // Simpan nama file di session agar frontend tahu filenya nanti
        session(['export_filename_invoice_pdfa' => "{$slug}.pdf"]);

        return redirect()->back()->with('downloadPdfA', true);
    }

    public function checkPdfAStatus()
    {
        $filename = session('export_filename_invoice_pdfa');
        $fileExist = "public/invoices/converted/pdfa-".$filename;
        // $fileExist "public/invoices/converted";

        if ($filename && Storage::exists($fileExist)) {
            // Provide the download URL if file exists
            $downloadUrl = s3_asset(true,10,$fileExist);
            return response()->json(['ready' => true, 'download_url' => $downloadUrl]);
        }

        return response()->json(['ready' => false]);
    }

    public function clearsessionPdfA()
    {
        $filename = session('export_filename_invoice_pdfa');
        $fileExist = "public/invoices/converted/pdfa-".$filename;
        $fileExistClear = "public/invoices/converted/clean-".$filename;

        if ($filename && Storage::exists($fileExist)) 
        {
            Storage::delete($fileExist);
            Storage::delete($fileExistClear);
        }

        // session()->forget('export_filename_invoice_pdfa');

        return response()->json(['cleared' => true]);
    }
    /**
     * Total After PPN dll
     */
    private function grandTotal($invoice)
    {
        $service_fee = $invoice->service_fee ?? 0;
        $tax = $invoice->tax ?? 0;

        $total =  $invoice->invoiceProducts() ? $invoice->invoiceProducts()->sum('sub_total') : 0;
        $charges = $invoice->charges ?? 0;
        $discount = $invoice->discount ?? 0;

        // return $tax;
        $totalAll = ($total + $charges) - $discount;
        $serviceFee = $service_fee != 0 ? round(($totalAll * $service_fee) / ParamSchema::PERCENTAGE) : 0 ;
        
        $totalAfterServiceFee = $totalAll + $serviceFee;
        $ppn = $tax != 0 ? round(($totalAfterServiceFee * $tax) / ParamSchema::PERCENTAGE) : 0 ;
        
        $grandTotal = $totalAfterServiceFee + $ppn;

        $invoice->total = $grandTotal;
        $invoice->save();

        return $grandTotal;
    }

    /**
     * Data Table Quote
     */
    public function dataTableJson()
    {
        // Fetch data for the DataTable
        $query = Invoice::query();
        $query->byCompany(Auth::user()->company_id)->orderBy('invoice_number', 'desc');
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
        $actionButtons = [];

        if(Access::can('downloadPdf','invoices'))
        {
            $pdf = [
                'name' => 'Pdf',
                'route' => 'invoice.download.pdf',
                'id' => true,
            ];

            array_push($actionButtons,$pdf);
        }

        if(Access::can('edit','invoices'))
        {
            $edit = [
                'name' => 'Edit',
                'route' => 'invoice.edit',
                'id' => true,
            ];

            array_push($actionButtons,$edit);
        }

        if(Access::can('destroy','invoices'))
        {
            $destroy = [
                'name' => 'Delete',
                'route' => 'invoice.destroy',
                'id' => true,
            ];

            array_push($actionButtons,$destroy);
        }


        $response =  datatablesFormater($query, $columnNames, $actionButtons, $searchable, $bootstrap);

        $data = $response->getData();
        foreach ($data->data as $index => $item) 
        {
            $item->total = 'Rp. '.number_format($item->total, 0,',','.'); // Format angka dengan 2 desimal
            switch ($item->status) 
            {
                case 'DRAFT':
                    $item->status = '<span class="badge badge-secondary">Draf</span>';
                    break;
                case 'SUBMITTED':
                    $item->status = '<span class="badge badge-warning">Submitted</span>';
                    break;
                case 'AUTHORISED':
                    $item->status = '<span class="badge badge-success">Waiting Payment</span>';
                    break;
            }
        }

        return response()->json($data);
    }

    /**
     * zero Invoice
     * Select2 Quote
     */

     public function select2(Request $request)
     {
        $invoice = Invoice::byCompany(Auth::user()->company_id)->with('customer')->byNumberResult($request->get('number_result'))
                ->orderBy('created_at','desc')
                ->limit(6)
                ->get();
                
        return response()->json($invoice);
     }


    /**
     * Suggetion When Choose Qute
     */
    public function suggestionQuote($id)
    {
        $bast = Bast::byCompany(Auth::user()->company_id)->where('id', $id)->first();

        $invoice = Quote::byCompany(Auth::user()->company_id)->find($bast->project->workOrder->quote_id);
        $invoiceProduct = $invoice->quoteProduct 
        ? $invoice->quoteProduct()
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
        $invoiceCustomer = $invoice->customer ? $invoice->customer->name : '' ;
        
        $data = 
        [
            'quote' => $invoice,
            'customer' => $invoiceCustomer,
            'product' => $invoiceProduct
        ];
        return $data;
    }

    public function updateQuote($id)
    {
        $invoice = Quote::byCompany(Auth::user()->company_id)->find($id);
        $invoice->status = ParamSchema::CLOSED;
        $invoice->save();
    }

    public function generateXeroInvoice($invoice)
    {
        $contactXero = $this->xeroService->checkOrCreateContact($invoice->quote->customer);
        $invoiceXero = $this->xeroService->createInvoice($invoice, $contactXero);
        $invoice->number_result = $invoiceXero['InvoiceNumber'];
        $invoice->invoice_xero_id = $invoiceXero['InvoiceID'];
        $invoice->contact_xero_id = $contactXero->ContactID;
        $invoice->save();
    }

    /**
     * Merge PDF
     */
    public function mergePdf($invoice, $bastFilePath)
    {
        // Path relatif untuk file gabungan
        $outputPath = "invoices/merged_invoice_{$invoice->number_result}_".date('YmdHis').'_'.Str::random(5).".pdf";
        
        // Hapus file gabungan sebelumnya jika ada
        if ($invoice->file_merge_path && Storage::exists($invoice->file_merge_path)) {
            Storage::delete($invoice->file_merge_path);
        }
        
        // Unduh PDF dari Xero dan simpan sementara
        $tempInvoicePdfPath = sys_get_temp_dir() . "/invoice_temp_{$invoice->id}.pdf";
        $xeroInvoicePdf = $this->xeroService->getInvoice($invoice->invoice_xero_id);
        file_put_contents($tempInvoicePdfPath, $xeroInvoicePdf);
        
        // Gunakan FPDI untuk menggabungkan file
        $pdf = new \setasign\Fpdi\Fpdi();
        
        // Tambahkan halaman dari file invoice (PDF dari Xero)
        $pageCount = $pdf->setSourceFile($tempInvoicePdfPath);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);
        }

        // Tambahkan halaman dari file BAST (download dari S3 ke temp)
        $tempBastPath = sys_get_temp_dir() . '/temp_bast_' . uniqid() . '.pdf';
        $bastContent = Storage::get($bastFilePath); // Download dari S3
        file_put_contents($tempBastPath, $bastContent);

        $pageCount = $pdf->setSourceFile($tempBastPath);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);
        }

        // Hapus temp BAST file
        unlink($tempBastPath);

        // Simpan hasil gabungan ke TEMP dulu, baru upload ke S3
        $tempMergedPath = sys_get_temp_dir() . '/merged_' . uniqid() . '.pdf';
        $pdf->Output($tempMergedPath, 'F');

        // Upload ke S3 menggunakan Laravel Storage
        Storage::put($outputPath, file_get_contents($tempMergedPath));

        // Hapus semua file temporary
        if (file_exists($tempInvoicePdfPath)) {
            unlink($tempInvoicePdfPath);
        }
        if (file_exists($tempMergedPath)) {
            unlink($tempMergedPath);
        }

        return $outputPath; // Kembalikan path relatif untuk disimpan di database
    }

    /**
     * Sent Mail
     */
    public function sentMail(Request $request, $slug)
    {
        $request->validate([
            'to' => 'required|array',
            'to.*' => 'email',
            'cc' => 'nullable|array',
            'cc.*' => 'email',
            'subject' => 'required|string',
            'content' => 'required|string',
        ]);

        try {
            // Retrieve the BAST and the selected merged file
            $invoice = Invoice::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            
            $smtpConfig = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');


            // Retrieve the file path and ensure it exists
            $filePath = Storage::path($invoice->file_merge_path);
            if (!Storage::exists($invoice->file_merge_path)) {
                return redirect()->back()->with('error', 'Selected file does not exist.');
            }

            // Send the email with the attachment
            $data = 
            [
                'subject' => $request->subject,
                'content' => $request->content,
            ];

            $nameFile = "INVOICE_".str_replace('/','-', $invoice->number_result). '.pdf';
            $attachments = [
                $filePath => $nameFile,
            ];

            $smtpConfig = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
            $fromEmail = $smtpConfig['username'] ?? '';
            $fromName = $smtpConfig['name'] ?? '';
            $toEmails = $request->to;
            $toNames = $request->to;
            $ccEmails = $request->cc;
            $subject = $request->subject;
            $tamplate = "email.bast_email";
            $companyId = Auth::user()->company_id;
            
            EmailNotifHelper::sentEmail(
                $fromEmail,
                $fromName,
                $toEmails, 
                $toNames, 
                $subject,
                $tamplate,
                $data, 
                $smtpConfig, 
                $companyId, 
                $ccEmails,
                [],
                $attachments
            );

            // Simpan record ke database
            $invoiceEmailRecord = new InvoiceEmailRecord();
            $invoiceEmailRecord->invoice_id = $invoice->id;
            $invoiceEmailRecord->user_id = Auth::user()->id;
            $invoiceEmailRecord->to = json_encode($request->to);
            $invoiceEmailRecord->cc = json_encode($request->cc);
            $invoiceEmailRecord->subject = $request->subject;
            $invoiceEmailRecord->content = $request->content;
            $invoiceEmailRecord->save();

            return redirect()->back()->with('successEmail', true);
        } catch (\Exception $e) {
            // dd($e);
            \Log::error('Failed to send BAST email: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to send the email.');
        }
    }

    public function requestExport(Request $request)
    {
        // Generate a unique file name
        $fileName = 'invoices_' . now()->timestamp . '.xlsx';

        // Queue the export job
        Excel::store(new InvoiceExport(), $fileName, \Maatwebsite\Excel\Excel::XLSX);

        // Return the file name to the frontend
        return response()->json(['file_name' => $fileName]);
    }

    public function export(Request $request, $format)
    {
        // Get filter parameters from the request
        $filters = $request->only(['search', 'order', 'start_date', 'end_date', 'status']);
        
        // Generate a unique export filename
        $filename = 'invoices_' . time() . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
    
        // Choose the export format
        $exportFormat = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        
        // Store the export in the 'public' disk
        ExportInvoiceJob::dispatch($filters, $filename, $exportFormat, Auth::user()->company_id);
        $filename = "public/" . $filename;
        // dd($filename);
        // Save filename to session or pass it to the frontend
        session(['export_filename_invoice' => $filename]);

        return redirect()->to(Route('invoice.index'))->with('export', true);
    }

    public function checkExportStatus(Request $request)
    {
        // Retrieve the export filename from the session
        $filename = session('export_filename_invoice');
        
        // dd($filename);
        // Check if the file exists on the public disk
        if ($filename && Storage::exists($filename)) {
            // Provide the download URL if file exists
            $downloadUrl = s3_asset(true,10,$filename);
            return response()->json(['ready' => true, 'download_url' => $downloadUrl]);
        }
    
        return response()->json(['ready' => false, 'filename' => $filename]);
    }

    public function clearsession()
    {
        // Retrieve the export filename from the session
        $filename = session('export_filename_invoice');

        // Forget the session variable to prevent re-download on refresh
        session()->forget('export_filename_invoice');

        // Check if the file exists and delete it
        if ($filename && Storage::exists($filename)) {
            Storage::delete($filename);
            Log::info("File deleted from storage: " . $filename);
        }

        return response()->json(['status' => 'export session cleared and file deleted']);
    }

}
