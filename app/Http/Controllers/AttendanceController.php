<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Schemas\RoleSchema;

use App\Models\Attendance;
use App\Models\User;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
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

        return view('attendance.index',compact('attendances', 'users'));
    }
}
