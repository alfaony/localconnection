<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FlowChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FlowChartController extends Controller
{

    public function index(Request $request)
    {
        $charts = FlowChart::byCompany(Auth::user()->company_id)
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->search . '%');
            })
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'List flowcharts',
            'data' => $charts
        ]);
    }

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'description' => 'nullable|string',
        'model' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    $chart = FlowChart::create([
        'name'        => $request->name,
        'description' => $request->description,
        'user_id'     => Auth::id(),
        'company_id'  => Auth::user()->company_id,


        'json_model'  => is_array($request->model)
            ? json_encode($request->model)
            : $request->model
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Flowchart created',
        'data' => $chart
    ], 201);
}


    public function show($id)
    {
        $chart = FlowChart::find($id);

        if (!$chart) {
            return response()->json([
                'success' => false,
                'message' => 'Flowchart not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $chart
        ]);
    }

    public function update(Request $request, $id)
{
    $chart = FlowChart::find($id);

    if (!$chart) {
        return response()->json([
            'success' => false,
            'message' => 'Flowchart not found'
        ], 404);
    }

    $request->validate([
        'name' => 'required|string',
        'model' => 'required'
    ]);

    $chart->update([
        'name'        => $request->name,
        'description' => $request->description,
        'json_model'  => is_array($request->model)
            ? json_encode($request->model)
            : $request->model
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Flowchart updated',
        'data' => $chart
    ]);
}


    public function destroy($id)
    {
        $chart = FlowChart::find($id);

        if (!$chart) {
            return response()->json([
                'success' => false,
                'message' => 'Flowchart not found'
            ], 404);
        }

        $chart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Flowchart deleted'
        ]);
    }
}
