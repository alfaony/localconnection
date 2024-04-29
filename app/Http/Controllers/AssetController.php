<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;

use App\Models\Asset;
use App\Models\User;
use App\Models\AssetType;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $order = 'desc'; if($request->order == 'asc') { $order = 'asc'; }
        $assets = Asset::byCompany(Auth::user()->company_id)
        ->where('name','like', '%' . $request->get('asset') . '%')
        ->OrderBy('created_at',$order)->paginate(10);

        $assetTypes = AssetType::byCompany(Auth::user()->company_id)->get();
        return view('asset.index',compact('assets', 'assetTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'asset_type_id' => 'required|uuid|exists:asset_types,id',
        ]);

        $asset = new Asset();
        $asset->name = $validatedData['name'];
        $asset->asset_type_id = $validatedData['asset_type_id'];
        $asset->status = ParamSchema::STORAGE;  // Default status, adjust as needed
        $asset->user_id = auth()->user()->id; // Assuming the user is logged in
        $asset->save();

        return redirect()->route('asset.index')->with('store', true);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Asset  $asset
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $asset = Asset::where('slug',$slug)->firstOrFail();
        $users = User::byRole(RoleSchema::OB)->get();
        $assetAssigns = $asset->assetAssign()->orderBy('created_at', 'desc')->paginate(10);  // Paginate the asset assigns

        return view('asset.show',compact('asset' ,'users', 'assetAssigns'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Asset  $asset
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $assetTypes = AssetType::byCompany(Auth::user()->company_id)->get();
        $assets = Asset::byCompany(Auth::user()->company_id)->paginate(10);
        $asset = Asset::where('slug',$slug)->firstOrFail();

        return view('asset.index',compact('assets', 'assetTypes', 'asset'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Asset  $asset
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'asset_type_id' => 'required|uuid|exists:asset_types,id',
        ]);

        $asset = Asset::where('slug',$slug)->firstOrFail();
        $asset->name = $validatedData['name'];
        $asset->asset_type_id = $validatedData['asset_type_id'];
        $asset->user_id = auth()->user()->id; // Assuming the user is logged in
        $asset->save();

        return redirect()->route('asset.index')->with('update', true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Asset  $asset
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $asset = Asset::where('slug',$slug)->firstOrFail();
        $asset->delete();
        return redirect()->route('asset.index')->with('delete', true);
    }
}
