<?php

namespace App\Http\Controllers;

use App\Models\MasterCheckItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterCheckItemController extends Controller
{
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $search = $request->input('search');
        $masterType = config('custom.master_type_check');
        
        $checkItems = MasterCheckItem::byCompany($companyId)
            ->when($request->type, function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%'.$search.'%');
            })
            ->paginate(10);
            
        return view('master-check-items.index', compact('checkItems', 'search', 'masterType'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:master_check_items,name,NULL,id,company_id,'.Auth::user()->company_id,
            'type' => 'required|string|max:255|in:'.implode(',', array_keys(config('custom.master_type_check')))
        ]);
        
        MasterCheckItem::create([
            'name' => $request->name,
            'type' => $request->type,
            'company_id' => Auth::user()->company_id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Item pemeriksaan berhasil ditambahkan!'
        ]);
    }

    public function update(Request $request, MasterCheckItem $masterCheckItem)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:master_check_items,name,'.$masterCheckItem->id.',id,company_id,'.Auth::user()->company_id,
             'type' => 'required|string|max:255|in:'.implode(',', array_keys(config('custom.master_type_check')))
        ]);

        $masterCheckItem->update([
            'name' => $request->name,
            'type' => $request->type
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Item pemeriksaan berhasil diperbarui!'
        ]);
    }

    public function destroy(MasterCheckItem $masterCheckItem)
    {
        $masterCheckItem->delete();
        return redirect()->route('master-check-item.index')->with('delete', true);
    }
}