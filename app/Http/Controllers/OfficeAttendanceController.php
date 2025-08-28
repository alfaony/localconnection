<?php

namespace App\Http\Controllers;

use App\Models\BarcodeAttendance;
use App\Models\OfficeAttendance;
use App\Jobs\ProcessScanAttendanceJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfficeAttendanceController extends Controller
{
    public function index()
    {
        return view('office_attendance.index');
    }
    
    public function scan($code)
    {
        $barcode = BarcodeAttendance::where('code', $code)->firstOrFail();

        ProcessScanAttendanceJob::dispatch(
            $barcode->id,
            auth()->id(),
            auth()->user()->company_id
        );

        return response()->json([
            'message' => 'Scan berhasil, sedang diverifikasi...',
            'status' => 'processing'
        ]);
    }

    public function complete(Request $request)
    {
        $request->validate([
            'location_lat' => 'required',
            'location_long' => 'required',
            'selfie' => 'required|image|max:2048',
        ]);

        $latestAttendance = OfficeAttendance::where('user_id', auth()->id())->latest('time')->firstOrFail();

        $path = $request->file('selfie')->store('selfie-attendance', 'public');

        $latestAttendance->update([
            'location_lat' => $request->location_lat,
            'location_long' => $request->location_long,
            'selfie_path' => $path,
        ]);

        return response()->json([
            'message' => 'Absensi lengkap dengan foto dan lokasi.',
            'status' => 'completed'
        ]);
    }
}
