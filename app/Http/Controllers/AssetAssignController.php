<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Schemas\ParamSchema;

use App\Models\AssetAssign;
use App\Models\Asset;

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
        $validatedData = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'assigned_to_user_id' => 'required|exists:users,id',
            'picked_up_date' => 'required|date'
        ]);
    
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
        $validatedData = $request->validate([
            'returned_date' => 'required|date'
        ]);
        
        // Membuat instance baru dari AssetAssign
        $assetAssign = AssetAssign::where('slug',$slug)->first();
        $assetAssign->returned_date = $validatedData['returned_date'];
    
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
        $assetAssign = AssetAssign::where('slug', $slug)->firstOrFail();

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
