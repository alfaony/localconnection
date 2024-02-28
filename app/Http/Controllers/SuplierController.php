<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\SuplierRequest;
use Carbon\Carbon;

use App\Models\Suplier;
use App\Models\Project;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\WorkOrder;

class SuplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $suplier = Suplier::byCompany(Auth::user()->company_id)->where('name','like', '%' . $request->get('suplier') . '%')
        ->OrderBy('created_at','asc')->paginate(10);

        $totalSuplier = Suplier::byCompany(Auth::user()->company_id)->count();

        return view('suplier.index',compact('suplier','totalSuplier'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $nomor = $request->get('nomor');
        $project = Project::byCompany(Auth::user()->company_id)->whereDoesntHave('suplier')->orderBy('created_at', 'desc')->get();
        $dateCreate = Carbon::now()->format('Y-m-d');
        $product = Product::byCompany(Auth::user()->company_id)->get();


        return view('suplier.createOrEdit',compact('nomor','project','dateCreate','product'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SuplierRequest $request)
    {
        try {
            DB::beginTransaction();
            //code...
            $suplier = new Suplier();
            $suplier->user_id = Auth::user()->id;
            $suplier->project_id = $request->post('project');
            $suplier->date = $request->post('date');
            $suplier->name = $request->post('name');
            $suplier->phone = $request->post('phone');
            $suplier->budget_saving = $request->post('budget_saving') ? TRUE : FALSE;
            $suplier->budget_movement = $request->post('budget_movement') ? TRUE : FALSE;
            $suplier->note = $request->post('note');

            if ($request->hasFile('file')) 
            {
                // Hapus file lama jika ada        
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('suplier', $filename, 'public');
                $suplier->file = $filename;
            }
            $suplier->save();

            $product = $request->input('product');
            $descriptions = $request->input('description');
            $prices = $request->input('price');
            $qtys = $request->input('qty');
            $subTotals = $request->input('sub_total');
            
            // Loop melalui salah satu array (karena semua memiliki panjang yang sama)
            for ($i = 0; $i < count($descriptions); $i++) 
            {
                $purchase = new Purchase();
                $purchase->sort = $i + 1;
                $purchase->product_id = $product[$i];
                $purchase->description = $descriptions[$i];
                $purchase->price = $prices[$i];
                $purchase->qty = $qtys[$i];
                $purchase->sub_total_price = $subTotals[$i];

                $suplier->purchase()->save($purchase);
            }

            $suplier->total_price = $suplier->purchase()->sum('sub_total_price') ?? 0;
            $suplier->save();

            DB::commit();
            return redirect()->to(route('suplier.index'))->with('store',true);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error($th);
            DB::rollback();
            return redirect()->to(route('suplier.index'))->with('store',false);
        }
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function show(Suplier $supplier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function edit($slug,Request $request)
    {
        $nomor = $request->nomor ?? 0 ;
        $suplier = Suplier::where('slug', $slug)->firstOrFail();
        $project = Project::byCompany(Auth::user()->company_id)->whereDoesntHave('suplier')->orWhere('id', $suplier->project_id)->orderBy('created_at', 'desc')->get();
        $dateCreate = Carbon::parse($suplier->created_at)->format('Y-m-d');
        $product = Product::byCompany(Auth::user()->company_id)->get();


        return view('suplier.createOrEdit',compact('suplier','nomor','project','dateCreate','product'));   
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function update(SuplierRequest $request, $slug)
    {
        // dd($request->all());
        try {
            DB::beginTransaction();

            $suplier = Suplier::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            $suplier->user_id = Auth::user()->id;
            $suplier->project_id = $request->post('project');
            $suplier->date = $request->post('date');
            $suplier->name = $request->post('name');
            $suplier->phone = $request->post('phone');
            $suplier->budget_saving = $request->post('budget_saving') ? TRUE : FALSE;
            $suplier->budget_movement = $request->post('budget_movement') ? TRUE : FALSE;
            $suplier->note = $request->post('note');

            if ($request->hasFile('file')) 
            {
                // Hapus file lama jika ada        
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('suplier', $filename, 'public');
                $suplier->file = $filename;
            }
            $suplier->save();

            $product = $request->input('product');
            $descriptions = $request->input('description');
            $prices = $request->input('price');
            $qtys = $request->input('qty');
            $subTotals = $request->input('sub_total');
            $idChild = $request->input('idChild');
            
            // Loop melalui salah satu array (karena semua memiliki panjang yang sama)
            for ($i = 0; $i < count($product); $i++) 
            {

                $id = $idChild[$i];

                if(!$id)
                {
                    $purchase = new Purchase();
                    $purchase->sort = $i + 1;
                    $purchase->product_id = $product[$i];
                    $purchase->description = $descriptions[$i];
                    $purchase->price = $prices[$i];
                    $purchase->qty = $qtys[$i];
                    $purchase->sub_total_price = $subTotals[$i];
    
                    $suplier->purchase()->save($purchase);
                }else
                {
                    $purchases = Purchase::find($id);
                    $purchases->sort = $i + 1;
                    $purchases->product_id = $product[$i];
                    $purchases->description = $descriptions[$i];
                    $purchases->price = $prices[$i];
                    $purchases->qty = $qtys[$i];
                    $purchases->sub_total_price = $subTotals[$i];
                    $purchases->save();
                } 
            }

            $suplier->total_price = $suplier->purchase()->sum('sub_total_price') ?? 0;
            $suplier->save();

            DB::commit();
            return redirect()->to(route('suplier.index'))->with('update',true);
        } catch (\Throwable $th) 
        {
            //throw $th;
            // dd($th);
            Log::error($th);
            DB::rollback();
            return redirect()->to(route('suplier.index'))->with('update',false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $suplier = Suplier::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $suplier->purchase()->delete();
        $suplier->delete();

        return redirect()->back()->with('delete',true);
    }

    /**
     * 
     * Destroy Child
     */
    public function deletePurchase(Purchase $purchase)
    {

        $suplierId = $purchase->suplier_id;
        $purchase->delete();
        
        $suplier = Suplier::find($suplierId);
        $total = $suplier->purchase->sum('sub_total_price');
        $suplier->total_price = $total;
        $suplier->save();

        return redirect()->back()->with('deletePurchase',true);
    }

    /**
     * Get Product with Ajax
     */

     public function productPrice(Request $request)
    {
        $suplierProductId = $request->get('suplierProductId');
        $productId = $request->get('product');

        $suplierProduct = Purchase::find($suplierProductId);

        if($suplierProduct)
        {
            if($suplierProduct && ($suplierProduct->product_id == $productId))
            {
                $price = $suplierProduct->price;
            }else
            {
                $product = Product::find($productId);
                $price = $product->price_buy ?? 0;
            }
        }else
        {
            $product = Product::find($productId);
            $price = $product->price_buy ?? 0;
        }

        return 
        [
            'status' => 200,
            'message' => 'okay',
            'data' => $price
        ];
    }

    /**
     * Suggetion When Choose Supplier
     */
    public function suggestionWorkOrder($id)
    {
        $workOrder = WorkOrder::find($id);
        $workOrderProduct = $workOrder->workOrderProduct 
        ? $workOrder->workOrderProduct()
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
        
        $data = 
        [
            'product' => $workOrderProduct
        ];
        return $data;
    }
}
