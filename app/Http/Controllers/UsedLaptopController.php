<?php

namespace App\Http\Controllers;

use App\Models\UsedLaptop;
use App\Models\MasterCheckItem;
use App\Models\UsedLaptopMedia;
use App\Models\UsedLaptopCheck;
use App\Models\UsedLaptopRepair;
use App\Models\WebhookSetting;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\UsedLaptopResource;

use App\Helpers\WebhookHelper;
use App\Helpers\Access;

class UsedLaptopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $appName = 'used_laptops';

    public function index()
    {
        return view('used_laptop.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkItems = MasterCheckItem::where('type', 'laptop_type')->byCompany(Auth::user()->company_id)->get();
        $laptopType = config('custom.postion_latpop');
        return view('used_laptop.createOrEdit', compact('checkItems', 'laptopType'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        DB::beginTransaction();
        try {
            // Validasi data utama laptop
            $validated = $request->validate([
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'zone_id' => 'required_with:warehouse_id|nullable|exists:zones,id',
                'rack_id' => 'required_with:warehouse_id|nullable|exists:racks,id',
                'weight' => 'nullable|numeric|min:0',
                'name' => 'required|string|max:255',
                'brand' => 'required|string|max:255',
                'serial_number' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        $exists = UsedLaptop::where('serial_number', $value)
                            ->byCompany(Auth::user()->company_id)
                            ->exists();
                        
                        if ($exists) {
                            $fail('Serial number sudah terdaftar di sistem.');
                        }
                    }
                ],
                'processor' => 'required|string|max:255',
                'ram' => 'required|string|max:255',
                'ssd' => 'required|string|max:255',
                'gpu' => 'nullable|string|max:255',
                'operating_system' => 'nullable|string|max:255',
                'purchase_price' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
                'photos' => 'nullable|array',
                'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
                'check_items' => 'nullable|array',
                'repairs' => 'nullable|array',
                'is_sold' => 'nullable|string',
            ]);

            // Simpan data laptop
            $laptop = new UsedLaptop();
            $laptop->weight = $validated['weight'] ?? null;
            $laptop->rack_id = $validated['rack_id'] ?? null;
            $laptop->is_sold = $validated['is_sold'] ?? null;
            $laptop->company_id = Auth::user()->company_id;
            $laptop->brand = $validated['brand'];
            $laptop->user_id = Auth::user()->id;
            $laptop->name = $validated['name'];
            $laptop->serial_number = $validated['serial_number'];
            $laptop->processor = $validated['processor'];
            $laptop->ram = $validated['ram'];
            $laptop->ssd = $validated['ssd'];
            $laptop->gpu = $validated['gpu'] ?? null;
            $laptop->operating_system = $validated['operating_system'] ?? null;
            $laptop->purchase_price = $validated['purchase_price'];
            $laptop->notes = $validated['notes'] ?? null;
            $laptop->user_id = auth()->id();
            $laptop->save();

            $url = route('used-laptop.show-qr', $laptop->slug);

            $qrPng = QrCode::format('png')->size(300)->generate($url);

            // Simpan ke disk
            $filename = 'qr_laptop_' . $laptop->slug.'_'.$laptop->serial_number.'.png';
            Storage::put('public/qrcodes/' . $filename, $qrPng);

            // Simpan path untuk view
            $laptop->update([
                'qr_code_path' => 'qrcodes/' . $filename,
            ]);
            
            // Simpan foto
             if ($request->hasFile('photos')) 
            {
                $photos = $request->file('photos');
                $photoOrder = [];
                
                // ✅ Baca urutan dari hidden input
                if ($request->has('new_photo_order') && !empty($request->new_photo_order)) {
                    $photoOrder = json_decode($request->new_photo_order, true);
                    
                    \Log::info('Using custom order:', ['order' => $photoOrder]);
                    
                    if (!is_array($photoOrder)) {
                        $photoOrder = array_keys($photos);
                        \Log::warning('Invalid order format, using default');
                    }
                } else {
                    $photoOrder = array_keys($photos);
                    \Log::info('Using default order:', ['order' => $photoOrder]);
                }
                
                // ✅ Simpan foto sesuai urutan
                foreach ($photoOrder as $displayOrder => $originalIndex) {
                    if (isset($photos[$originalIndex])) {
                        $photo = $photos[$originalIndex];
                        $path = $photo->store('used-laptop');
                        
                        UsedLaptopMedia::create([
                            'used_laptop_id' => $laptop->id,
                            'file_path' => $path,
                            'order' => $displayOrder,
                        ]);
                        
                        \Log::info('Photo saved:', [
                            'original_index' => $originalIndex,
                            'display_order' => $displayOrder,
                            'path' => $path
                        ]);
                    }
                }
            }
            
            // Simpan checklist
            foreach ($request->input('check_items') as $checkItemId => $checkData) {
                UsedLaptopCheck::create([
                    'used_laptop_id' => $laptop->id,
                    'master_check_item_id' => $checkItemId,
                    'status' => $checkData['condition'],
                    'notes' => $checkData['notes'] ?? null,
                    'checked_at' => now(),
                ]);
            }
            
            // Simpan kerusakan
            if ($request->has('repairs')) {
                foreach ($request->input('repairs') as $repairData) {
                    if(!isset($repairData['description']) || !isset($repairData['cost'])) continue;
                    UsedLaptopRepair::create([
                        'used_laptop_id' => $laptop->id,
                        'repair_item' => $repairData['description'],
                        'cost' => $repairData['cost'],
                    ]);
                }
            }

            // $payload = [
            //     'id' => $laptop->id,
            //     'is_sold' => $laptop->is_sold,
            //     'serial_number' => $laptop->serial_number,
            //     'brand' => $laptop->brand,
            //     'slug' => $laptop->slug,
            //     'name' => $laptop->name,
            //     'processor' => $laptop->processor,
            //     'ram' => $laptop->ram,
            //     'ssd' => $laptop->ssd,
            //     'gpu' => $laptop->gpu,
            //     'operating_system' => $laptop->operating_system,
            //     'notes' => $laptop->notes,
            //     'buying_price' => $laptop->purchase_price,
            //     'selling_price' => $laptop->suggested_selling_price,
            //     'images' => $laptop->media()->get()->map(function ($media) {
            //         return env('APP_URL') . s3_asset(true,10,$media->file_path);
            //     })->toArray(),
            // ];

            $payload = (new UsedLaptopResource($laptop))->resolve();

            // ✅ Hanya kirim webhook jika laptop sudah memiliki rack
            if ($laptop->rack_id) {
                WebhookHelper::sendWebhook(Auth::user()->company_id, $this->appName, 'store', $payload);
            }
            
            DB::commit();
            
            return redirect()->route('used-laptop.show', $laptop->slug)->with('success', 'Laptop berhasil ditambahkan!');
                
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menambahkan laptop: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UsedLaptop  $usedLaptop
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $laptop = UsedLaptop::where('slug', $slug)->byCompany(Auth::user()->company_id)
        ->with(['media' => function($query) {
            $query->orderBy('order', 'asc');
        }])
        ->firstOrFail();
        return view('used_laptop.show', compact('laptop'));
    }

    public function showQr($slug)
    {
        $laptop = UsedLaptop::where('slug', $slug)
        ->with(['media' => function($query) {
            $query->orderBy('order', 'asc');
        }])
        ->firstOrFail();
        return view('used_laptop.showQr', compact('laptop'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UsedLaptop  $usedLaptop
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $laptop = UsedLaptop::where('slug', $slug)->byCompany(Auth::user()->company_id)->firstOrFail();
        $laptopType = config('custom.postion_latpop');
        $checkItems = MasterCheckItem::where('type','laptop_type')->byCompany(Auth::user()->company_id)->get();
        return view('used_laptop.createOrEdit', compact('checkItems', 'laptop','laptopType'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UsedLaptop  $usedLaptop
     * @return \Illuminate\Http\Response
     */
     public function update(Request $request, $slug)
    {
        $laptop = UsedLaptop::where('slug', $slug)->byCompany(Auth::user()->company_id)->firstOrFail();

        if(!$laptop) 
        {
            return back()->with('error', 'Laptop tidak ditemukan');
        }
        return $this->saveLaptop($request, $laptop);
    }

    public function maskAsSold(Request $request, $slug)
    {
        DB::beginTransaction();
        try {
            $laptop = UsedLaptop::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            $laptop->update([
                'is_sold' => true,
                'sold_price' => $request->sold_price,
                'sold_at' => $request->sold_at,
            ]);
            
            // $payload = [
            //     'id' => $laptop->id,
            //     'is_sold' => $laptop->is_sold,
            //     'serial_number' => $laptop->serial_number,
            //     'brand' => $laptop->brand,
            //     'slug' => $laptop->slug,
            //     'name' => $laptop->name,
            //     'processor' => $laptop->processor,
            //     'ram' => $laptop->ram,
            //     'ssd' => $laptop->ssd,
            //     'gpu' => $laptop->gpu,
            //     'operating_system' => $laptop->operating_system,
            //     'notes' => $laptop->notes,
            //     'buying_price' => $laptop->purchase_price,
            //     'selling_price' => $laptop->suggested_selling_price,
            //     'images' => $laptop->media()->get()->map(function ($media) {
            //         return env('APP_URL') . s3_asset(true,10,$media->file_path);
            //     })->toArray(),
            // ];
            $payload = (new UsedLaptopResource($laptop))->resolve();

            // ✅ Hanya kirim webhook jika laptop memiliki rack (QC PASSED)
            if ($laptop->rack_id) {
                WebhookHelper::sendWebhook(Auth::user()->company_id, $this->appName, 'sold', $payload);
            }

            DB::commit();
            return redirect()->route('used-laptop.show', $laptop->slug)->with('success', 'Laptop berhasil ditandai sebagai terjual!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menandai laptop sebagai terjual: ' . $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UsedLaptop  $usedLaptop
     * @return \Illuminate\Http\Response
     */
   
    private function saveLaptop(Request $request, UsedLaptop $laptop = null)
    {
        DB::beginTransaction();
        
        try {
            // Validasi data utama laptop
            $validated = $request->validate([
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'zone_id' => 'required_with:warehouse_id|nullable|exists:zones,id',
                'rack_id' => 'required_with:warehouse_id|nullable|exists:racks,id',
                'weight' => 'nullable|numeric|min:0',
                'name' => 'required|string|max:255',
                'brand' => 'required|string|max:255',
                'serial_number' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($laptop) {
                        $query = UsedLaptop::where('serial_number', $value)->byCompany(Auth::user()->company_id);
                        
                        // Exclude current laptop when editing
                        if ($laptop) {
                            $query->where('id', '!=', $laptop->id);
                        }
                        
                        if ($query->exists()) {
                            $fail('Serial number sudah terdaftar di sistem.');
                        }
                    }
                ],
                'processor' => 'required|string|max:255',
                'ram' => 'required|string|max:255',
                'ssd' => 'required|string|max:255',
                'gpu' => 'nullable|string|max:255',
                'operating_system' => 'nullable|string|max:255',
                'purchase_price' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
                'photos' => 'nullable|array',
                'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
                'check_items' => 'required|array|min:1',
                'repairs' => 'nullable|array',
                'is_sold' => 'nullable|string',
            ]);

            // Simpan atau update data laptop
            if ($laptop) {
                // Track rack changes untuk webhook
                $oldRackId = $laptop->rack_id;
                
                $laptop->update([
                    'is_sold' => $validated['is_sold'],
                    'weight' => $validated['weight'] ?? null,
                    'serial_number' => $validated['serial_number'],
                    'name' => $validated['name'],
                    'brand' => $validated['brand'],
                    'processor' => $validated['processor'],
                    'ram' => $validated['ram'],
                    'ssd' => $validated['ssd'],
                    'gpu' => $validated['gpu'] ?? null,
                    'operating_system' => $validated['operating_system'] ?? null,
                    'purchase_price' => $validated['purchase_price'],
                    'notes' => $validated['notes'] ?? null,
                ]);
                if(Access::can('getLocation','warehouses'))
                {
                    $laptop->rack_id = $validated['rack_id'] ?? null;
                    $laptop->save();
                }
                
                // Track if rack was changed
                $rackChanged = $oldRackId != $laptop->rack_id;
            } else {
                $rackChanged = false;
                $laptop = UsedLaptop::create([
                    'weight' => $validated['weight'] ?? null,
                    'name' => $validated['name'],
                    'brand' => $validated['brand'],
                    'processor' => $validated['processor'],
                    'ram' => $validated['ram'],
                    'ssd' => $validated['ssd'],
                    'gpu' => $validated['gpu'] ?? null,
                    'operating_system' => $validated['operating_system'] ?? null,
                    'purchase_price' => $validated['purchase_price'],
                    'notes' => $validated['notes'] ?? null,
                    'user_id' => auth()->id(),
                ]);
            }
            
            // Simpan foto baru
            // ✅ UPDATE ORDER FOTO EXISTING (PENTING!)
        if ($request->has('photo_order') && !empty($request->photo_order)) {
            $photoOrder = json_decode($request->photo_order, true);
            if (is_array($photoOrder)) {
                foreach ($photoOrder as $item) {
                    UsedLaptopMedia::where('id', $item['id'])
                        ->update(['order' => $item['order']]);
                }
            }
        }
        
        // ✅ SIMPAN FOTO BARU DENGAN ORDER
        if ($request->hasFile('photos')) {
            $photos = $request->file('photos');
            $orderedPhotos = [];

            // Gunakan urutan hasil drag & drop jika tersedia
            if ($request->filled('new_photo_order')) {
                $orderMapping = json_decode($request->new_photo_order, true);

                if (is_array($orderMapping)) {
                    foreach ($orderMapping as $originalIndex) {
                        if (isset($photos[$originalIndex])) {
                            $orderedPhotos[] = $photos[$originalIndex];
                        }
                    }

                    // Tambahkan foto yang tidak ada di mapping (fallback)
                    foreach ($photos as $index => $photo) {
                        if (!in_array($index, $orderMapping, true)) {
                            $orderedPhotos[] = $photo;
                        }
                    }
                }
            }

            if (empty($orderedPhotos)) {
                $orderedPhotos = $photos;
            }

            $currentMaxOrder = $laptop->media()->max('order') ?? -1;

            foreach ($orderedPhotos as $offset => $photo) {
                $path = $photo->store('used-laptop');
                UsedLaptopMedia::create([
                    'used_laptop_id' => $laptop->id,
                    'file_path' => $path,
                    'order' => $currentMaxOrder + $offset + 1,
                ]);
            }
        }
        
        // ✅ UPDATE CHECKLIST (DENGAN PENGECEKAN NULL)
        $checkItems = $request->input('check_items', []); // Default empty array jika null
        
        if (!empty($checkItems) && is_array($checkItems)) {
            foreach ($checkItems as $checkItemId => $checkData) {
                // Hanya proses jika ada data condition
                if (isset($checkData['condition']) && !empty($checkData['condition'])) {
                    UsedLaptopCheck::updateOrCreate(
                        [
                            'used_laptop_id' => $laptop->id,
                            'master_check_item_id' => $checkItemId,
                        ],
                        [
                            'status' => $checkData['condition'],
                            'notes' => $checkData['notes'] ?? null,
                            'checked_at' => now(),
                        ]
                    );
                }
            }
        }

            
            // Hapus kerusakan yang tidak ada
            $existingRepairIds = $laptop->repairs->pluck('id')->toArray();
            $submittedRepairIds = [];
            
            // Update kerusakan
            if ($request->has('repairs')) {
                foreach ($request->input('repairs') as $index => $repairData) {
                    if (!empty($repairData['description'])) {
                        $repair = UsedLaptopRepair::updateOrCreate(
                            ['id' => $repairData['id'] ?? null],
                            [
                                'used_laptop_id' => $laptop->id,
                                'repair_item' => $repairData['description'],
                                'cost' => $repairData['cost'] ?? 0,
                            ]
                        );
                        $submittedRepairIds[] = $repair->id;
                    }
                }
            }
            
            // Hapus kerusakan yang tidak disubmit
            $repairsToDelete = array_diff($existingRepairIds, $submittedRepairIds);
            if (!empty($repairsToDelete)) {
                UsedLaptopRepair::destroy($repairsToDelete);
            }
            
            $shouldRun = WebhookSetting::byCompany(Auth::user()->company_id)
            ->hasApp($this->appName)
            ->exists();

             $setting = WebhookSetting::byCompany(Auth::user()->company_id)
            ->hasApp($this->appName)
            ->first();


            // ✅ Kirim webhook jika:
            // 1. Webhook setting ada
            // 2. Laptop memiliki rack_id (sudah QC PASSED)
            // 3. Atau rack baru saja diubah (untuk sync ketika rack di-assign)
            if($shouldRun && ($laptop->rack_id || $rackChanged))
            {
                // $payload = [
                //     'id' => $laptop->id,
                //     'is_sold' => $laptop->is_sold,
                //     'serial_number' => $laptop->serial_number,
                //     'brand' => $laptop->brand,
                //     'slug' => $laptop->slug,
                //     'name' => $laptop->name,
                //     'processor' => $laptop->processor,
                //     'ram' => $laptop->ram,
                //     'ssd' => $laptop->ssd,
                //     'gpu' => $laptop->gpu,
                //     'operating_system' => $laptop->operating_system,
                //     'notes' => $laptop->notes,
                //     'buying_price' => $laptop->purchase_price,
                //     'selling_price' => $laptop->suggested_selling_price,
                //     'images' => $laptop->media()->get()->map(function ($media) {
                //         return env('APP_URL') . s3_asset(true,10,$media->file_path);
                //     })->toArray(),
                // ];

                $payload = (new UsedLaptopResource($laptop))->resolve();


                WebhookHelper::sendWebhook(Auth::user()->company_id, $this->appName, 'update', $payload);                
            }

            DB::commit();
            
            return redirect()->route('used-laptop.index')
                ->with('success', $laptop ? 'Laptop berhasil diperbarui!' : 'Laptop berhasil ditambahkan!');
                
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan laptop: ' . $e->getMessage());
        }
    }

    public function destroy($slug)
    {
        $laptop = UsedLaptop::where('slug', $slug)->firstOrFail();
        $payload = [
            'id' => $laptop->id,
        ];
        
        // ✅ Hanya kirim webhook jika laptop memiliki rack (QC PASSED)
        if ($laptop->rack_id) {
            WebhookHelper::sendWebhook(Auth::user()->company_id, $this->appName, 'delete', $payload);
        }
        
        $laptop->delete();
        return redirect()->route('used-laptop.index')->with('success', 'Laptop berhasil dihapus!');
    }

    public function mediaDestroy($id)
    {
        try {
            $media = UsedLaptopMedia::findOrFail($id);
            Storage::delete('public/' . $media->file_path);
            $media->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Media berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus media: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkSerialNumber(Request $request)
    {
        $serialNumber = $request->input('serial_number');
        $laptopId = $request->input('laptop_id'); // ID laptop saat edit
        
        $query = UsedLaptop::where('serial_number', $serialNumber)->byCompany(Auth::user()->company_id);
        
        // Exclude current laptop when editing
        if ($laptopId) {
            $query->where('id', '!=', $laptopId);
        }
        
        $exists = $query->exists();
        
        if ($exists) {
            $laptop = $query->first();
            return response()->json([
                'exists' => true,
                'message' => 'Serial number sudah terdaftar',
                'laptop' => [
                    'name' => $laptop->name,
                    'brand' => $laptop->brand,
                    'slug' => $laptop->slug,
                ]
            ]);
        }
        
        return response()->json([
            'exists' => false,
            'message' => 'Serial number tersedia'
        ]);
    }
}
