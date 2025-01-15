<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\Sensor;
use App\Models\Warehouse;
use App\Models\SensorZone;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ZoneController extends Controller
{
    public function index()
    {
        $query = Zone::byCompany(Auth::user()->company_id)->with('warehouse', 'sensors');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%") // Cari berdasarkan Nama Rack
                  ->orWhereHas('warehouse', function ($q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%"); // Cari berdasarkan Nama Zone
                  })
                  ->orWhereHas('sensors', function ($q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%") // Cari berdasarkan Nama Sensor
                        ->orWhere(function ($query) use ($search) {
                            $query->whereRaw("EXISTS (
                                SELECT 1 FROM sensor_zone
                                WHERE sensor_zone.sensor_id = sensors.id
                                AND (sensor_zone.sensor_code LIKE ? OR sensor_zone.value LIKE ?)
                            )", ["%{$search}%", "%{$search}%"]);
                        });
                  });
            });
        }
        
        $zones = $query->paginate(10);
        
        $zones = $query->paginate(10);
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
                        'sensor_id' => $sensor['sensor_id'],
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
            return back()->with('error', 'Terjadi kesalahan: ' . $th->getMessage());
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
                'sensors.*.id' => 'nullable|exists:sensor_zone,id',
                'sensors.*.sensor_id' => 'required|exists:sensors,id',
                'sensors.*.sensor_code' => 'nullable|string|max:255',
                'sensors.*.value' => 'nullable|string|max:255',
            ]);

            // Ambil Zone yang akan diperbarui
            $zone = Zone::byCompany(Auth::user()->company_id)->findOrFail($id);
            
            // Update data Zone
            $zone->update([
                'name' => $request->name,
                'warehouse_id' => $request->warehouse_id,
            ]);


            $existingSensors = $zone->sensors()->pluck('sensor_zone.id', 'sensor_zone.id')->toArray();
            $existingSensors = array_map('strval', $existingSensors); // Ubah semua ID ke string

            $requestSensorIds = collect($request->sensors)->pluck('id')->map(fn($id) => (string) $id)->toArray(); // Ubah ke string

            $sensorsToDelete = array_diff($existingSensors, $requestSensorIds);

            $newSensors = [];
            foreach ($request->sensors as $sensor) 
            {
                $pivotId = $sensor['id'] ?? null;
                $sensorId = $sensor['sensor_id'];
                $sensorCode = $sensor['sensor_code'];
                $sensorValue = $sensor['value'];

                if (isset($existingSensors[$pivotId])) {
                    SensorZone::where('id', $pivotId)->update([
                        'sensor_id' => $sensorId,
                        'sensor_code' => $sensorCode,
                        'value' => $sensorValue,
                    ]);
                } else 
                {
                    // ✅ Sensor baru, tambahkan ke array untuk insert
                    $newSensors[] = [
                        'sensor_id' => $sensorId,
                        'sensor_code' => $sensorCode,
                        'value' => $sensorValue,
                    ];
                }
            }

            // 6️⃣ Insert sensor baru
            if (!empty($newSensors)) {
                $zone->sensors()->attach($newSensors);
            }

            // Tambahkan sensor baru dengan pivot data
            if (!empty($sensorsToDelete)) 
            {
                $sensorsToDelete = SensorZone::whereIn('id', $sensorsToDelete)->delete();
            }
            
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