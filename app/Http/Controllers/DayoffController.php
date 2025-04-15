<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Models\Dayoff;
use App\Models\DayoffType;
use App\Models\DayoffQuota;
use App\Models\EmployeeChecking;

use Carbon\Carbon;

use App\Helpers\Access;

class DayoffController extends Controller
{
    public function index()
    {
        $cutis = Dayoff::byCompany(Auth::user()->company_id)
            ->latest()
            ->paginate(10);

        return view('dayoff.index', compact('cutis'));
    }

    public function create()
    {
        $types = DayoffType::all();
        $mode = 'create';
        $cuti = null;
        return view('dayoff.createOrEdit', compact('types', 'mode', 'cuti'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'dayoff_type_id' => 'required|exists:dayoff_types,id',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'reason' => 'nullable|string',
            'file' => 'nullable|file|max:2048',
        ]);

        $user = auth()->user();
        if (!$user->dayoff_active) 
        {
            return redirect()->back()->withErrors(['msg' => 'Mohon maaf, Anda belum tersedia. Mohon hubungi admin untuk mengaktifkan akun Anda.']);
        }
        $type = DayoffType::where('id', $request->dayoff_type_id)->firstOrFail();
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
        // if ($type->is_limited) {
        //     $quota = DayoffQuota::firstOrNew([
        //         'user_id' => $user->id,
        //         'dayoff_type_id' => $type->id,
        //         'year' => now()->year
        //     ]);

        //     $available = ($quota->quota ?? $type->default_quota) - ($quota->used ?? 0);
        //     if ($available < $daysRequested) {
        //         return back()->withErrors(['msg' => 'Kuota cuti tidak mencukupi.'])->withInput();
        //     }

        //     $quota->quota = $quota->quota ?? $type->default_quota;
        //     $quota->used += $daysRequested;
        //     $quota->save();
        // }

        $filePath = null;
        if ($request->hasFile('file') && $type->permission_required) 
        {
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

        return redirect()->route('dayoff.index')->with('store', true);
    }

    public function edit($id)
    {
        $cuti = Dayoff::where('user_id', auth()->id())->findOrFail($id);
        $types = DayoffType::all();

        if (!$cuti->permissionChanged) {
            return back()->withErrors(['msg' => 'Anda tidak diizinkan untuk mengedit cuti ini.']);
        }

        return view('dayoff.createOrEdit', [
            'types' => $types,
            'cuti' => $cuti,
            'mode' => 'edit',
        ]);
    }

    public function update($id, Request $request)
    {
        $cuti = Dayoff::where('user_id', auth()->id())->findOrFail($id);

        if (!$cuti->permissionChanged) 
        {
            return back()->withErrors(['msg' => 'Anda tidak diizinkan untuk mengedit cuti ini.']);
        }

        $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'reason' => 'nullable|string',
            'file' => 'nullable|file|max:2048',
        ]);

        $filePath = $cuti->file ?? NULL;
        if ($request->hasFile('file') && $type->permission_required) 
        {
            $filePath = $request->file('file')->store('dayoff-files');
        }
        if ($cuti->rejected_at) 
        {
            $cuti->approval_finance_user_id = null;
            $cuti->approved_finance_at = null;
            $cuti->approval_hr_user_id = null;
            $cuti->approved_hr_at = null;
            $cuti->rejected_at = null;
            $cuti->reason_reject = null;
            $cuti->save();
        }

        $cuti->update([
            'date_start' => $request->date_start,
            'date_end' => $request->date_end,
            'reason' => $request->reason,
            'file' => $filePath,
        ]);

        return redirect()->route('dayoff.index')->with('update', true);
    }

    public function destroy($id)
    {
        $cuti = Dayoff::where('user_id', auth()->id())->findOrFail($id);

        if (!$cuti->permissionChanged) {
            return back()->withErrors(['msg' => 'Anda tidak diizinkan untuk menghapus cuti ini.']);
        }

        // Kembalikan kuota jika cuti terbatas
        // $type = $cuti->type;
        // if ($type->is_limited) 
        // {
        //     $days = Carbon::parse($cuti->date_start)->diffInDays(Carbon::parse($cuti->date_end)) + 1;
        //     $quota = DayoffQuota::where('user_id', auth()->id())
        //         ->where('dayoff_type_id', $type->id)
        //         ->where('year', $cuti->date_start->format('Y'))
        //         ->first();

        //     if ($quota) {
        //         $quota->used = max(0, $quota->used - $days);
        //         $quota->save();
        //     }
        // }

        // Hapus file
        if ($cuti->file && Storage::exists($cuti->file)) 
        {
            Storage::delete($cuti->file);
        }

        $cuti->delete();

        return redirect()->route('dayoff.index')->with('success', 'Cuti berhasil dihapus.');
    }

    public function checkInfo(Request $request)
    {
        $request->validate([
            'dayoff_type_id' => 'required|exists:dayoff_types,id',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
        ]);

        $user = auth()->user();
        $type = DayoffType::findOrFail($request->dayoff_type_id);

        $dateStart = Carbon::parse($request->date_start);
        $dateEnd = Carbon::parse($request->date_end);
        $durasi = $dateStart->diffInDays($dateEnd) + 1;

        $quota = $type->is_limited
            ? DayoffQuota::where('user_id', $user->id)
                ->where('dayoff_type_id', $type->id)
                ->first()
            : null;

        $sisa = !$type->is_limited ? 'Unlimited' : ($quota->quota ?? $type->default_quota) - ($quota->used ?? 0);
        $sisaAfter = $sisa === 'Unlimited' ? 'Unlimited' : $sisa - $durasi;

        $excludeId = $request->query('exclude_id');

        $hasOverlap = Dayoff::where('user_id', $user->id)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($query) use ($dateStart, $dateEnd) {
                $query->whereBetween('date_start', [$dateStart, $dateEnd])
                    ->orWhereBetween('date_end', [$dateStart, $dateEnd])
                    ->orWhere(function ($q) use ($dateStart, $dateEnd) {
                        $q->where('date_start', '<=', $dateStart)
                        ->where('date_end', '>=', $dateEnd);
                    });
            })
            ->exists();

        return response()->json([
            'quota' => $sisa,
            'durasi' => $durasi,
            'remaining' => $sisaAfter,
            'overlaps' => $hasOverlap,
            'quota_insufficient' => ($sisa !== 'Unlimited' && $sisaAfter < 0),
        ]);
    }

    public function financeApprovement(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'action_type' => 'required|in:reject,approve',
            ]);

            $actionType = $request->input('action_type');
            $ids = json_decode($request->input('cuti_ids'), true) ?? [];
            
            if (empty($ids)) {
                return back()->withErrors(['msg' => 'Tidak ada cuti yang dipilih.']);
            }
    
            $cutis = Dayoff::with('type')
                ->whereIn('id', $ids)
                ->whereNull('approval_finance_user_id')
                ->whereNull('approved_finance_at')
                ->get();
    
            if ($cutis->isEmpty()) 
            {
                DB::rollback();
                return back()->withErrors(['msg' => 'Semua cuti yang dipilih sudah diproses atau belum disetujui HR.']);
            }
    
            foreach ($cutis as $cuti) 
            {
                if ($actionType === 'approve')
                {
                    $cuti->update([
                        'approval_finance_user_id' => auth()->id(),
                        'approved_finance_at' => now(),
                    ]);
                }

                if ($actionType === 'reject')
                {
                    $cuti->update([
                        'rejected_at' => now(),
                    ]);
                }
                

                if(!$this->approve($cuti) && $actionType === 'approve')
                {
                    DB::rollback();
                    return back()->withErrors(['msg' => 'Terjadi Kesalahan Saat Menyetujui']);
                }
                
            }
            
            DB::commit();
            return back()->with('success', 'Beberapa cuti berhasil disetujui oleh Finance.');
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error($th->getMessage());

            DB::rollBack();
            return back()->withErrors(['msg' => 'Terjadi kesalahan saat memproses persetujuan. Silakan coba lagi.']);
        }
    }

    public function hrApprovement(Request $request)
    {
        DB::beginTransaction();
        try {
            $ids = json_decode($request->input('cuti_ids'), true) ?? [];
    
            if (empty($ids)) {
                return back()->withErrors(['msg' => 'Tidak ada cuti yang dipilih.']);
            }
    
            $cutis = Dayoff::with('type')
                ->whereIn('id', $ids)
                ->whereNull('approval_hr_user_id')
                ->whereNull('approved_hr_at')
                ->whereNull('rejected_at')
                ->get();
    
            if ($cutis->isEmpty()) 
            {
                DB::rollback();
                return back()->withErrors(['msg' => 'Semua cuti yang dipilih sudah diproses atau belum disetujui FINANCE.']);
            }
    
            foreach ($cutis as $cuti) 
            {
                $cuti->update([
                    'approval_hr_user_id' => auth()->id(),
                    'approved_hr_at' => now(),
                ]);
    
                if(!$this->approve($cuti))
                {
                    DB::rollback();
                    return back()->withErrors(['msg' => 'Terjadi Kesalahan Saat Menyetujui']);
                }
            }
            
            DB::commit();
            return back()->with('success', 'Beberapa cuti berhasil disetujui oleh HR.');
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error($th->getMessage());

            DB::rollBack();
            return back()->withErrors(['msg' => 'Terjadi kesalahan saat memproses persetujuan. Silakan coba lagi.']);
        }
    }

    public function approve($dayoff)
    {
        if ($dayoff->type->is_limited && $dayoff->approvalFinance && $dayoff->approvalHR ) 
        {
            $daysRequested = Carbon::parse($dayoff->date_start)->diffInDays(Carbon::parse($dayoff->date_end)) + 1;
            
            $quota = DayoffQuota::firstOrNew([
                'user_id' => $dayoff->user->id,
                'dayoff_type_id' => $dayoff->type->id,
            ]);
            
            $available = ($quota->quota ?? $dayoff->type->default_quota) - ($quota->used ?? 0);
            if ($available < $daysRequested) 
            {
                return false;
            }

            $quota->quota = $quota->quota ?? $dayoff->type->default_quota;
            $quota->used += $daysRequested;
            $quota->save();
        }

        if($dayoff->type->permission_required && $dayoff->approvalFinance && $dayoff->approvalHR)
        {
            $existingCheckinDates = EmployeeChecking::where('user_id', $dayoff->user_id)
            ->whereDate('created_at', '>=', $dayoff->date_start)
            ->whereDate('created_at', '<=', $dayoff->date_end)
            ->get()
            ->map(function ($checkin) {
                return $checkin->created_at;
            })
            ->unique()
            ->values();

            foreach ($existingCheckinDates as $tanggal) 
            {
                $firstDivision = $this->findFirstDivision($dayoff->user);

                EmployeeChecking::create([
                    'user_id' => $dayoff->user_id,
                    'division_id' => $firstDivision->id,
                    'scheduled_time' => $tanggal,
                    'scheduled_timeout' => $tanggal,
                    'is_dayoff' => false,
                    'is_active' => false,
                    'is_completed' => false,
                    'is_permission' => true,
                    'created_at' => $tanggal, // inject tanggal checkin asli
                    'updated_at' => now(),
                ]);
            }

            EmployeeChecking::where('user_id', $dayoff->user_id)
            ->whereDate('created_at', '>=', $dayoff->date_start)
            ->whereDate('created_at', '<=', $dayoff->date_end)
            ->where('is_active',true)
            ->delete();
        }

        return true;
    }

    public function infoApprovementHr()
    {
        $total = Dayoff::byCompany(Auth::user()->company_id)->whereNull('approved_hr_at')
            ->whereNull('rejected_at')
            ->count();

        return response()->json(['total' => $total]);
    }

    public function infoApprovementFinance()
    {
        $total = Dayoff::byCompany(Auth::user()->company_id)->whereNull('approved_finance_at')
            ->whereNotNull('approved_hr_at')
            ->whereNull('rejected_at')
            ->count();

        return response()->json(['total' => $total]);
    }

    private function findFirstDivision($user)
    {
        foreach ($user->divisions as $division) 
        {
            if($division->manual_checkin)
            {
                return $division;
            }
        }


        return $user->divisions->first();

    } 

}