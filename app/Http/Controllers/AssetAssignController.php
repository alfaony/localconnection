<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Schemas\ParamSchema;

use App\Models\AssetAssign;
use App\Models\Asset;

use Carbon\Carbon;

class AssetAssignController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $minDate = AssetAssign::byCompany(Auth::user()->company_id)
                          ->where('asset_id', $request->asset_id)
                          ->whereNotNull('returned_date')
                          ->orderBy('created_at', 'desc')
                          ->first();

        $rules = [
            'asset_id' => 'required|exists:assets,id',
            'assigned_to_user_id' => 'required|exists:users,id',
            'picked_up_date' => 'required|date',
        ];

        // Menambahkan validasi untuk 'picked_up_date' jika 'minDate' ada
        if ($minDate) 
        {
            $rules['picked_up_date'] .= '|after_or_equal:' . $minDate->returned_date;
        }

        // Pesan kesalahan kustom dalam bahasa Indonesia
        $messages = 
        [
            'asset_id.required' => 'ID aset diperlukan.',
            'asset_id.exists' => 'Aset yang dipilih tidak valid.',
            'assigned_to_user_id.required' => 'ID pengguna yang ditugaskan diperlukan.',
            'assigned_to_user_id.exists' => 'Pengguna yang dipilih tidak valid.',
            'picked_up_date.required' => 'Tanggal pengambilan diperlukan.',
            'picked_up_date.date' => 'Tanggal pengambilan harus dalam format yang benar.',
            'picked_up_date.after_or_equal' => 'Tanggal pengambilan harus pada atau setelah tanggal ' . ($minDate ? Carbon::parse($minDate->returned_date)->format('d-m-Y') : 'pengembalian terakhir.'),
        ];

        // Melakukan validasi dengan pesan kustom
        $validatedData = $request->validate($rules, $messages);
    
        // Membuat instance baru dari AssetAssign
        $assetAssign = new AssetAssign();
        $assetAssign->asset_id = $validatedData['asset_id'];
        $assetAssign->assigned_to_user_id = $validatedData['assigned_to_user_id'];
        $assetAssign->picked_up_date = $validatedData['picked_up_date'];
    
        // Simpan ke database
        $assetAssign->save();

        $asset = $assetAssign->asset;  // Get the related asset
        $asset->status = ParamSchema::PIC;  // Update status
        $asset->save();

        return redirect()->route('asset.show',$assetAssign->asset->slug)->with('store', true);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AssetAssign  $assetAssign
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $assetAssign = AssetAssign::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();

        $messages = [
            'returned_date.required' => 'Tanggal pengembalian diperlukan.',
            'returned_date.date' => 'Tanggal pengembalian harus dalam format tanggal yang valid.',
            'returned_date.after_or_equal' => 'Tanggal pengembalian harus sama dengan atau setelah tanggal pinjam '.Carbon::parse($assetAssign->picked_up_date)->format('d-m-Y').'.',
        ];
    
        // Melakukan validasi dengan pesan kustom
        $validatedData = $request->validate([
            'returned_date' => 'required|date|after_or_equal:'.$assetAssign->picked_up_date,
        ], $messages);
        
        // Membuat instance baru dari AssetAssign
        $assetAssign->returned_date = $validatedData['returned_date'];
        $assetAssign->received_to_user_id = Auth::user()->id;
    
        // Simpan ke database
        $assetAssign->save();

        $asset = $assetAssign->asset;  // Get the related asset
        $asset->status = ParamSchema::STORAGE;  // Update status
        $asset->save(); 

        return redirect()->route('asset.show',$assetAssign->asset->slug)->with('update', true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AssetAssign  $assetAssign
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $assetAssign = AssetAssign::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();

        if ($assetAssign) 
        {
            $asset = $assetAssign->asset;
            $asset->status = ParamSchema::STORAGE;  // Update status
            $asset->save();  // Save the updated asset
            
            $assetAssign->delete();
            
            // Redirect with a success message
            return redirect()->route('asset.show',$asset->slug)->with('delete', true);
        } else {
            // Redirect with an error message if AssetAssign not found
            return redirect()->back()->with('delete', false);
        }

    }
}
