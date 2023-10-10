<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\SuplierRequest;

use App\Models\Suplier;
use App\Models\Project;
use App\Models\Purchase;

class SuplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $suplier = Suplier::where('name','like', '%' . $request->get('suplier') . '%')
        ->OrderBy('created_at','asc')->paginate(10);

        $totalSuplier = count(Suplier::get());

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
        $project = Project::get();

        return view('suplier.createOrEdit',compact('nomor','project'));
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
            $suplier->save();

            $descriptions = $request->input('description');
            $prices = $request->input('price');
            $qtys = $request->input('qty');
            $subTotals = $request->input('sub_total');
            
            // Loop melalui salah satu array (karena semua memiliki panjang yang sama)
            for ($i = 0; $i < count($descriptions); $i++) 
            {
                $purchase = new Purchase();
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
        $project = Project::get();

        return view('suplier.createOrEdit',compact('suplier','nomor','project'));   
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function update(SuplierRequest $request, Suplier $suplier)
    {
        // dd($request->all());
        try {
            DB::beginTransaction();
            //code...
            $suplier->user_id = Auth::user()->id;
            $suplier->project_id = $request->post('project');
            $suplier->date = $request->post('date');
            $suplier->name = $request->post('name');
            $suplier->phone = $request->post('phone');
            $suplier->save();

            $descriptions = $request->input('description');
            $prices = $request->input('price');
            $qtys = $request->input('qty');
            $subTotals = $request->input('sub_total');
            $idChild = $request->input('idChild');
            
            // Loop melalui salah satu array (karena semua memiliki panjang yang sama)
            for ($i = 0; $i < count($descriptions); $i++) 
            {

                $id = $idChild[$i];

                if(!$id)
                {
                    $purchase = new Purchase();
                    
                    $purchase->description = $descriptions[$i];
                    $purchase->price = $prices[$i];
                    $purchase->qty = $qtys[$i];
                    $purchase->sub_total_price = $subTotals[$i];
    
                    $suplier->purchase()->save($purchase);
                }else
                {
                    $purchases = Purchase::find($id);
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
    public function destroy(Suplier $suplier)
    {
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
}
