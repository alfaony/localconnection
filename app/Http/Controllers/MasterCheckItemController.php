<?php

namespace App\Http\Controllers;

use App\Models\MasterCheckItem;
use App\Models\ItemCategory;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterCheckItemController extends Controller
{
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $search = $request->input('search');
        $masterType = config('custom.master_type_check');
        $itemCategories = ItemCategory::where('company_id', auth()->user()->company_id)->get();

        
        $checkItems = MasterCheckItem::byCompany($companyId)
            ->when($request->type, function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%'.$search.'%');
            })
            ->paginate(10);
            
        return view('master-check-items.index', compact('checkItems', 'search', 'masterType', 'itemCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:master_check_items,name,NULL,id,company_id,'.Auth::user()->company_id,
            'type' => 'required|string|max:255|in:'.implode(',', array_keys(config('custom.master_type_check'))),
            'item_category_id' => 'nullable',
            'new_category_name' => 'required_if:item_category_id,__create_new__|nullable|string|max:255',
        ]);
        
        DB::beginTransaction();
        try {
            // Cek apakah harus membuat kategori baru
            if ($request->item_category_id === '__create_new__') {
                // Validasi wajib isi nama baru
                if (!$request->filled('new_category_name')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nama kategori baru wajib diisi.',
                    ], 422);
                }
    
                $newCategory = ItemCategory::create([
                    'name' => $request->new_category_name,
                    'type' => $request->type,
                    'company_id' => auth()->user()->company_id,
                ]);
    
                $categoryId = $newCategory->id;
            } else {
                $categoryId = $request->item_category_id;
            }
    
            // Simpan item
            $item = MasterCheckItem::create([
                'name' => $request->name,
                'type' => $request->type,
                'item_category_id' => $categoryId,
                'company_id' => auth()->user()->company_id,
            ]);
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Item pemeriksaan berhasil ditambahkan!'
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            Log::error($th->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Item pemeriksaan gagal ditambahkan!'
            ], 500);
        }
    }

    public function update(Request $request, MasterCheckItem $masterCheckItem)
    {
         $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'item_category_id' => 'nullable',
            'new_category_name' => 'required_if:item_category_id,__create_new__|nullable|string|max:255',
        ]);

        // Handle category creation if needed
        if ($request->item_category_id === '__create_new__') {
            $category = ItemCategory::create([
                'company_id' => auth()->user()->company_id,
                'name' => $request->new_category_name,
                'type' => $request->type,
            ]);
            $categoryId = $category->id;
        } else {
            $categoryId = $request->item_category_id;
        }

        $masterCheckItem->update([
            'name' => $request->name,
            'type' => $request->type,
            'item_category_id' => $categoryId,
        ]);

        return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui.']);
    }

    public function destroy(MasterCheckItem $masterCheckItem)
    {
        $masterCheckItem->delete();
        return redirect()->route('master-check-item.index')->with('delete', true);
    }
}