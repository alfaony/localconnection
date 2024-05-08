<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\EquipmentReduction;
use App\Models\Equipment;
use App\Models\Reduction;

class EquipmentReductionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = EquipmentReduction::query();

        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }
    
        if ($request->filled('reduction_id')) {
            $query->where('reduction_id', $request->reduction_id);
        }
    
        $order = $request->input('order', 'desc'); // Default is 'desc'
        $query->orderBy('date', $order);
    
        $equipmentReductions = $query->paginate(10);

        $equipments = Equipment::select('id','name','total_stock')->byCompany(Auth::user()->company_id)->get();
        $reductions = Reduction::select('id','name')->byCompany(Auth::user()->company_id)->get();
        return view('equipment_reduction.index',compact('equipmentReductions', 'equipments', 'reductions'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $equipments = Equipment::select('id','name','total_stock')->byCompany(Auth::user()->company_id)->get();
        $reductions = Reduction::select('id','name')->byCompany(Auth::user()->company_id)->get();

        return view('equipment_reduction.createOrEdit',compact('equipments','reductions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Melakukan validasi data yang masuk
        $validatedData = $request->validate([
            'date'          => 'required|date',
            'reduction_id'  => 'required|exists:reductions,id', // pastikan reduction_id ada di tabel reductions
            'equipment_id'  => 'required|exists:equipment,id', // pastikan equipment_id ada di tabel equipments
            'stock'         => 'required|integer|min:1', // stock harus integer dan minimal 0
            'report'        => 'nullable|string',
            'found'         => 'nullable|string',
            'doing'         => 'nullable|string'
        ]);

        // Membuat instance baru dari EquipmentReduction
        $equipmentReduction = new EquipmentReduction();

        $equipmentReduction->date = $request->post('date');
        $equipmentReduction->reduction_id = $request->post('reduction_id');
        $equipmentReduction->equipment_id = $request->post('equipment_id');
        $equipmentReduction->user_id = Auth::user()->id;
        $equipmentReduction->stock = $request->post('stock');
        $equipmentReduction->report = $request->post('report');
        $equipmentReduction->found = $request->post('found');
        $equipmentReduction->doing = $request->post('doing');

        // Simpan instance ke dalam database
        $equipmentReduction->save();

        // Setelah menyimpan, arahkan kembali ke halaman index dengan pesan sukses
        return redirect()->route('equipment-reduction.index')->with('store', true);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EquipmentReduction  $equipmentReduction
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $reduction = EquipmentReduction::byCompany(Auth::user()->company_id)->where('slug',$slug)->first();
        $equipments = Equipment::select('id','name','total_stock')->byCompany(Auth::user()->company_id)->get();
        $reductions = Reduction::select('id','name')->byCompany(Auth::user()->company_id)->get();

        return view('equipment_reduction.show',compact('equipments','reductions', 'reduction'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EquipmentReduction  $equipmentReduction
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $reduction = EquipmentReduction::byCompany(Auth::user()->company_id)->where('slug',$slug)->first();
        $equipments = Equipment::select('id','name','total_stock')->byCompany(Auth::user()->company_id)->get();
        $reductions = Reduction::select('id','name')->byCompany(Auth::user()->company_id)->get();

        return view('equipment_reduction.createOrEdit',compact('equipments','reductions', 'reduction'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EquipmentReduction  $equipmentReduction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $validatedData = $request->validate([
            'date'          => 'required|date',
            'reduction_id'  => 'required|exists:reductions,id', // pastikan reduction_id ada di tabel reductions
            'equipment_id'  => 'required|exists:equipment,id', // pastikan equipment_id ada di tabel equipments
            'stock'         => 'required|integer|min:1', // stock harus integer dan minimal 0
            'report'        => 'nullable|string',
            'found'         => 'nullable|string',
            'doing'         => 'nullable|string'
        ]);

        // Membuat instance baru dari EquipmentReduction
        $equipmentReduction = EquipmentReduction::byCompany(Auth::user()->company_id)->where('slug',$slug)->first();

        $equipmentReduction->date = $request->input('date');
        $equipmentReduction->reduction_id = $request->input('reduction_id');
        $equipmentReduction->equipment_id = $request->input('equipment_id');
        $equipmentReduction->user_id = Auth::user()->id;
        $equipmentReduction->stock = $request->input('stock');
        $equipmentReduction->report = $request->input('report');
        $equipmentReduction->found = $request->input('found');
        $equipmentReduction->doing = $request->input('doing');

        // Simpan instance ke dalam database
        $equipmentReduction->save();

        // Setelah menyimpan, arahkan kembali ke halaman index dengan pesan sukses
        return redirect()->route('equipment-reduction.index')->with('update', true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EquipmentReduction  $equipmentReduction
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $reduction = EquipmentReduction::byCompany(Auth::user()->company_id)->where('slug',$slug)->first();
        $reduction->delete();

        return redirect()->route('equipment-reduction.index')->with('delete', true);

    }
}
