<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Equipment;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $equipments = Equipment::byCompany(Auth::user()->company_id)->paginate(10);
        return view('equipment.index',compact('equipments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('equipment.createOrEdit');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'total_stock' => 'required|integer|min:0' // memastikan stok tidak negatif
        ], [
            'total_stock.min' => 'The total stock must be a non-negative number.', // pesan kesalahan khusus
        ]);

        $equipment = new Equipment();
        $equipment->code = $this->generateCode();
        $equipment->name = $request->post('name');
        $equipment->total_stock = $request->post('total_stock');
        $equipment->user_id = Auth::user()->id;
        $equipment->save();

        return redirect()->to(route('equipment.index'))->with('store', true);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Equipment  $equipment
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $equipment = Equipment::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        return view('equipment.show',compact('equipment'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Equipment  $equipment
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $equipment = Equipment::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        return view('equipment.createOrEdit',compact('equipment'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Equipment  $equipment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'total_stock' => 'required|integer|min:0' // memastikan stok tidak negatif
        ], [
            'total_stock.min' => 'The total stock must be a non-negative number.', // pesan kesalahan khusus
        ]);

        $equipment = Equipment::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();

        $equipment->name = $request->post('name');
        $equipment->total_stock = $request->post('total_stock');
        $equipment->user_id = Auth::user()->id;
        $equipment->save();

        return redirect()->to(route('equipment.index'))->with('store', true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Equipment  $equipment
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $equipment = Equipment::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $equipment->delete();
        return redirect()->back()->with('delete',true);
    }

    /**
     * 
     * Generate private code
     */
    public function generateCode()
    {
        return Equipment::byCompany(Auth::user()->company_id)->max('code') + 1;
    }

    /**
     * History
     */
    public function history($slug)
    {
        $equipment = Equipment::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $activities = $equipment->activities()->orderBy('created_at', 'desc')->paginate(10);
        return view('equipment.history',compact('equipment', 'activities'));
    }
}
