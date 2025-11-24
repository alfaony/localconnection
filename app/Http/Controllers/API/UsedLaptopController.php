<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UsedLaptop;
use App\Models\MasterCheckItem;
use App\Models\UsedLaptopMedia;
use App\Models\UsedLaptopCheck;
use App\Models\UsedLaptopRepair;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UsedLaptopController extends Controller
{
    public function maskAsSold(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $this->validate($request, [
                'sold_price' => 'required|numeric',
            ]);
            
            $laptop = UsedLaptop::byCompany(Auth::user()->company_id)->where('is_sold', false)->where('id', $id)->first();
            if (!$laptop) 
            {
                DB::rollBack();
                return response()->json(['message' => 'Laptop tidak ditemukan'], 404);
            }

            $laptop->update([
                'is_sold' => true,
                'sold_price' => $request->input('sold_price'),
                'sold_at' => Carbon::now(),
            ]);
            
            DB::commit();
            return response()->json(['message' => 'Laptop berhasil ditandai sebagai terjual!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menandai laptop sebagai terjual: ' . $e->getMessage()], 422);
        }
    }
}
