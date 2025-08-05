<?php

namespace App\Http\Controllers;

use App\Models\UsedItem;
use App\Models\MasterCheckItem;
use App\Models\UsedItemMedia;
use App\Models\UsedItemCheck;
use App\Models\UsedItemRepair;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UsedItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('used_item.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkItems = MasterCheckItem::where('type', 'item_type')->byCompany(Auth::user()->company_id)->get();
        return view('used_item.createOrEdit', compact('checkItems'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:20480',
            'check_items' => 'nullable|array',
            'repairs' => 'nullable|array',
        ]);

        // DB::beginTransaction();
        try 
        {
            return $this->saveLaptop($request);

            // return redirect()->route('used-item.index')->with('success', 'Data berhasil disimpan');
        } catch (\Throwable $th) {
            //throw $th;
            // DB::rollBack();
            return redirect()->route('used-item.index')->with('error', 'Data gagal disimpan'. $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UsedItem  $usedLaptop
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $usedItem = UsedItem::where('slug', $slug)->byCompany(Auth::user()->company_id)->firstOrFail();
        return view('used_item.show', compact('usedItem'));
    }

    public function showQr($slug)
    {
        $usedItem = UsedItem::where('slug', $slug)->firstOrFail();
        return view('used_item.showQr', compact('usedItem'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UsedItem  $usedLaptop
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $usedItem = UsedItem::where('slug', $slug)->byCompany(Auth::user()->company_id)->firstOrFail();
        $checkItems = MasterCheckItem::where('type','item_type')->byCompany(Auth::user()->company_id)->get();
        return view('used_item.createOrEdit', compact('checkItems', 'usedItem'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UsedItem  $usedLaptop
     * @return \Illuminate\Http\Response
     */
     public function update(Request $request, $slug)
    {
        $item = UsedItem::where('slug', $slug)->byCompany(Auth::user()->company_id)->firstOrFail();
        $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'check_items' => 'required|array|min:1',
            'repairs' => 'nullable|array',
        ]);

        if(!$item) 
        {
            return back()->with('error', 'item tidak ditemukan');
        }
        return $this->saveLaptop($request, $item);
    }

    public function maskAsSold(Request $request, $slug)
    {
        DB::beginTransaction();
        try {
            $item = UsedItem::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            $item->update([
                'is_sold' => true,
                'sold_price' => $request->sold_price,
                'sold_at' => $request->sold_at,
            ]);
            
            DB::commit();
            return redirect()->route('used-item.show', $item->slug)->with('success', 'item berhasil ditandai sebagai terjual!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menandai item sebagai terjual: ' . $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UsedItem  $usedLaptop
     * @return \Illuminate\Http\Response
     */
   
    private function saveLaptop(Request $request, UsedItem $item = null)
    {
        DB::beginTransaction();
         $validated = $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'check_items' => 'required|array|min:1',
            'repairs' => 'nullable|array',
        ]);
        $new = false;
        
        try {
            
            // Simpan atau update data item
            if ($item) {
                $item->update([
                    'name' => $validated['name'],
                    'serial_number' => $validated['serial_number'],
                    'purchase_price' => $validated['purchase_price'],
                    'notes' => $validated['notes'] ?? null,
                ]);
            } else {
                $new = true;
                $item = UsedItem::create([
                    'company_id' => Auth::user()->company_id,
                    'user_id' => Auth::user()->id,
                    'name' => $validated['name'],
                    'serial_number' => $validated['serial_number'],
                    'purchase_price' => $validated['purchase_price'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                $url = route('used-item.show-qr', $item->slug);

                $qrPng = QrCode::format('png')->size(300)->generate($url);

                // Simpan ke disk
                $filename = 'qr_item_' . $item->slug.'.png';
                Storage::put('public/qrcodes/' . $filename, $qrPng);

                // Simpan path untuk view
                $item->update([
                    'qr_code_path' => 'qrcodes/' . $filename,
                ]);
            }
            
            // Simpan foto baru
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('used-items', 'public');
                    UsedItemMedia::create([
                        'used_item_id' => $item->id,
                        'file_path' => $path,
                    ]);
                }
            }
            
            // Update checklist
            foreach ($request->input('check_items') as $checkItemId => $checkData) {
                $check = UsedItemCheck::updateOrCreate(
                    [
                        'used_item_id' => $item->id,
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
            $existingRepairIds = $item->repairs->pluck('id')->toArray();
            $submittedRepairIds = [];
            
            // Update kerusakan
            if ($request->has('repairs')) {
                foreach ($request->input('repairs') as $index => $repairData) {
                    if (!empty($repairData['description'])) {
                        $repair = UsedItemRepair::updateOrCreate(
                            ['id' => $repairData['id'] ?? null],
                            [
                                'used_item_id' => $item->id,
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
                UsedItemRepair::destroy($repairsToDelete);
            }
            
            DB::commit();
            
            return redirect()->route('used-item.show', $item->slug)
                ->with('success', !$new ? 'item berhasil diperbarui!' : 'item berhasil ditambahkan!');
                
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            throw $e;
            return back()->withInput()->with('error', 'Gagal menyimpan item: ' . $e->getMessage());
        }
    }

    public function destroy($slug)
    {
        $item = UsedItem::where('slug', $slug)->firstOrFail();
        $item->delete();
        return redirect()->route('used-item.index')->with('success', 'item berhasil dihapus!');
    }

    public function mediaDestroy($id)
    {
        $media = UsedItemMedia::findOrFail($id);
        Storage::delete($media->file_path);
        $media->delete();
        return back()->with('success', 'Media berhasil dihapus!');
    }
}

