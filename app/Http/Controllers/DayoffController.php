<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\Dayoff;
use App\Models\DayoffType;
use App\Models\DayoffQuota;
use App\Models\EmployeeChecking;

use App\Jobs\ExportDayoffJob;

use Carbon\Carbon;

use App\Helpers\Access;

class DayoffController extends Controller
{
    public function index(Request $request)
    {
        $users = User::byCompany(Auth::user()->company_id)->orderBy('name')->get();
        $types = DayoffType::all();
        $filePath = 'exports/' . session('last_export_filename');
        $fileExists = $filePath && Storage::exists($filePath) ?? false;

        $query = Dayoff::with(['user', 'type']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('type_id')) {
            $query->where('dayoff_type_id', $request->type_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $query->where(function ($q) use ($startDate, $endDate) 
            {
                $q->whereBetween('date_start', [$startDate, $endDate])
                ->orWhereBetween('date_end', [$startDate, $endDate]);
            });
        }

        $cutis = $query->byCompany(Auth::user()->company_id)
            ->latest()
            ->paginate(10);

        return view('dayoff.index', compact('cutis','users','types','fileExists'));
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
            $filePath = $request->file('file')->store('public/dayoff-files');
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

    public function show($id)
    {
        $dayoff = Dayoff::byCompany(Auth::user()->company_id)->findOrFail($id);

        return view('dayoff.show', [
            'dayoff' => $dayoff,
        ]);
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
            $filePath = $request->file('file')->store('public/dayoff-files');
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
        $excludeId = $request->query('exclude_id');

        $dateStart = Carbon::parse($request->date_start);
        $dateEnd = Carbon::parse($request->date_end);
        $durasi = $dateStart->diffInDays($dateEnd) + 1;

        $quota = $type->is_limited
        ? DayoffQuota::where('user_id', $user->id)
            ->where('dayoff_type_id', $type->id)
            ->first()
        : null;

        $pendingDuration = Dayoff::where('user_id', $user->id)
            ->where('dayoff_type_id', $type->id)
            ->whereNull('rejected_at')
            ->where(function ($q) {
                $q->whereNull('approved_hr_at')->orWhereNull('approved_finance_at');
            })
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->get()
            ->sum(fn($cuti) => Carbon::parse($cuti->date_start)->diffInDays(Carbon::parse($cuti->date_end)) + 1);
        
        $used = ($quota->used ?? 0) + $pendingDuration;
        
        $sisa = !$type->is_limited
            ? 'Unlimited'
            : max(0, ($quota->quota ?? $type->default_quota) - $used);

        $sisaAfter = $sisa === 'Unlimited' ? 'Unlimited' : $sisa - $durasi;

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
            ->whereDate('scheduled_time', '>=', $dayoff->date_start)
            ->whereDate('scheduled_time', '<=', $dayoff->date_end)
            ->get()
            ->map(function ($checkin) 
            {
                return Carbon::parse($checkin->scheduled_time)->format('Y-m-d');
            })
            ->unique()
            ->values();

            foreach ($existingCheckinDates as $tanggal) 
            {
                $firstDivision = $this->findFirstDivision($dayoff->user);

                $employeeChecking = new EmployeeChecking();
                $employeeChecking->user_id = $dayoff->user_id;
                $employeeChecking->division_id = $firstDivision->id;
                $employeeChecking->scheduled_time = $tanggal;
                $employeeChecking->scheduled_timeout = $tanggal;
                $employeeChecking->is_dayoff = false;
                $employeeChecking->is_active = false;
                $employeeChecking->is_completed = false;
                $employeeChecking->is_permission = true;
                $employeeChecking->created_at = Carbon::parse($tanggal);
                $employeeChecking->updated_at = Carbon::parse($tanggal);
                $employeeChecking->save();
            }

            EmployeeChecking::where('user_id', $dayoff->user_id)
            ->whereDate('created_at', '>=', $dayoff->date_start)
            ->whereDate('created_at', '<=', $dayoff->date_end)
            ->where('is_permission', false)
            ->delete();
        }

        return true;
    }

    public function infoApprovementHr()
    {
        $total = Dayoff::byCompany(Auth::user()->company_id)
            ->whereNull('approved_hr_at')
            ->whereNull('approval_hr_user_id')
            ->whereNull('rejected_at')
            ->count();

        return response()->json(['total' => $total]);
    }

    public function infoApprovementFinance()
    {
        $total = Dayoff::byCompany(Auth::user()->company_id)->whereNull('approved_finance_at')
            ->whereNull('approval_finance_user_id')
            ->whereNull('rejected_at')
            ->count();

        return response()->json(['total' => $total]);
    }

    public function export(Request $request, $format)
    {
        $filename = 'laporan_cuti_' . time() . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $exportFormat = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        
        ExportDayoffJob::dispatch($request->all(), $filename, $exportFormat, $request->start_date, $request->end_date);

        session(['export_filename_dayoff' => 'public/exports/' . $filename]);

        return redirect()->back()->with('export', true);
    }

    public function checkExportStatus()
    {
        $filename = session('export_filename_dayoff');

        if ($filename && Storage::exists($filename)) {
            return response()->json([
                'ready' => true,
                'download_url' => Storage::url($filename),
            ]);
        }

        return response()->json([
            'ready' => false,
            'filename' => $filename,
        ]);
    }

    public function clearExportSession()
    {
        $filename = session('export_filename_dayoff');

        session()->forget('export_filename_dayoff');

        if ($filename && Storage::exists($filename)) {
            Storage::delete($filename);
            \Log::info("Laporan cuti dihapus dari storage: " . $filename);
        }

        return redirect()->back()->with('export', true);
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