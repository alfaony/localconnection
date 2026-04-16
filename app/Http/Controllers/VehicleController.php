<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::byCompany(auth()->user()->company_id)
            ->with('picUser')
            ->when(request('search'), function($query) {
                $query->where('vehicle_id', 'LIKE', '%' . request('search') . '%')
                      ->orWhere('vehicle_type', 'LIKE', '%' . request('search') . '%')
                      ->orWhere('type', 'LIKE', '%' . request('search') . '%')
                      ->orWhere('position', 'LIKE', '%' . request('search') . '%')
                      ->orwhereHas('picUser', function ($query) {
                          $query->where('name', 'LIKE', '%' . request('search') . '%');
                      })
                      ;
            })
            ->latest()
            ->paginate(10);
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
            'subscription_kir' => 'nullable|date',
            'pic_user_id' => 'nullable|uuid|exists:users,id',
            'subscription_stnk' => 'nullable|date',
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

    public function storePhoto(Request $request, $id)
    {
        $request->validate([
            'photo'       => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'description' => 'nullable|string|max:255',
        ]);

        $vehicle = Vehicle::byCompany(auth()->user()->company_id)->findOrFail($id);

        $file     = $request->file('photo');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path     = $file->storeAs('vehicle-photos', $filename, 'public');

        VehiclePhoto::create([
            'vehicle_id'  => $vehicle->id,
            'uploaded_by' => auth()->id(),
            'photo'       => $path,
            'description' => $request->description,
            'taken_at'    => $request->taken_at ?? now()->toDateString(),
        ]);

        return redirect()->route('vehicle.show', $vehicle->id)->with('photo_uploaded', true);
    }

    public function destroyPhoto($vehicleId, $photoId)
    {
        $vehicle = Vehicle::byCompany(auth()->user()->company_id)->findOrFail($vehicleId);
        $photo   = VehiclePhoto::where('vehicle_id', $vehicle->id)->findOrFail($photoId);

        Storage::disk('public')->delete($photo->photo);
        $photo->delete();

        return redirect()->route('vehicle.show', $vehicle->id)->with('photo_deleted', true);
    }

    public function infoPic()
    {
        $user    = auth()->user();
        $deadline = now()->addDays(30);

        // STNK/KIR deadline reminder (existing)
        $vehicles = Vehicle::where('pic_user_id', $user->id)
            ->where(function ($q) use ($deadline) {
                $q->whereDate('subscription_stnk', '<=', $deadline)
                  ->orWhereDate('subscription_kir', '<=', $deadline);
            })
            ->get();

        $html = view('vehicle.partial-vehicle', compact('vehicles'))->render();

        // Photo reminder (new) — vehicles without photo this month
        $photoReminders = Vehicle::where('pic_user_id', $user->id)
            ->whereDoesntHave('photos', function ($q) {
                $q->whereYear('taken_at', now()->year)
                  ->whereMonth('taken_at', now()->month);
            })
            ->get(['id', 'vehicle_id', 'vehicle_type', 'type'])
            ->map(fn($v) => [
                'id'           => $v->id,
                'vehicle_id'   => $v->vehicle_id,
                'vehicle_type' => $v->vehicle_type,
                'type'         => $v->type,
                'show_url'     => route('vehicle.show', $v->id),
            ]);

        return response()->json([
            'html'           => $html,
            'photoReminders' => $photoReminders,
            'bulan'          => now()->locale('id')->monthName,
            'tahun'          => now()->year,
        ]);
    }

    public function infoManager()
    {
        $user = auth()->user();
        $today = now();
        $deadline = now()->addDays(30);

        $vehicles = Vehicle::where('company_id', $user->company_id)
            ->where(function ($q) use ($deadline) {
                $q->whereDate('subscription_stnk', '<=', $deadline)
                ->orWhereDate('subscription_kir', '<=', $deadline)
                ;
            })
            ->get();

        $html = view('vehicle.partial-vehicle', compact('vehicles'))->render();
        return response()->json(['html' => $html]);
    }
}
