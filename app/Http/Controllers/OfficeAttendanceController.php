<?php

namespace App\Http\Controllers;

use App\Models\BarcodeAttendance;
use App\Models\OfficeAttendance;
use App\Jobs\ProcessScanAttendanceJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Helpers\Access;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use Carbon\Carbon;

class OfficeAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        // Data absensi dengan pagination
        $query = OfficeAttendance::byCompany($companyId, Access::can('general_access', 'office_attendances'),Access::can('division_access', 'office_attendances'))
            ->with('user');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('time', [Carbon::parse($request->start_date)->startOfDay(), Carbon::parse($request->end_date)->endOfDay()]);
        }

        if ($request->filled('employee')) {
            $query->where('user_id', $request->employee);
        }

        if ($request->filled('filter')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->filter . '%')
                  ->orWhere('email', 'like', '%' . $request->filter . '%');
            });
        }

        if ($request->sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $officeAttendance = $query->paginate(10);
        
        // Data karyawan
        $employees = User::byCompany($companyId)->where('wfo_check_in', true)->get();
        
        // Statistik untuk dashboard
        $totalAttendance = OfficeAttendance::byCompany($companyId, Access::can('general_access', 'office_attendances'),Access::can('division_access', 'office_attendances'))
            ->selectRaw('count(*) as total_attendance')
            ->groupBy('user_id')
            ->get()
            ->sum('total_attendance');

       $todayAttendance = OfficeAttendance::byCompany($companyId, Access::can('general_access', 'office_attendances'),Access::can('division_access', 'office_attendances'))
        ->whereDate('time', Carbon::today())
        ->selectRaw('user_id, COUNT(*) as total_absen')
        ->groupBy('user_id')
        ->get()->count('user_id');

        
        $averageAttendancePerDay = OfficeAttendance::byCompany($companyId, Access::can('general_access', 'office_attendances'),Access::can('division_access', 'office_attendances'))
            ->selectRaw('count(*) as total_attendance')
            ->groupByRaw('date(created_at)')
            ->pluck('total_attendance')
            ->avg();
            
        $totalEmployees = User::byCompany($companyId)->where('wfo_check_in', true)->count();
        
        $locationCount = OfficeAttendance::byCompany($companyId, Access::can('general_access', 'office_attendances'),Access::can('division_access', 'office_attendances'))
            ->whereNotNull('location_lat')
            ->whereNotNull('location_long')
            ->count();

        // Permission
        

        
        return view('office_attendance.index', compact(
            'officeAttendance', 
            'employees',
            'totalAttendance',
            'todayAttendance',
            'totalEmployees',
            'locationCount'
        ));
    }
    

    public function scan($code)
    {
        if(!Auth::user()->wfo_check_in || !Access::can('scan','office_attendances'))
        {
            return redirect()->route('office-attendance.index')->with('error', 'Absensi WFH belum diaktifkan. Silahkan hubungi admin.');
        }
        
        if(!Auth::user()->shouldWorkToday())
        {
            return redirect()->route('office-attendance.index')->with('error', 'Tidak ada jadwal absensi untuk hari ini.');   
        }

        $timesPerDay = config('services.checking_setting.times_per_day');
        $todayCount = OfficeAttendance::byCompany(auth()->user()->company_id)
            ->whereDate('created_at', today())
            ->where('user_id', auth()->id())
            ->count();

        if($todayCount >= $timesPerDay)
        {
            return redirect()->route('office-attendance.index')->with('error', 'Absensi sudah mencapai batas maksimum hari ini. Silahkan coba lagi besok.');
        }

        $verified = $this->verified($code);
        if($verified)
        {
            return $verified;
        }
        
        $barcode = BarcodeAttendance::where('code', $code)->first();
        
        // Cek apakah barcode sudah diverifikasi oleh user ini (fallback check)
        $isAlreadyVerified = $barcode->is_used && $barcode->user_id == auth()->id();
        
        // Jika belum verified, dispatch job
        if (!$isAlreadyVerified) {
            ProcessScanAttendanceJob::dispatch(
                $barcode->id,
                auth()->id(),
                auth()->user()->company_id
            );
        }

        // Pass status verifikasi ke view
        return view('office_attendance.attendance', [
            'barcode' => $barcode,
            'isAlreadyVerified' => $isAlreadyVerified
        ])->with('success', $isAlreadyVerified ? 'QR code sudah terverifikasi.' : 'QR code sedang diverifikasi. Harap tunggu...');
    }

    public function complete(Request $request, $code)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
            'photo' => 'required'
        ], [
            'location_lat.required' => 'Lokasi harus diisi',
            'location_long.required' => 'Lokasi harus diisi',
            'photo.required' => 'Foto selfie harus diisi',
        ]);
        
        $verified = $this->verified($code);
        if($verified)
        {
            // dd($verified);
            return $verified;
        }
        $barcode = BarcodeAttendance::where('code', $code)->first();

        $foto = null;
        if ($request->photo) 
        {
            $foto = $this->saveBase64ImageToStorage($request->photo, 'office_attendance');
        }


        $officeAttendance = new OfficeAttendance();
        $officeAttendance->create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'barcode_attendance_id' => $barcode->id,
            'time' => now(),
            'location_lat' => $request->latitude,
            'location_long' => $request->longitude,
            'selfie_path' => $foto
        ]);

        return redirect()->route('office-attendance.index')->with('success', 'Absensi lengkap dengan foto dan lokasi.');
    }

    public function export(Request $request)
    {
        return Excel::download(new AttendanceExport($request), 'absensi_' . now()->format('Ymd_His') . '.xlsx');
    }

    protected function verified($code)
    {
        $barcode = BarcodeAttendance::where('code', $code)
            ->where(function($query) {
                $query->where('is_used', false)
                    ->orWhere(function($query) {
                        $query->where('user_id', auth()->id())
                            ->where('is_used', true);
                    });
            })
            ->first();
        if (!$barcode) 
        {
            return redirect()->route('office-attendance.index')->with('error', 'QR code tidak ditemukan atau sudah digunakan.');
        }

        // if($barcode->user_id == auth()->id())
        // {
        //     return view('office_attendance.attendance', compact('barcode'))->with('success', 'QR code sedang diverifikasi. Harap tunggu...');
        // }

        $officeAttendance = OfficeAttendance::where('barcode_attendance_id', $barcode->id)->first();
        if($officeAttendance) 
        {
            return redirect()->route('office-attendance.index')->with('error', 'QR code sudah digunakan.');   
        }
    }

    protected function saveBase64ImageToStorage($base64Image, $folder)
    {
        $fileName = uniqid() . '.png';

        // Decode Base64 image
        $imageData = base64_decode(str_replace(['data:image/png;base64,', ' '], ['', '+'], $base64Image));

        // Use Storage facade to save the file in the public directory
        $filePath = "$folder/$fileName";
        Storage::put("$filePath", $imageData);

        return $filePath; // Return the file path as is
    }
}
