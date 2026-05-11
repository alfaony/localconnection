<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Schemas\RoleSchema;
use App\Helpers\Access;
use App\Models\ScheduleOb;
use App\Models\User;
use App\Models\ShiftingOb;

use Carbon\Carbon;

class ScheduleObController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::byCompany(Auth::user()->company_id)
            ->where(function ($query) {
                $query
                    ->byRole(RoleSchema::OB)
                    ->orWhere('is_shift_attendance', true);
            })
            ->get();

        $shifts = ShiftingOb::byCompany(Auth::user()->company_id)->get();
        $deleteAccess = Access::can('destroy', 'schedule_obs');

        return view('schedule_ob.index', compact('users', 'shifts', 'deleteAccess'));
    }

    public function calendar(Request $request)
    {
        // if (!Access::can('calendar', 'schedule_obs')) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $query = ScheduleOb::byCompany(Auth::user()->company_id)
            ->with('user', 'shiftingOb');

        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('date', [
                Carbon::parse($request->start)->toDateString(),
                Carbon::parse($request->end)->toDateString(),
            ]);
        }

        return response()->json(
            $query->get()->map(fn($s) => [
                'id'    => $s->id,
                'title' => $s->user->name . ' - ' . $s->shiftingOb->name,
                'start' => $s->date,
                'extendedProps' => [
                    'user_id'        => $s->user_id,
                    'shifting_ob_id' => $s->shifting_ob_id,
                ],
            ])
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'shifting_ob_id' => 'required|exists:shifting_obs,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
    
        DB::beginTransaction();

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate   = Carbon::parse($request->end_date);

            // Ambil semua tanggal yang sudah ada untuk user+shift ini dalam range
            $existingDates = ScheduleOb::where('user_id', $request->user_id)
                ->where('shifting_ob_id', $request->shifting_ob_id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->toArray();

            $created = 0;
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                if (in_array($date->toDateString(), $existingDates)) {
                    continue;
                }
                ScheduleOb::create([
                    'user_id'        => $request->user_id,
                    'shifting_ob_id' => $request->shifting_ob_id,
                    'date'           => $date->toDateString(),
                ]);
                $created++;
            }

            DB::commit();

            if ($created === 0) {
                return redirect()->route('schedule-ob.index')->with('error', 'Semua tanggal dalam range ini sudah dijadwalkan untuk shift yang sama.');
            }

            $skipped = count($existingDates);
            $message = "Penjadwalan berhasil dibuat ($created hari).";
            if ($skipped > 0) {
                $message .= " $skipped tanggal dilewati karena sudah ada.";
            }

            return redirect()->route('schedule-ob.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membuat penjadwalan.');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ScheduleOb  $scheduleOb
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'shifting_ob_id' => 'required|exists:shifting_obs,id',
            'date' => 'required|date',
        ]);

        if ($this->checkScheduleConflict($request->user_id, $request->shifting_ob_id, $request->date, $id)) {
            return redirect()->back()->with('error', 'Pengguna sudah dijadwalkan untuk shift ini pada tanggal yang dipilih.');
        }

        $schedule = ScheduleOb::byCompany(Auth::user()->company_id)->where('id',$id)->firstOrFail();
        $schedule->update($request->all());

        return redirect()->route('schedule-ob.index')->with('success', 'Penjadwalan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ScheduleOb  $scheduleOb
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $schedule = ScheduleOb::byCompany(Auth::user()->company_id)->where('id',$id)->firstOrFail();
        $schedule->delete();

        return redirect()->route('schedule-ob.index')->with('success', 'Penjadwalan berhasil dihapus.');
    }

    private function checkScheduleConflict($userId, $shiftingObId, $date, $scheduleId = null)
    {
        $query = ScheduleOb::where('user_id', $userId)
            ->where('shifting_ob_id', $shiftingObId)
            ->where('date', $date);

        if ($scheduleId) {
            $query->where('id', '!=', $scheduleId);
        }

        return $query->exists();
    }
}
