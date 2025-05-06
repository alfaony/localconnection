<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::byCompany(auth()->user()->company_id)->with('picUser')->latest()->paginate(10);
        return view('vehicle.index', compact('vehicles'));
    }

    public function create()
    {
        $users = User::byCompany(auth()->user()->company_id)->pluck('name', 'id');
        $typeVehicles = config('custom.type_vehicle');
        return view('vehicle.createOrEdit', compact('users','typeVehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|string',
            'vehicle_type' => 'required|string',
            'type' => 'required|string',
            'position' => 'required|string',
            'pic_user_id' => 'required|uuid|exists:users,id',
            'service_terakhir' => 'nullable|date',
            'subscription_stnk' => 'nullable|date',
            'subscription_kir' => 'nullable|date',
        ]);
    
        Vehicle::create([
            'company_id' => auth()->user()->company_id,
            'vehicle_id' => $request->vehicle_id,
            'vehicle_type' => $request->vehicle_type,
            'type' => $request->type,
            'position' => $request->position,
            'pic_user_id' => $request->pic_user_id,
            'service_terakhir' => $request->service_terakhir,
            'subscription_stnk' => $request->subscription_stnk,
            'subscription_kir' => $request->subscription_kir,
        ]);
    

        return redirect()->route('vehicle.index')->with('store', true);
    }


    public function show($id)
    {
        $vehicle = Vehicle::byCompany(auth()->user()->company_id)->with(['activities', 'picUser'])->findOrFail($id);
        return view('vehicle.show', compact('vehicle'));
    }

    public function edit($id)
    {
        $vehicle = Vehicle::byCompany(auth()->user()->company_id)->findOrFail($id);
        $users = User::pluck('name', 'id');
        $typeVehicles = config('custom.type_vehicle');

        return view('vehicle.createOrEdit', compact('vehicle', 'users','typeVehicles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'service_terakhir' => 'nullable|date',
            'subscription_stnk' => 'nullable|date',
            'subscription_kir' => 'nullable|date',
            'pic_user_id' => 'required|uuid'
        ]);

        $vehicle = Vehicle::byCompany(auth()->user()->company_id)->findOrFail($id);
        
        $vehicle->update([
            'vehicle_id' => $request->input('vehicle_id', $vehicle->vehicle_id),
            'vehicle_type' => $request->input('vehicle_type', $vehicle->vehicle_type),
            'type' => $request->input('type', $vehicle->type),
            'position' => $request->input('position', $vehicle->position),
            'pic_user_id' => $request->input('pic_user_id', $vehicle->pic_user_id),
            'service_terakhir' => $request->input('service_terakhir', $vehicle->service_terakhir),
            'subscription_stnk' => $request->input('subscription_stnk', $vehicle->subscription_stnk),
            'subscription_kir' => $request->input('subscription_kir', $vehicle->subscription_kir),
        ]);

        return redirect()->route('vehicle.show', $vehicle->id)->with('update', true);
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::byCompany(auth()->user()->company_id)->findOrFail($id);
        $vehicle->delete();

        return redirect()->route('vehicle.index')->with('delete', true);
    }
}
