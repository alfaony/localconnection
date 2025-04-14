<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Models\Dayoff;
use App\Models\DayoffType;
use App\Models\DayoffQuota;

use Carbon\Carbon;

use App\Helpers\Access;

class DayoffController extends Controller
{
    public function index()
    {
        $cutis = Dayoff::with('type')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('dayoff.index', compact('cutis'));
    }

    public function create()
    {
        $types = DayoffType::all();
        return view('dayoff.createOrEdit', [
            'types' => $types,
            'cuti' => null,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'dayoff_type_code' => 'required|exists:dayoff_types,code',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'reason' => 'nullable|string',
            'file' => 'nullable|file|max:2048',
        ]);

        $user = auth()->user();
        $type = DayoffType::where('code', $request->dayoff_type_code)->firstOrFail();
        $daysRequested = Carbon::parse($request->date_start)->diffInDays(Carbon::parse($request->date_end)) + 1;

        // Cek tumpang tindih
        $hasOverlap = Dayoff::where('user_id', $user->id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('date_start', [$request->date_start, $request->date_end])
                    ->orWhereBetween('date_end', [$request->date_start, $request->date_end])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('date_start', '<=', $request->date_start)
                            ->where('date_end', '>=', $request->date_end);
                    });
            })
            ->exists();

        if ($hasOverlap) {
            return back()->withErrors(['msg' => 'Tanggal tumpang tindih dengan cuti lain.'])->withInput();
        }

        // Validasi kuota jika cuti terbatas
        if ($type->is_limited) {
            $quota = DayoffQuota::firstOrNew([
                'user_id' => $user->id,
                'dayoff_type_id' => $type->id,
                'year' => now()->year
            ]);

            $available = ($quota->quota ?? $type->default_quota) - ($quota->used ?? 0);
            if ($available < $daysRequested) {
                return back()->withErrors(['msg' => 'Kuota cuti tidak mencukupi.'])->withInput();
            }

            $quota->quota = $quota->quota ?? $type->default_quota;
            $quota->used += $daysRequested;
            $quota->save();
        }

        $filePath = null;
        if ($request->hasFile('file') && $type->code === 'sakit') {
            $filePath = $request->file('file')->store('dayoff-files');
        }

        Dayoff::create([
            'user_id' => $user->id,
            'dayoff_type_id' => $type->id,
            'date_start' => $request->date_start,
            'date_end' => $request->date_end,
            'reason' => $request->reason,
            'file' => $filePath,
        ]);

        return redirect()->route('dayoff.index')->with('success', 'Cuti berhasil diajukan.');
    }

    public function edit($id)
{
    $cuti = Dayoff::where('user_id', auth()->id())->findOrFail($id);
    $types = DayoffType::all();

    return view('dayoff.createOrEdit', [
        'types' => $types,
        'cuti' => $cuti,
        'mode' => 'edit',
    ]);
}

    public function update($id, Request $request)
    {
        $cuti = Dayoff::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'reason' => 'nullable|string',
        ]);

        $cuti->update([
            'reason' => $request->reason,
        ]);

        return redirect()->route('dayoff.index')->with('success', 'Cuti berhasil diupdate.');
    }

    public function destroy($id)
    {
        $cuti = Dayoff::where('user_id', auth()->id())->findOrFail($id);

        // Kembalikan kuota jika cuti terbatas
        $type = $cuti->type;
        if ($type->is_limited) {
            $days = Carbon::parse($cuti->date_start)->diffInDays(Carbon::parse($cuti->date_end)) + 1;
            $quota = DayoffQuota::where('user_id', auth()->id())
                ->where('dayoff_type_id', $type->id)
                ->where('year', $cuti->date_start->format('Y'))
                ->first();

            if ($quota) {
                $quota->used = max(0, $quota->used - $days);
                $quota->save();
            }
        }

        // Hapus file
        if ($cuti->file && \Storage::exists($cuti->file)) 
        {
            Storage::delete($cuti->file);
        }

        $cuti->delete();

        return redirect()->route('dayoff.index')->with('success', 'Cuti berhasil dihapus.');
    }

    public function checkQuota(Request $request)
    {
        $user = auth()->user();
        $code = $request->query('type');
        $type = DayoffType::where('code', $code)->first();

        if (!$type) return response()->json(['quota' => null]);

        if (!$type->is_limited) {
            return response()->json(['quota' => 'Unlimited']);
        }

        $quota = DayoffQuota::where('user_id', $user->id)
            ->where('dayoff_type_id', $type->id)
            ->where('year', now()->year)
            ->first();

        $available = ($quota->quota ?? $type->default_quota) - ($quota->used ?? 0);
        return response()->json(['quota' => $available]);
    }
}