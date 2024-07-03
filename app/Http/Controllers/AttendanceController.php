<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Attendance;
use App\Models\User;
use App\Models\ScheduleOb;
use Carbon\Carbon;
use App\Models\SettingCompany;

use App\Schemas\RoleSchema;
use App\Schemas\ParamSchema;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $userId = Auth::user()->id;
        $needsClockIn = false;
        $needsClockOut = false;
        $attendanceId = null;

        $shift = ScheduleOb::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        if ($shift) 
        {
            $attendance = Attendance::where('date', $today)
                ->where('user_id', $userId)
                ->first();

            if (!$attendance) {
                $needsClockIn = true;
            } elseif (!$attendance->clock_out) {
                $needsClockOut = true;
                $attendanceId = $attendance->slug;
            }
        }

        $start_date = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end_date = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $users = User::byCompany(Auth::user()->company_id)->byRole(RoleSchema::OB)->get();
        // Pastikan tanggal masuk valid
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);

        // Ambil data kehadiran berdasarkan rentang tanggal dan paginasi
        $query = Attendance::query();

        if ($request->has('user') && $request->user != '') {
            $query->whereHas('user', function ($q) use ($request) 
            {
                $q->where('name', 'like', '%' . $request->user . '%');
            });
        }

        $attendances = $query->byCompany(Auth::user()->company_id)
                            ->whereDate('date', '>=', $start_date)
                            ->whereDate('date', '<=', $end_date)->paginate(10);

        return view('attendance.index',compact('attendances', 'users', 'start_date', 'end_date', 'needsClockIn', 'needsClockOut', 'attendanceId', 'shift'));
    }

    public function create()
    {
        return view('attendance.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pic_in' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $check = $this->checkPoint();
        if(!$check['shiftStatus'])
        {
            return redirect()->route('attendance.index')->with('error', 'Tidak ada jadwal shift hari ini.');
        }

        if ($request->hasFile('pic_in')) 
        {
            // Hapus file lama jika ada        
            $file = $request->file('pic_in');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('attendance', $filename, 'public');
        }

        Attendance::create([
            'schedule_ob_id' => $check['shift']->id,
            'user_id' => Auth::user()->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => $check['clock_in'],
            'pic_in' => $filename,
            'ontime_in' => $check['ontime'] ? 1 : 0,
            'point' => $check['point'],
        ]);

        return redirect()->route('attendance.index')->with('success', 'Absen masuk berhasil.');
    }

    public function edit($slug)
    {
        $attendance = Attendance::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        return view('attendance.edit', compact('attendance'));
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'status' => 'required|string',
            'pic_out' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'note' => 'nullable|string',
        ]);

        $check = $this->checkPoint($request->note ? true : false);

        $attendance = Attendance::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();

        if ($request->hasFile('pic_out')) 
        {
            // Hapus file lama jika ada        
            $file = $request->file('pic_out');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('attendance', $filename, 'public');
        }

        $attendance->update([
            'clock_out' => $check['clock_in'],
            'pic_out' => $filename,
            'ontime_out' => $check['ontime'] ? 1 : 0,
            'point' => $check['point'],
            'note' => $request->note
        ]);

        return redirect()->route('attendance.index')->with('success', 'Absen keluar berhasil.');
    }

    protected function checkPoint($hasReason = false)
    {
        $shiftStatus = false;
        $shift = null;;
        $ontime = false;
        $points = 0;
        $clock_in = null;
        $clock_out = null;
        $now = Carbon::now();

        
        $shift = ScheduleOb::where('user_id', Auth::user()->id)
            ->where('date', $now->toDateString())
            ->first();

        if ($shift) 
        {
            $shiftStatus = true;
            $attended = Attendance::where('date', $now->toDateString())
                ->where('user_id', Auth::user()->id)
                ->first();
                
            $smtpConfig = SettingCompany::byCompany(Auth::user()->company_id)
                ->get()
                ->pluck('field_value', 'field_title');

            $clock_in = $shift->shiftingOb->clock_in ?? $smtpConfig['clock_in'];
            $clock_out = $shift->shiftingOb->clock_out ?? ParamSchema::CLOCKOUT;
            $late_point = $smtpConfig['late_point'] ?? 0;
            $on_time_point = $smtpConfig['on_time_point'] ?? 0;

            $clockInLimit = Carbon::createFromTimeString($clock_in)->endOfMinute();
            $clockOutLimit = Carbon::createFromTimeString($clock_out)->endOfMinute();

            if(!$attended)
            {
                $clockIn = Carbon::createFromTimeString($now)->endOfMinute();
                if ($clockIn->gt($clockInLimit)) {
                    $points = $late_point;
                    $ontime = false;
                } else {
                    $points = $on_time_point;
                    $ontime = true;
                }

                if ($clockIn->eq($clockInLimit)) {
                    $points = $on_time_point;
                    $ontime = true;
                }
            }else
            {
                $clockOut = Carbon::createFromTimeString($now)->endOfMinute();
                if ($clockOut->lt($clockOutLimit)) {
                    $ontime = false;
                } else 
                {
                    $ontime = true;
                }

                if ($clockOut->eq($clockOutLimit)) 
                {
                    $ontime = true;
                }


                if($attended->ontime_in && !$ontime && $hasReason == true)
                {
                    $points = $on_time_point;
                }
                elseif ($attended->ontime_in && $ontime) 
                {
                    $points = $on_time_point;
                }else
                {
                    $points = $late_point;
                }
                
            }

        }

        return ['shift'=>$shift, 'shiftStatus' => $shiftStatus, 'ontime' => $ontime, 'point' => $points, 'clock_in' => $now->toTimeString(), 'clock_out' => $now->toTimeString()];
    }
}

