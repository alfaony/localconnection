<?php

namespace App\Http\Controllers;

use App\Models\UsedLaptop;
use App\Models\MasterCheckItem;
use App\Models\UsedLaptopMedia;
use App\Models\UsedLaptopCheck;
use App\Models\UsedLaptopRepair;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UsedLaptopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
        $checkItems = MasterCheckItem::byCompany(Auth::user()->company_id)->get();
        return view('used_laptop.createOrEdit', compact('checkItems'));
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
                'name' => 'required|string|max:255',
                'serial_number' => 'required|string|max:255',
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
            ]);

            // Simpan data laptop
            $laptop = new UsedLaptop();
            $laptop->company_id = Auth::user()->company_id;
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
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('used-laptop', 'public');
                    UsedLaptopMedia::create([
                        'used_laptop_id' => $laptop->id,
                        'file_path' => $path,
                    ]);
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
            
            DB::commit();
            
            return redirect()->route('used-laptop.show', $laptop->slug)->with('success', 'Laptop berhasil ditambahkan!');
                
        } catch (\Exception $e) {
            dd($e);
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
        $laptop = UsedLaptop::where('slug', $slug)->byCompany(Auth::user()->company_id)->firstOrFail();
        return view('used_laptop.show', compact('laptop'));
    }

    public function showQr($slug)
    {
        $laptop = UsedLaptop::where('slug', $slug)->firstOrFail();
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
        $checkItems = MasterCheckItem::byCompany(Auth::user()->company_id)->get();
        return view('used_laptop.createOrEdit', compact('checkItems', 'laptop'));

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
        $url = route('used-laptop.show-qr', $laptop->slug);

        $qrPng = QrCode::format('png')->size(300)->generate($url);

        // Simpan ke disk
        $filename = 'qr_laptop_' . $laptop->slug.'_'.$laptop->serial_number.'.png';
        Storage::put('public/qrcodes/' . $filename, $qrPng);

        // Simpan path untuk view
        $laptop->update([
            'qr_code_path' => 'qrcodes/' . $filename,
        ]);

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
                'name' => 'required|string|max:255',
                'processor' => 'required|string|max:255',
                'ram' => 'required|string|max:255',
                'ssd' => 'required|string|max:255',
                'gpu' => 'nullable|string|max:255',
                'operating_system' => 'nullable|string|max:255',
                'purchase_price' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
                'photos' => 'nullable|array',
                'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'check_items' => 'required|array|min:1',
                'repairs' => 'nullable|array',
            ]);
            
            // Simpan atau update data laptop
            if ($laptop) {
                $laptop->update([
                    'name' => $validated['name'],
                    'processor' => $validated['processor'],
                    'ram' => $validated['ram'],
                    'ssd' => $validated['ssd'],
                    'gpu' => $validated['gpu'] ?? null,
                    'operating_system' => $validated['operating_system'] ?? null,
                    'purchase_price' => $validated['purchase_price'],
                    'notes' => $validated['notes'] ?? null,
                ]);
            } else {
                $laptop = UsedLaptop::create([
                    'name' => $validated['name'],
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
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('used-laptops', 'public');
                    UsedLaptopMedia::create([
                        'used_laptop_id' => $laptop->id,
                        'file_path' => $path,
                    ]);
                }
            }
            
            // Update checklist
            foreach ($request->input('check_items') as $checkItemId => $checkData) {
                $check = UsedLaptopCheck::updateOrCreate(
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
            
            DB::commit();
            
            return redirect()->route('used-laptop.index')
                ->with('success', $laptop ? 'Laptop berhasil diperbarui!' : 'Laptop berhasil ditambahkan!');
                
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan laptop: ' . $e->getMessage());
        }
    }

    public function destroy($slug)
    {
        $laptop = UsedLaptop::where('slug', $slug)->firstOrFail();
        $laptop->delete();
        return redirect()->route('used-laptop.index')->with('success', 'Laptop berhasil dihapus!');
    }

    public function mediaDestroy($id)
    {
        $media = UsedLaptopMedia::findOrFail($id);
        Storage::delete($media->file_path);
        $media->delete();
        return back()->with('success', 'Media berhasil dihapus!');
    }
}
