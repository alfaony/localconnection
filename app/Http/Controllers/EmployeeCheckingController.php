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
        $users = User::byCompany(Auth::user()->company_id)->get();
        $query = EmployeeChecking::query();

        // Filter by user
        if ($request->has('user') && $request->user) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user . '%');
            });
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('scheduled_time', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }

        $query->byRole();
        // Exclude check-ins scheduled for times that have passed
        $query->where('scheduled_time', '<', Carbon::now());

        $employeeCheckings = $query->orderBy('scheduled_time','desc')->paginate(10);

        return view('employee_checking.index', compact('employeeCheckings', 'users'));
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
