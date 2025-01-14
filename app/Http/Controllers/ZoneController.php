<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\Warehouse;
use App\Models\Sensor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ZoneController extends Controller
{
    public function index()
    {
        $zones = Zone::byCompany(Auth::user()->company_id)->paginate(10);
        $warehouses = Warehouse::byCompany(Auth::user()->company_id)->get();
        $sensors = Sensor::byCompany(Auth::user()->company_id)->get();

        return view('zone.index', compact('warehouses', 'sensors', 'zones'));
    }

    public function create()
    {
        $warehouses = Warehouse::byCompany(Auth::user()->company_id)->get();
        $sensors = Sensor::byCompany(Auth::user()->company_id)->get();

        return view('zone.index', compact('warehouses', 'sensors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'sensors' => 'array',
        ]);

        DB::beginTransaction();
        try {
            $zone = Zone::create([
                'warehouse_id' => $request->warehouse_id,
                'name' => $request->name,
                'user_id' => Auth::id(),
            ]);

            $sensorData = [];
            if($request->sensors)
            {
                foreach ($request->sensors as $sensor) {
                    $sensorData[] = [
                        'sensor_id' => $sensor['id'],
                        'sensor_code' => $sensor['sensor_code'],
                        'value' => $sensor['value'],
                    ];
                }

                $zone->sensors()->sync($sensorData);
            }
            DB::commit();
            return redirect()->route('zone.index')->with('store', true);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }


        return redirect()->route('zone.index')->with('store', true);
    }

    public function edit(Zone $zone)
    {
        $zones = Zone::with(['warehouse', 'sensors'])->paginate(10);
        $warehouses = Warehouse::all();
        $sensors = Sensor::all();
        return view('zone.index', compact('zones', 'zone', 'warehouses', 'sensors'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Validasi Data
            $request->validate([
                'name' => 'required|string|max:255',
                'warehouse_id' => 'required|exists:warehouses,id',
                'sensors' => 'nullable|array',
                'sensors.*.id' => 'required|exists:sensors,id',
                'sensors.*.sensor_code' => 'nullable|string|max:255',
                'sensors.*.value' => 'required',
            ]);

            // dd("here");
            // Ambil Zone yang akan diperbarui
            $zone = Zone::findOrFail($id);
            
            // Update data Zone
            $zone->update([
                'name' => $request->name,
                'warehouse_id' => $request->warehouse_id,
            ]);

            // Hapus semua sensor yang terkait dengan zone ini
        $zone->sensors()->detach();

        // Siapkan array untuk relasi baru
        $syncData = [];

        if ($request->has('sensors')) {
            foreach ($request->sensors as $sensor) {
                $syncData[$sensor['id']] = [
                    'sensor_code' => $sensor['sensor_code'],
                    'value' => $sensor['value'],
                ];
            }
        }

        // Tambahkan sensor baru dengan pivot data
        $zone->sensors()->attach($syncData);

            DB::commit();

            return redirect()->route('zone.index')->with('update', true);
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function destroy(Zone $zone)
    {
        $zone->sensors()->detach();
        $zone->delete();

        return redirect()->route('zone.index')->with('delete', true);
    }
}