<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
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
        $userId = $request->input('user_id');
        $tab = $request->input('tab') ?? 'point_checkin';
        $today = Carbon::today()->toDateString();

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
            case 'point_checkin':
                // Ambil data pengguna dengan pagination
                $users = User::byCompany(auth()->user()->company_id)
                    ->when($userId, function ($query) use ($userId, $start) {
                        return $query->where('id', $userId);
                    })
                    ->withCount([
                        'employeeCheckings as total_checkin_today' => function ($query) use ($today) {
                            $query->where('is_active', false)->where('is_completed', true)->whereDate('scheduled_time', $today);
                        },
                        'employeeCheckings as total_successful_checkins' => function ($query) use ($startDate, $endDate, $start, $end) {
                            $query->where('is_active', false)->where('is_completed', true);
                            if ($startDate && $endDate) 
                            {
                                $query->whereBetween('scheduled_time', [$start, $end]);
                            }
                        },
                        'employeeCheckings as total_failed_checkins' => function ($query) use ($startDate, $endDate, $start, $end) {
                            $query->where('is_completed', false)
                                ->where('is_active', false);
                            if ($startDate && $endDate) {
                                $query->whereBetween('scheduled_time', [$start, $end]);
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

                    $user->point_checkin = "{$totalCheckins} (" . number_format($pointPercentage, 2) . "%)";
                    $user->today_percentage = "{$totalToday} (" . number_format($todayPercentage, 2) . "%)";
                }
                break;

            case 'detail_checkin':
                if ($userId) {
                    $query = EmployeeChecking::query();

                    // Filter by user ID
                    $query->when($userId, function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });

                    // Filter by date range
                    $query->when($startDate && $endDate, function ($q) use ($start, $end) {
                        $q->whereBetween('scheduled_time', [$start, $end]);
                    });

                    // Ambil data dengan pagination
                    $employeeCheckings = $query->orderBy('scheduled_time', 'desc')->paginate(10);
                }
                break;
        }

        return view('employee_checking.index', compact('employeeCheckings', 'users', 'userSelect', 'tab'));
    }

    /**
     * Diploy Report
     */
    public function report(Request $request)
    {




        return view('employee_checking.report', compact('checkins','users'));
    }
        /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmployeeChecking  $employeeChecking
     * @return \Illuminate\Http\Response
     */
    public function show(EmployeeChecking $employeeChecking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EmployeeChecking  $employeeChecking
     * @return \Illuminate\Http\Response
     */
    public function edit(EmployeeChecking $employeeChecking)
    {
        //
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
        // Verifikasi reCAPTCHA
        // $recaptcha = $request->input('recaptcha');
        // $response = Http::post('https://www.google.com/recaptcha/api/siteverify', [
        //     'secret' => config('captcha.secret'),
        //     'response' => $recaptcha,
        // ]);

        // if (!$response->json()['success']) {
        //     return response()->json(['message' => 'reCAPTCHA verification failed.'], 422);
        // }

        // Validasi bahwa $local_id sesuai dengan jadwal dan user yang melakukan check-in

        if (!$employeeChecking) 
        {
            return response()->json(['message' => 'Invalid check-in schedule.'], 422);
        }

        // Pastikan check-in dilakukan dalam waktu yang diperbolehkan
        $scheduledTime = strtotime($employeeChecking->scheduled_time);
        $currentTime = time();
        if ($currentTime < $scheduledTime || $currentTime > ($scheduledTime + config('services.checking_setting.duration'))) {
            return response()->json('Check-in time is outside the allowed window.', 422);
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
                'last_scheduled_checkin' => $employeeChecking->scheduled_time,
            ]);
        } else 
        {
            // Jika UserStatus belum ada, buat satu dan simpan fcm_id
            $user->status()->create([
                'last_scheduled_checkin' => $employeeChecking->scheduled_time,
            ]);
        }

        // Update status check-in
        $employeeChecking->is_active = false;
        $employeeChecking->is_completed = true;
        $employeeChecking->checkin_start_time = Carbon::now();
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmployeeChecking  $employeeChecking
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeChecking $employeeChecking)
    {
        //
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
}
