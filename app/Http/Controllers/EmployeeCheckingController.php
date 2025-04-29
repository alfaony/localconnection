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
use App\Models\UserStatus;

use App\Jobs\EmployeeCheckingExportJob;

use Carbon\Carbon;

use App\Schemas\ParamSchema;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $sortOrder = $request->input('sort_order') ?? 'desc';
        
        $tab = $request->input('tab') ?? 'detail_checkin';
        $today = Carbon::today()->toDateString();
        $manualCheck = $this->checkingDivision(Auth::user());

        // Load data pengguna
        $userSelect = User::where('is_checkin',true)->byCompany(Auth::user()->company_id)->get();

        // Nullable variabel
        $employeeCheckings = collect();
        $users = collect();
        

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth()->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        switch ($tab) 
        {
            case 'detail_checkin':
                $query = EmployeeChecking::query();

                // Filter by user ID
                $query->byRole($userId);

                // Filter by date range
                $query->when($startDate && $endDate, function ($q) use ($start, $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                });

                // Ambil data dengan pagination
                $employeeCheckings = $query->orderByRaw('DATE(updated_at) DESC') // Urutkan tanggal secara menurun
                ->orderByRaw('is_active = false') // Pindahkan is_active=false ke bawah
                ->orderBy('scheduled_time', 'desc') // Urutkan berdasarkan updated_at
                ->paginate(10);
                break;
            case 'point_checkin':                
                $users = User::where('is_checkin', true)->withCheckinCounts($userId, $start, $end, $today)->get();

                // Kalkulasi point_percentage dan sorting di sisi PHP
                $users = $users->map(function ($user) {
                    $user->point_percentage = $user->point_percentage;
                    return $user;
                })->sortBy([
                    ['point_percentage', $sortOrder]
                ]);

                $currentPage = $request->get('page', 1);
                $users = new LengthAwarePaginator
                (
                    $users->forPage($currentPage, 10), 
                    $users->count(), 
                    10, 
                    $currentPage,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
                break;
        }

        return view('employee_checking.index', compact('employeeCheckings', 'users', 'userSelect', 'tab', 'manualCheck','start', 'end'));
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
            // Konversi waktu terjadwal ke format timestamp
            $scheduledStartTime = strtotime($employeeChecking->scheduled_time);
            $scheduledEndTime = strtotime($employeeChecking->scheduled_timeout);
            $currentTime = time();

            // Hitung batas waktu check-in yang diperbolehkan
            $checkinWindowEnd = $scheduledStartTime + config('services.checking_setting.duration');

            // Periksa apakah waktu saat ini berada di luar jendela waktu check-in yang diperbolehkan
            if ($currentTime < $scheduledEndTime && ($currentTime < $scheduledStartTime || $currentTime > $checkinWindowEnd)) 
            {
                return response()->json('Check-in time is outside the allowed window.', 422);
            }
        }

        if ($source == 'manual_checkin') 
        {
            $lastScheduledCheckin = EmployeeChecking::where('user_id', $employeeChecking->user_id)->where('is_active', false)->whereDate('scheduled_time', Carbon::today())->orderBy('updated_at', 'desc')->first();
    
            if ($lastScheduledCheckin) 
            {
                $lastCheckinTime = Carbon::parse($lastScheduledCheckin->checkin_start_time);
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
        // if ($user->status) {
        //     $user->status->update([
        //         'last_scheduled_checkin' => Carbon::now(),
        //     ]);
        // } else 
        // {
        //     // Jika UserStatus belum ada, buat satu dan simpan fcm_id
        //     $user->status()->create([
        //         'last_scheduled_checkin' => Carbon::now(),
        //     ]);
        // }
        $recorded = UserStatus::where('user_id', $user->id)->where('fcm_id', $request->input('fcm_token'))->first();
        if ($recorded)
        {
            $recorded->last_scheduled_checkin = Carbon::now();
            $recorded->save();
        }

        // Update status check-in
        $employeeChecking->is_active = false;
        $employeeChecking->is_completed = true;
        $employeeChecking->checkin_start_time = Carbon::now();

        if($source == 'manual_checkin')
        {
            // $employeeChecking->scheduled_time = Carbon::now();
            // $employeeChecking->scheduled_timeout = Carbon::now();
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
     * Check last Schedule Check-In
     */
    public function checkLastScheduledCheckin(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $currentCheckinTime = Carbon::now();
            $lastScheduledCheckin = EmployeeChecking::where('user_id', $user->id)
                ->where('is_active', false)
                ->whereDate('scheduled_time', Carbon::today())
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($currentCheckinTime->hour < 8 || $currentCheckinTime->hour >= 17) {
                return response()->json(['status' => false, 'message' => 'Check-in hanya diizinkan antara jam 08:00 dan 17:00'], 200);
            }

            if ($lastScheduledCheckin) {
                $lastCheckinTime = Carbon::parse($lastScheduledCheckin->checkin_start_time);
                $timeDifference = $currentCheckinTime->diffInMinutes($lastCheckinTime);

                if ($timeDifference < 30) {
                    return response()->json(['status' => false, 'message' => 'Anda harus menunggu 30 menit sebelum melakukan check-in manual berikutnya'], 200);
                } else {
                    return response()->json(['status' => true, 'message' => 'Check-in diizinkan'], 200);
                }
            } else {
                return response()->json(['status' => true, 'message' => 'Check-in diizinkan'], 200);
            }
        }

        return response()->json(['status' => false, 'message' => 'Pengguna tidak ditemukan'], 400);
    }
    /**
     * 
     * Protected for checking has divison
     */

     /**
      * 
      */
    public function export(Request $request, $format)
    {
        // Determine file name and format
        try {
            $filename = 'quotes_' . time() . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
            $exportFormat = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
    
            $userId = $request->input('user_id');
            $start = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->startOfMonth()->startOfDay();
            $end = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
            $today = Carbon::today()->toDateString();
            $sort = $request->input('sort') ?? "asc";
            $role = Auth::user()->role->name ?? NULL; 
            
            // Queue the export and store the job file name in session
            EmployeeCheckingExportJob::dispatch($filename, $exportFormat, Auth::user()->company_id, $userId, $start, $end, $today, $sort, $role);
    
    
            $filename = "public/" . $filename;
            session(['export_filename_checkin' => $filename]);
            $filename = session('export_filename_checkin');
    
            return redirect()->back()->with('export',true);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error("Error storing file: " . $th->getMessage());
            return redirect()->back()->with('export',true);
        }
    }

    public function checkExportStatus()
    {
        $filename = session('export_filename_checkin');

        if ($filename && Storage::exists($filename)) {
            // Provide the download URL if file exists
            $downloadUrl = Storage::url($filename);
            return response()->json(['ready' => true, 'download_url' => $downloadUrl]);
        }
    
        return response()->json(['ready' => false,'filename' => $filename]);
    }

    public function clearsession()
    {
        // Retrieve the export filename from the session
        $filename = session('export_filename_checkin');

        // Forget the session variable to prevent re-download on refresh
        session()->forget('export_filename_checkin');

        // Check if the file exists and delete it
        if ($filename && Storage::exists($filename)) {
            Storage::delete($filename);
            Log::info("File deleted from storage: " . $filename);
        }

        return redirect()->back()->with('export',true);
    }

    protected function checkingDivision($user)
    {
        $manual_checkin = false;
        $requires_photo = false;
        $requires_location = false;

        if($user->manual_checkin)
        {
            $manual_checkin = true;
        }

        if($user->requires_photo)
        {
            $requires_photo = true;
        }

        if($user->requires_location)
        {
            $requires_location = true;
        }

        return [
            'manual_checkin' => $manual_checkin,
            'requires_photo' => $requires_photo,
            'requires_location' => $requires_location
        ];
    }
}
