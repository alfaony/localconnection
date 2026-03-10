<?php

namespace App\Http\Controllers;

use App\Models\Rack;
use App\Models\Zone;
use App\Models\Sensor;
use App\Models\RackSensor;
use App\Models\ProductStore;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RackController extends Controller
{
    public function index()
    {
        $query = Rack::byCompany(Auth::user()->company_id)->with('zone', 'sensors');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%") // Cari berdasarkan Nama Rack
                ->orWhereHas('zone', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%"); // Cari berdasarkan Nama Zone
                })
                ->orWhereHas('sensors', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%") // Cari berdasarkan Nama Sensor
                        ->orWhere(function ($query) use ($search) {
                            $query->whereRaw("EXISTS (
                                SELECT 1 FROM rack_sensor
                                WHERE rack_sensor.sensor_id = sensors.id
                                AND (rack_sensor.sensor_code LIKE ? OR rack_sensor.value LIKE ?)
                            )", ["%{$search}%", "%{$search}%"]);
                        });
                });
            });
        }

        $racks = $query->paginate(10);
        $zones = Zone::byCompany(Auth::user()->company_id)->get();
        $sensors = Sensor::byCompany(Auth::user()->company_id)->get();

        return view('rack.index', compact('racks', 'zones', 'sensors'));
    }

    public function create()
    {
        return view('rack.create', compact('zones', 'sensors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'zone_id' => 'required|exists:zones,id',
            'sensors' => 'nullable|array',
            'sensors.*.sensor_id' => 'required_with:sensors.*.sensor_id|exists:sensors,id',
            'sensors.*.sensor_code' => 'nullable|string|max:255',
            'sensors.*.value' => 'required_with:sensors.*',
        ], [
            'name.required' => 'Nama Rack harus diisi',
            'name.string' => 'Nama Rack harus berupa string',
            'name.max' => 'Nama Rack maksimal 255 karakter',
            'description.string' => 'Deskripsi harus berupa string',
            'zone_id.required' => 'ID Zona harus diisi',
            'zone_id.exists' => 'ID Zona tidak ditemukan',
            'sensors.array' => 'Sensor harus berupa array',
            'sensors.*.sensor_id.exists' => 'ID Sensor tidak ditemukan',
            'sensors.*.sensor_id.required_with' => 'ID Sensor harus diisi jika Sensor Code dan Value diisi',
            'sensors.*.sensor_code.string' => 'Sensor Code harus berupa string',
            'sensors.*.sensor_code.max' => 'Sensor Code maksimal 255 karakter',
            'sensors.*.value.required_with' => 'Value harus diisi',
        ]);

        $rack = Rack::create([
            'zone_id' => $validated['zone_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->has('sensors')) {
            $rack->sensors()->attach($validated['sensors']);
        }

        return redirect()->route('rack.index')->with('store', true);
    }

    public function edit(Rack $rack)
    {
        $zones = Zone::byCompany(Auth::user()->company_id)->get();
        $sensors = Sensor::byCompany(Auth::user()->company_id)->get();
        $racks = Rack::byCompany(Auth::user()->company_id)->with('zone', 'sensors')->paginate(10);
        return view('rack.index', compact('rack', 'zones', 'sensors', 'racks'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            //  Validasi Data
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'zone_id' => 'required|exists:zones,id',
                'sensors' => 'nullable|array',
                'sensors.*.id' => 'nullable|exists:rack_sensor,id', // ID dari pivot table
                'sensors.*.sensor_id' => 'required_with:sensors.*.sensor_id|exists:sensors,id',
                'sensors.*.sensor_code' => 'nullable|string|max:255',
                'sensors.*.value' => 'required_with:sensors.*',
            ], [
                'name.required' => 'Nama Rack harus diisi',
                'name.string' => 'Nama Rack harus berupa string',
                'name.max' => 'Nama Rack maksimal 255 karakter',
                'description.string' => 'Deskripsi harus berupa string',
                'zone_id.required' => 'ID Zona harus diisi',
                'zone_id.exists' => 'ID Zona tidak ditemukan',
                'sensors.array' => 'Sensor harus berupa array',
                'sensors.*.id.exists' => 'ID Sensor Zone tidak ditemukan',
                'sensors.*.sensor_id.required_with' => 'ID Sensor harus diisi jika Sensor Code dan Value diisi',
                'sensors.*.sensor_id.exists' => 'ID Sensor tidak ditemukan',
                'sensors.*.sensor_code.string' => 'Sensor Code harus berupa string',
                'sensors.*.sensor_code.max' => 'Sensor Code maksimal 255 karakter',
                'sensors.*.value.required_with' => 'Value harus diisi',
            ]);

            //  Ambil Rack yang akan diperbarui
            $rack = Rack::byCompany(Auth::user()->company_id)->findOrFail($id);
            
            //  Update data Rack
            $rack->update([
                'name' => $request->name,
                'description' => $request->description,
                'zone_id' => $request->zone_id,
            ]);

            // Ambil sensor yang sudah ada di dalam rack (pivot)
            $existingSensors = $rack->sensors()->pluck('rack_sensor.id', 'rack_sensor.id')->toArray();
            $existingSensors = array_map('strval', $existingSensors); // Ubah semua ID ke string agar tidak ada perbedaan tipe

            //  Ambil ID sensor dari request (jika ada) dan ubah ke string
            $requestSensorIds = collect($request->sensors)->pluck('id')->filter()->map(fn($id) => (string) $id)->toArray();

            //  Sensor yang harus dihapus (soft delete)
            $sensorsToDelete = array_diff($existingSensors, $requestSensorIds);

            //  Proses Insert & Update Sensor
            $newSensors = [];
            if($request->sensors)
            {
                foreach ($request->sensors as $sensor) 
                {
                    $pivotId = $sensor['id'] ?? null;
                    $sensorId = $sensor['sensor_id'];
                    $sensorCode = $sensor['sensor_code'];
                    $sensorValue = $sensor['value'];
    
                    if ($pivotId && isset($existingSensors[$pivotId])) {
                        //  Sensor sudah ada, lakukan update
                        RackSensor::where('id', $pivotId)->update([
                            'sensor_id' => $sensorId,
                            'sensor_code' => $sensorCode,
                            'value' => $sensorValue,
                        ]);
                    } else {
                        //  Sensor baru, tambahkan ke array untuk insert
                        $newSensors[] = [
                            'rack_id' => $rack->id,
                            'sensor_id' => $sensorId,
                            'sensor_code' => $sensorCode,
                            'value' => $sensorValue,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            //  Insert sensor baru ke dalam pivot table
            if (!empty($newSensors)) {
                RackSensor::insert($newSensors);
            }

            //  Soft Delete sensor yang tidak ada dalam request
            if (!empty($sensorsToDelete)) 
            {
                RackSensor::whereIn('id', $sensorsToDelete)->delete();
            }
            
            DB::commit();
            return redirect()->route('rack.index')->with('update', 'Rack berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(Rack $rack)
    {
        $rack->load(['zone.warehouse', 'sensors', 'productStores']);

        $availableProductStores = ProductStore::byCompany(Auth::user()->company_id)
            ->whereNull('rack_id')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'barcode']);

        return view('rack.show', compact('rack', 'availableProductStores'));
    }

    public function assignProductStore(Request $request, Rack $rack)
    {
        $request->validate([
            'product_store_ids' => 'required|array|min:1',
            'product_store_ids.*' => 'exists:product_stores,id',
        ], [
            'product_store_ids.required' => 'Pilih minimal satu product store',
            'product_store_ids.min' => 'Pilih minimal satu product store',
        ]);

        // Hanya ambil product store milik company yg belum punya rack (anti-duplicate)
        $toAssign = ProductStore::byCompany(Auth::user()->company_id)
            ->whereNull('rack_id')
            ->whereIn('id', $request->product_store_ids)
            ->get();

        if ($toAssign->isEmpty()) {
            return back()->with('error', 'Product store yang dipilih sudah ter-assign ke rack lain atau tidak ditemukan.');
        }

        $toAssign->each(fn($ps) => $ps->update(['rack_id' => $rack->id]));

        $count = $toAssign->count();
        return back()->with('assign_success', "{$count} product store berhasil di-assign ke rack.");
    }

    public function unassignProductStore(Request $request, Rack $rack)
    {
        $request->validate([
            'product_store_id' => 'required|exists:product_stores,id',
        ]);

        $productStore = ProductStore::byCompany(Auth::user()->company_id)
            ->where('rack_id', $rack->id)
            ->findOrFail($request->product_store_id);

        $productStore->update(['rack_id' => null]);

        return back()->with('unassign_success', 'Product store berhasil dilepas dari rack.');
    }

    public function destroy(Rack $rack)
    {
        $rack->delete();
        return redirect()->route('rack.index')->with('delete', true);
    }
}