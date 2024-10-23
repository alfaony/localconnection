<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;

use App\Models\EmployeeChecking;
use App\Models\User;

use Carbon\Carbon;

use App\Schemas\ParamSchema;
class EmployeeCheckingController extends Controller
{
    
    protected $firebaseDatabase;

    public function __construct()
    {
        $this->firebaseDatabase = (new Factory)
        ->withServiceAccount(storage_path(config('services.firebase.service_account')))
        ->withDatabaseUri(config('services.firebase.service_database_checkin_url'))
        ->createDatabase();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Search
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $userId = $request->input('user_id') ?? Auth::user()->id;
        $tab = $request->input('tab') ?? 'detail_checkin';
        $today = Carbon::today()->toDateString();
        $manualCheck = $this->checkingDivision(Auth::user());

        // Load data pengguna
        $userSelect = User::byCompany(Auth::user()->company_id)->get();

        // Nullable variabel
        $employeeCheckings = collect();
        $users = collect();

        // Hitung jumlah hari dalam rentang tanggal
        $totalDays = 1; // Default 1 hari jika startDate dan endDate tidak ada

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today()->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::today()->endOfDay();
        $totalDays = $start->diffInDays($end) + 1; // Total hari dalam rentang

        switch ($tab) 
        {
            case 'detail_checkin':
                if ($userId) {
                    $query = EmployeeChecking::query();

                    // Filter by user ID
                    $query->when($userId, function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });

                    // Filter by date range
                    $query->when($startDate && $endDate, function ($q) use ($start, $end) {
                        $q->whereBetween('created_at', [$start, $end]);
                    });

                    // Ambil data dengan pagination
                    $employeeCheckings = $query->orderBy('created_at', 'desc')->paginate(10);
                }
                break;
            case 'point_checkin':
                // Ambil data pengguna dengan pagination
                $totalDaysQuery = EmployeeChecking::where('user_id', $userId)->where('is_dayoff', false);
                // Filter berdasarkan rentang tanggal (jika ada)
                if ($start && $end) 
                {
                    $totalDaysQuery->whereBetween('created_at', [$start, $end]);
                }

                $totalDays = $totalDaysQuery->distinct()->count(DB::raw('DATE(created_at)'));

                $users = User::byCompany(auth()->user()->company_id)
                    ->when($userId, function ($query) use ($userId, $start) {
                        return $query->where('id', $userId);
                    })
                    ->withCount([
                        'employeeCheckings as total_checkin_today' => function ($query) use ($today) {
                            $query->where('is_active', false)->where('is_completed', true)->where('is_dayoff', false)->whereDate('created_at', $today);
                        },
                        'employeeCheckings as total_successful_checkins' => function ($query) use ($startDate, $endDate, $start, $end) {
                            $query->where('is_active', false)->where('is_completed', true)->where('is_dayoff', false);
                            if ($start && $end) 
                            {
                                $query->whereBetween('created_at', [$start, $end]);
                            }
                        },
                        'employeeCheckings as total_failed_checkins' => function ($query) use ($startDate, $endDate, $start, $end) {
                            $query->where('is_completed', false)
                                ->where('is_active', false)
                                ->where('is_dayoff', false);
                            if ($startDate && $endDate) {
                                $query->whereBetween('created_at', [$start, $end]);
                            }
                        }
                    ])
                    ->paginate(10);

                // Hitung point check-in dan persentase
                foreach ($users as $user) {
                    $totalCheckins = $user->total_successful_checkins;
                    $totalToday = $user->total_checkin_today ?? 0;

                    // Hitung total target check-in
                    $targetCheckins = $totalDays * ParamSchema::TARGET_CHECKIN;
                    // Hitung presentase point check-in dan check-in hari ini
                    $pointPercentage = $targetCheckins ? ($totalCheckins / $targetCheckins) * 100 : 0;
                    $todayPercentage = $totalToday ? ($totalToday / 10) * 100 : 0;

                    $user->point_checkin = "{$totalCheckins} (" . number_format($pointPercentage, 0) . "%)";
                    $user->today_percentage = "{$totalToday} (" . number_format($todayPercentage, 0) . "%)";
                }
                break;
        }

        return view('employee_checking.index', compact('employeeCheckings', 'users', 'userSelect', 'tab', 'manualCheck'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmployeeChecking  $employeeChecking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmployeeChecking $employeeChecking)
    {
        $request->validate([
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
            'recaptcha' => 'required'
        ]);
        $source = $request->input('source') ?? 'auto_checkin';

        // Verifikasi reCAPTCHA
        $recaptcha = $request->input('recaptcha');
        $response = Http::get('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('captcha.secret'),
            'response' => $recaptcha,
        ]);

        if (!$response->json()['success']) 
        {
            return response()->json(['Verification reCAPTCHA verification failed.'], 422);
        }

        // Validasi bahwa $local_id sesuai dengan jadwal dan user yang melakukan check-in

        if (!$employeeChecking) 
        {
            return response()->json('Invalid check-in schedule.', 422);
        }

        // Pastikan check-in dilakukan dalam waktu yang diperbolehkan
        
        if($source == 'auto_checkin')
        {
            $scheduledTime = strtotime($employeeChecking->scheduled_time);
            $currentTime = time();
            if ($currentTime < $scheduledTime || $currentTime > ($scheduledTime + config('services.checking_setting.duration'))) {
                return response()->json('Check-in time is outside the allowed window.', 422);
            }
        }

        if ($source == 'manual_checkin') 
        {
            $lastScheduledCheckin = Auth::user()->status ? Auth::user()->status->last_scheduled_checkin : null;
    
            if ($lastScheduledCheckin) 
            {
                $lastCheckinTime = Carbon::parse($lastScheduledCheckin);
                $currentCheckinTime = Carbon::now();
                $timeDifference = $currentCheckinTime->diffInMinutes($lastCheckinTime);
    
                if ($timeDifference < 30) {
                    return response()->json('Check-in gagal: Anda harus menunggu 30 menit sebelum melakukan check-in manual berikutnya.', 422);
                }
            }
        }

        // Simpan foto jika ada
        if ($request->hasFile('photo')) 
        {
            $photoPath = $request->file('photo')->store('checkin_photos', 'public');
            $employeeChecking->photo_path = Storage::url($photoPath);
        }

        // Update latitude dan longitude jika ada
        if ($request->filled('latitude')) 
        {
            $employeeChecking->location_latitude = $request->input('latitude');
        }
        if ($request->filled('longitude')) {
            $employeeChecking->location_longitude = $request->input('longitude');
        }

        $user = Auth::user();

        // Update fcm_id pada user_status terkait
        if ($user->status) {
            $user->status->update([
                'last_scheduled_checkin' => Carbon::now(),
            ]);
        } else 
        {
            // Jika UserStatus belum ada, buat satu dan simpan fcm_id
            $user->status()->create([
                'last_scheduled_checkin' => Carbon::now(),
            ]);
        }

        // Update status check-in
        $employeeChecking->is_active = false;
        $employeeChecking->is_completed = true;
        $employeeChecking->checkin_start_time = Carbon::now();

        if($source == 'manual_checkin')
        {
            $employeeChecking->scheduled_time = Carbon::now();
            $employeeChecking->scheduled_timeout = Carbon::now();
            $employeeChecking->checkin_start_time = Carbon::now();
        }
        $employeeChecking->save();

        // Update data di Firebase
        if($this->firebaseDatabase)
        {
            $payload = 
            [
                'created_at' => $employeeChecking->created_at,
                'updated_at' => $employeeChecking->updated_at,
                'local_id' => $employeeChecking->id,
                'scheduled_time' => $employeeChecking->scheduled_time,
                'is_active' => false,
            ];

            $this->firebaseDatabase->getReference('employee_checkins/' . $employeeChecking->user_id . '/' . $employeeChecking->id)->remove();
        }

        return response()->json(['message' => 'Check-in updated successfully']);
    }

    /**
     * Update Status false
     */
    public function updatestatus(Request $request, EmployeeChecking $employeeChecking)
    {
        $employeeChecking->is_active = false;
        $employeeChecking->save();


        if($this->firebaseDatabase)
        {
            $payload = 
            [
                'created_at' => $employeeChecking->created_at,
                'local_id' => $employeeChecking->id,
                'scheduled_time' => $employeeChecking->scheduled_time,
                'is_active' => false,
            ];

            $this->firebaseDatabase->getReference('employee_checkins/' . Auth::user()->id . '/' . $employeeChecking->id)->remove();
        }

        return response()->json(['message' => 'Check-in updated successfully']);
    }

    /**
     * 
     * Protected for checking has divison
     */
    protected function checkingDivision($user)
    {
        $divisions = $user->divisions;
        $employeeChecking = EmployeeChecking::where('user_id', $user->id)->where('scheduled_time', Carbon::now())->count();

        $manual_checkin = false;
        $requires_photo = false;
        $requires_location = false;

        if($divisions->count() > 0)
        {
            foreach ($divisions as $division) 
            {
                if($division->manual_checkin)
                {
                    $manual_checkin = true;
                }

                if($division->requires_photo)
                {
                    $requires_photo = true;
                }

                if($division->requires_location)
                {
                    $requires_location = true;
                }
            }
        }

        return [
            'manual_checkin' => $manual_checkin,
            'requires_photo' => $requires_photo,
            'requires_location' => $requires_location
        ];
    }
}
