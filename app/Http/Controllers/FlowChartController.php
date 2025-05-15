<?php

namespace App\Http\Controllers;

use App\Models\FlowChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FlowChartController extends Controller
{
    // Menampilkan semua flowchart
    public function index()
    {
        $charts = FlowChart::byCompany(Auth::user()->company_id)
            ->when(request('search'), function($query) {
                $query->where('name', 'LIKE', '%' . request('search') . '%');
            })->paginate(10);
        return view('flowcharts.index', compact('charts'));
    }

    // Form tambah flowchart
    public function create()
    {
        return view('flowcharts.createOrEdit');
    }

    // Simpan flowchart baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'model' => 'required|string'
        ]);


        $chart = FlowChart::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => Auth::user()->id,
            'company_id' => Auth::user()->company_id,
            'json_model' => $request->model
        ]);

        return redirect()->route('flowchart.index')->with('store', true);
    }

    // Tampilkan diagram + canvas
    public function show($id)
    {
        $chart = FlowChart::findOrFail($id);
        return view('flowcharts.show', compact('chart'));
    }

    // Form edit
    public function edit($id)
    {
        $chart = FlowChart::findOrFail($id);
        return view('flowcharts.createOrEdit', compact('chart'));
    }

    // Update flowchart
    public function update(Request $request, $id)
    {
        $chart = FlowChart::findOrFail($id);
        $chart->update([
            'name' => $request->name,
            'description' => $request->description,
            'json_model' => $request->model,
        ]);

        return redirect()->route('flowchart.index')->with('update', true);
    }

    // Hapus flowchart
    public function destroy($id)
    {
        $chart = FlowChart::findOrFail($id);
        $chart->delete();

        return redirect()->route('flowchart.index')->with('delete', true);
    }
}
