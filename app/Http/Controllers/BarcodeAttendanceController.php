<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarcodeAttendance;
use Illuminate\Support\Str;
use App\Events\NewBarcodeGenerated;

class BarcodeAttendanceController extends Controller
{

    public function generate(Request $request)
    {
        $companyId = auth()->user()->company_id;

        // Hapus QR lama yang belum dipakai (opsional, bisa di-cron juga)
        BarcodeAttendance::where('user_create_id', auth()->user()->id)
            ->where('is_used', false)
            ->delete();

        // Buat QR baru
        $barcode = BarcodeAttendance::create([
            'id' => Str::uuid(),
            'company_id' => $companyId,
            'user_create_id' => auth()->user()->id,
            'code' => Str::uuid(),
            'expires_at' => now()->addMinutes(5)
        ]);

        // Broadcast agar QR baru ditampilkan di layar
        broadcast(new NewBarcodeGenerated($barcode, auth()->user()->id))->toOthers();

        return response()->json([
            'message' => 'QR baru berhasil dibuat',
            'barcode' => $barcode
        ]);
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $barcode = BarcodeAttendance::where('user_create_id', auth()->user()->id)
            ->where('is_used', false)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();

        if (!$barcode) 
        {
            $barcode = BarcodeAttendance::create([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_create_id' => auth()->user()->id,
                'code' => Str::uuid(),
                'expires_at' => now()->addMinutes(5)
            ]);
        }

        return view('barcode.index', compact('barcode'));
    }
}
