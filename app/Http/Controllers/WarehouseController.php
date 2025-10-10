<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Zone;
use App\Models\WarehouseType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse; 

class WarehouseController extends Controller
{
    public function index()
    {
        $query = Warehouse::byCompany(Auth::user()->company_id)->with('warehouseType');

        // Cek apakah ada input pencarian
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('location', 'LIKE', "%{$search}%")
                  ->orWhereHas('warehouseType', function ($q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%"); // Cari berdasarkan tipe gudang
                  });
            });
        }
        
        // Paginasi hasil
        $warehouses = $query->paginate(10);
        $warehouse_types = WarehouseType::all();

        return view('warehouse.index', compact('warehouses', 'warehouse_types'));
    }

    public function create()
    {
        $warehouse_types = WarehouseType::all();
        return view('warehouse.create', compact('warehouse_types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'location' => 'required',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'warehouse_type_id' => 'required|exists:warehouse_types,id'
        ]);

        Warehouse::create([
            'name' => $request->name,
            'location' => $request->location,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'warehouse_type_id' => $request->warehouse_type_id,
            'user_id' => auth()->user()->id
        ]);

        return redirect()->route('warehouse.index')->with('store', true);
    }

    public function show(Warehouse $warehouse)
    {
        return view('warehouse.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse)
    {
        $warehouse_types = WarehouseType::all();
        $warehouses = Warehouse::byCompany(Auth::user()->company_id)->with('warehouseType')->paginate(10);
        $warehouseEdit = $warehouse;

        return view('warehouse.index', compact('warehouse', 'warehouse_types','warehouses', 'warehouseEdit'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'name' => 'required|max:255',
            'location' => 'required',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'warehouse_type_id' => 'required|exists:warehouse_types,id'
        ]);

        $warehouse->update([
            'name' => $request->name,
            'location' => $request->location,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'warehouse_type_id' => $request->warehouse_type_id
        ]);

        return redirect()->route('warehouse.index')->with('update', true);
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();
        return redirect()->route('warehouse.index')->with('delete', true);
    }

    public function getLocation(Request $request): JsonResponse
    {
        try {
            $warehouseId = $request->warehouse_id;
            $zoneId = $request->zone_id;
            $excludeRackId = $request->exclude_rack_id; // untuk edit mode

            // ==========================================
            // LEVEL 1: GET ALL WAREHOUSES
            // ==========================================
            if (!$warehouseId) {
                $warehouses = Warehouse::select('id', 'name')
                    ->orderBy('name', 'asc')
                    ->get();

                return response()->json([
                    'success' => true,
                    'message' => 'Warehouses berhasil dimuat',
                    'level' => 'warehouse',
                    'total' => $warehouses->count(),
                    'data' => $warehouses
                ], 200);
            }

            // ==========================================
            // LEVEL 2: GET ZONES by WAREHOUSE
            // ==========================================
            if ($warehouseId && !$zoneId) {
                $warehouse = Warehouse::find($warehouseId);

                if (!$warehouse) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Warehouse tidak ditemukan',
                        'level' => 'warehouse',
                        'data' => []
                    ], 404);
                }

                $zones = $warehouse->zones()
                    ->select('id', 'warehouse_id', 'name')
                    ->orderBy('name', 'asc')
                    ->get();

                if ($zones->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Warehouse ini belum memiliki zone',
                        'level' => 'zone',
                        'warehouse' => [
                            'id' => $warehouse->id,
                            'name' => $warehouse->name
                        ],
                        'data' => []
                    ], 200);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Zones berhasil dimuat',
                    'level' => 'zone',
                    'warehouse' => [
                        'id' => $warehouse->id,
                        'name' => $warehouse->name
                    ],
                    'total' => $zones->count(),
                    'data' => $zones
                ], 200);
            }

            // ==========================================
            // LEVEL 3: GET RACKS by WAREHOUSE + ZONE
            // ==========================================
            if ($warehouseId && $zoneId) {
                $warehouse = Warehouse::find($warehouseId);

                if (!$warehouse) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Warehouse tidak ditemukan',
                        'level' => 'warehouse',
                        'data' => []
                    ], 404);
                }

                $zone = Zone::find($zoneId);

                if (!$zone) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Zone tidak ditemukan',
                        'level' => 'zone',
                        'data' => []
                    ], 404);
                }

                // VALIDASI: Zone harus milik warehouse
                if ($zone->warehouse_id != $warehouseId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Zone tidak ditemukan di warehouse yang dipilih',
                        'level' => 'zone',
                        'data' => []
                    ], 404);
                }

                // Query racks
                $query = $zone->racks()
                    ->select('id', 'zone_id', 'name');

                // KONDISI EDIT MODE - Exclude rack tertentu
                $isEditMode = false;
                if ($excludeRackId) {
                    $query->where('id', '!=', $excludeRackId);
                    $isEditMode = true;
                }

                // Filter tambahan (optional)
                // if ($request->has('active_only') && $request->active_only == true) {
                //     $query->where('status', 'active');
                // }

                // if ($request->has('available_only') && $request->available_only == true) {
                //     $query->where('capacity', '>', 0);
                // }

                $racks = $query->orderBy('name', 'asc')->get();

                if ($racks->isEmpty()) {
                    $message = $isEditMode 
                        ? 'Tidak ada rack lain yang tersedia di zone ini'
                        : 'Zone ini belum memiliki rack';

                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'level' => 'rack',
                        'is_edit_mode' => $isEditMode,
                        'warehouse' => [
                            'id' => $warehouse->id,
                            'name' => $warehouse->name
                        ],
                        'zone' => [
                            'id' => $zone->id,
                            'name' => $zone->name,
                            'code' => null
                        ],
                        'data' => []
                    ], 200);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Racks berhasil dimuat',
                    'level' => 'rack',
                    'is_edit_mode' => $isEditMode,
                    'excluded_rack_id' => $excludeRackId,
                    'warehouse' => [
                        'id' => $warehouse->id,
                        'name' => $warehouse->name
                    ],
                    'zone' => [
                        'id' => $zone->id,
                        'name' => $zone->name,
                        'code' => null
                    ],
                    'total' => $racks->count(),
                    'data' => $racks
                ], 200);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}