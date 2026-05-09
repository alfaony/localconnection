<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Models\EmployeeChecking;
use App\Models\User;
use App\Models\UserStatus;

use App\Jobs\EmployeeCheckingExportJob;
use App\Events\EmployeeCheckinDeactivated;

use Carbon\Carbon;

use App\Schemas\ParamSchema;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeCheckingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $userId    = $request->input('user_id');
        $sortOrder = $request->input('sort_order') ?? 'desc';

        $tab       = $request->input('tab') ?? 'detail_checkin';
        $today     = Carbon::today()->toDateString();
        $manualCheck = $this->checkingDivision(Auth::user());

        $userSelect = User::where('is_checkin', true)->isActive()->byCompany(Auth::user()->company_id)->get();

        $employeeCheckings = collect();
        $users             = collect();

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth()->startOfDay();
        $end   = $endDate   ? Carbon::parse($endDate)->endOfDay()     : Carbon::now()->endOfDay();

        switch ($tab) {
            case 'detail_checkin':
                $query = EmployeeChecking::query();
                $query->byRole($userId);
                $query->when($start && $end, function ($q) use ($start, $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                });
                $employeeCheckings = $query
                    ->orderByRaw('DATE(updated_at) DESC')
                    ->orderByRaw('is_active = false')
                    ->orderBy('scheduled_time', 'desc')
                    ->paginate(10);
                break;

            case 'point_checkin':
                $users = User::where('is_checkin', true)->isActive()->withCheckinCounts($userId, $start, $end, $today)->get();
                $users = $users->map(function ($user) {
                    $user->point_percentage = $user->point_percentage;
                    return $user;
                })->sortBy([['point_percentage', $sortOrder]]);

                $currentPage = $request->get('page', 1);
                $users = new LengthAwarePaginator(
                    $users->forPage($currentPage, 10),
                    $users->count(),
                    10,
                    $currentPage,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
                break;
        }

        return view('employee_checking.index', compact(
            'employeeCheckings', 'users', 'userSelect', 'tab', 'manualCheck', 'start', 'end'
        ));
    }

    /**
     * Kembalikan checkin yang sedang aktif untuk user saat ini (dipakai saat page load/refresh).
     * Ini menggantikan snapshot Firebase yang dulu diambil saat monitorCheckin() di-load.
     */
    public function currentActive()
    {
        $user     = Auth::user();
        $now      = Carbon::now();
        $duration = (int) config('services.checking_setting.duration'); // detik

        // Range query — lebih efisien daripada TIMESTAMPDIFF karena bisa pakai index
        $windowStart = $now->copy()->subSeconds($duration);

        $checkin = EmployeeChecking::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('is_completed', false)
            ->where('is_dayoff', false)
            ->where('scheduled_time', '<=', $now)
            ->where('scheduled_time', '>=', $windowStart)
            ->orderBy('scheduled_time', 'desc')
            ->first();

        if (!$checkin) {
            return response()->json(['active' => false]);
        }

        $windowEnd  = Carbon::parse($checkin->scheduled_time)->addSeconds($duration);
        $timeLeftSec = max(0, $now->diffInSeconds($windowEnd, false));

        return response()->json([
            'active'            => true,
            'local_id'          => $checkin->id,
            'scheduled_time'    => $checkin->scheduled_time,
            'requires_photo'    => (bool) $user->requires_photo,
            'requires_location' => (bool) $user->requires_location,
            'time_left_seconds' => $timeLeftSec,
        ]);
    }

    /**
     * User melakukan check-in (submit popup).
     */
    public function update(Request $request, EmployeeChecking $employeeChecking)
    {
        $request->validate([
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'latitude'  => 'nullable',
            'longitude' => 'nullable',
            'recaptcha' => 'required',
        ]);

        $source = $request->input('source') ?? 'auto_checkin';

        // Verifikasi reCAPTCHA
        try {
            $response = Http::timeout(5)
                ->retry(2, 100)
                ->get('https://www.google.com/recaptcha/api/siteverify', [
                    'secret'   => config('captcha.secret'),
                    'response' => $request->input('recaptcha'),
                ]);

            if (!$response->successful() || !$response->json()['success']) {
                return response()->json(['message' => 'reCAPTCHA verification failed.'], 422);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('reCAPTCHA timeout: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            \App\Jobs\HandleErrorEmployeeCheckin::dispatch($employeeChecking);
            return response()->json('Connection timeout. Please try again.', 503);
        }

        try {
            if (!$employeeChecking) {
                return response()->json('Invalid check-in schedule.', 422);
            }

            if ($source == 'auto_checkin') {
                $scheduledStartTime = strtotime($employeeChecking->scheduled_time);
                $scheduledEndTime   = strtotime($employeeChecking->scheduled_timeout);
                $currentTime        = time();
                $checkinWindowEnd   = $scheduledStartTime + config('services.checking_setting.duration');

                if ($currentTime < $scheduledEndTime && ($currentTime < $scheduledStartTime || $currentTime > $checkinWindowEnd)) {
                    return response()->json('Check-in time is outside the allowed window.', 422);
                }
            }

            if ($source == 'manual_checkin') {
                $lastScheduledCheckin = EmployeeChecking::where('user_id', $employeeChecking->user_id)
                    ->where('is_active', false)
                    ->whereDate('scheduled_time', Carbon::today())
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($lastScheduledCheckin) {
                    $timeDifference = Carbon::now()->diffInMinutes(Carbon::parse($lastScheduledCheckin->checkin_start_time));
                    if ($timeDifference < 30) {
                        return response()->json('Check-in gagal: Anda harus menunggu 30 menit sebelum melakukan check-in manual berikutnya.', 422);
                    }
                }
            }

            if ($request->hasFile('photo')) {
                $employeeChecking->photo_path = $request->file('photo')->store('checkin_photos');
            }

            if ($request->filled('latitude'))  $employeeChecking->location_latitude  = $request->input('latitude');
            if ($request->filled('longitude')) $employeeChecking->location_longitude = $request->input('longitude');

            $user     = Auth::user();
            $recorded = UserStatus::where('user_id', $user->id)
                ->where('fcm_id', $request->input('fcm_token'))
                ->first();
            if ($recorded) {
                $recorded->last_scheduled_checkin = Carbon::now();
                $recorded->save();
            }

            $employeeChecking->is_active        = false;
            $employeeChecking->is_completed     = true;
            $employeeChecking->checkin_start_time = Carbon::now();
            $employeeChecking->save();

            // Beritahu frontend bahwa checkin ini sudah selesai (tutup popup)
            broadcast(new EmployeeCheckinDeactivated($employeeChecking->user_id, $employeeChecking->id));

            return response()->json(['message' => 'Check-in updated successfully']);

        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            \App\Jobs\HandleErrorEmployeeCheckin::dispatch($employeeChecking);
            return response()->json(['message' => 'Check-in updated successfully']);
        }
    }

    /**
     * User menutup popup tanpa submit — tandai tidak aktif.
     */
    public function updatestatus(Request $request, EmployeeChecking $employeeChecking)
    {
        $employeeChecking->is_active = false;
        $employeeChecking->save();

        broadcast(new EmployeeCheckinDeactivated($employeeChecking->user_id, $employeeChecking->id));

        return response()->json(['message' => 'Check-in updated successfully']);
    }

    /**
     * Cek apakah user boleh checkin manual sekarang.
     */
    public function checkLastScheduledCheckin(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $currentCheckinTime   = Carbon::now();
            $lastScheduledCheckin = EmployeeChecking::where('user_id', $user->id)
                ->where('is_active', false)
                ->whereDate('scheduled_time', Carbon::today())
                ->orderBy('updated_at', 'desc')
                ->first();

            $startTime = Carbon::parse($user->start_time);
            $endTime   = Carbon::parse($user->end_time);

            if ($currentCheckinTime->lt($startTime) || $currentCheckinTime->gte($endTime)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Check-in hanya diizinkan antara jam ' . $startTime->format('H:i') . ' dan ' . $endTime->format('H:i'),
                ], 200);
            }

            if ($lastScheduledCheckin) {
                $timeDifference = $currentCheckinTime->diffInMinutes(Carbon::parse($lastScheduledCheckin->checkin_start_time));
                if ($timeDifference < 30) {
                    return response()->json(['status' => false, 'message' => 'Anda harus menunggu 30 menit sebelum melakukan check-in manual berikutnya'], 200);
                }
            }

            return response()->json(['status' => true, 'message' => 'Check-in diizinkan'], 200);
        }

        return response()->json(['status' => false, 'message' => 'Pengguna tidak ditemukan'], 400);
    }

    public function export(Request $request, $format)
    {
        try {
            $filename     = 'employee_checkin_' . time() . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
            $exportFormat = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

            $userId = $request->input('user_id');
            $start  = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->startOfMonth()->startOfDay();
            $end    = $request->input('end_date')   ? Carbon::parse($request->input('end_date'))->endOfDay()     : Carbon::now()->endOfDay();
            $today  = Carbon::today()->toDateString();
            $sort   = $request->input('sort') ?? 'desc';
            $role   = Auth::user()->role->name ?? null;

            EmployeeCheckingExportJob::dispatch($filename, $exportFormat, Auth::user(), $userId, $start, $end, $today, $sort, $role);

            session(['export_filename_checkin' => $filename]);

            return redirect()->back()->with('export', true);
        } catch (\Throwable $th) {
            Log::error('Error storing file: ' . $th->getMessage());
            return redirect()->back()->with('export', true);
        }
    }

    public function checkExportStatus()
    {
        $filename = session('export_filename_checkin');
        try {
            if ($filename && Storage::exists($filename)) {
                return response()->json(['ready' => true, 'download_url' => s3_asset(true, 10, $filename)]);
            }
            return response()->json(['ready' => false, 'filename' => $filename]);
        } catch (\Throwable $th) {
            return response()->json(['ready' => false, 'filename' => $filename]);
        }
    }

    public function clearsession()
    {
        $filename = session('export_filename_checkin');
        session()->forget('export_filename_checkin');

        if ($filename && Storage::exists($filename)) {
            Storage::delete($filename);
        }

        return redirect()->back()->with('export', true);
    }

    protected function checkingDivision($user)
    {
        return [
            'manual_checkin'    => (bool) $user->manual_checkin,
            'requires_photo'    => (bool) $user->requires_photo,
            'requires_location' => (bool) $user->requires_location,
        ];
    }
}
