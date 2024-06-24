<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Schemas\RoleSchema;
use App\Models\Attendance;
use App\Models\SettingCompany;
use App\Models\ScheduleOb;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    // protected function authenticated(Request $request, $user)
    // {
    //     if ($user->role->name == RoleSchema::OB) {
    //         $now = Carbon::now();
    //         $attended = Attendance::where('date',$now->toDateString())->where('user_id',$user->id)->first();
    //         if(!$attended)
    //         {
    //             $smtpConfig = SettingCompany::byCompany($user->company_id)->get()->pluck('field_value','field_title');
                
    
    //             $clock_in = $smtpConfig['clock_in'] ?? $now->toTimeString();
    //             $late_point = $smtpConfig['late_point'] ?? 0;
    //             $on_time_poin = $smtpConfig['on_time_poin'] ?? 0;
    
    //             $attendanceTime = Carbon::createFromTimeString($clock_in);
    //             $points = $now->gt($attendanceTime) ? $late_point : $on_time_poin;
    
    
    //             $attendance = new Attendance();
    //             $attendance->user_id = $user->id;
    //             $attendance->date = $now->toDateString();
    //             $attendance->clock_in = $now->toTimeString();
    //             $attendance->point = $points;
    //             $attendance->save();

    //             session()->flash('status', 'Attendance recorded');
    //         }


    //     }
    //     return redirect()->intended($this->redirectPath());
    // }
    protected function authenticated(Request $request, $user)
    {
        if ($user->role->name == RoleSchema::OB) {
            $now = Carbon::now();

            // Check if there's a shift for today
            $shift = ScheduleOb::where('user_id', $user->id)
                ->where('date', $now->toDateString())
                ->first();

            if ($shift) 
            {
                // Check if the user has already attended today
                $attended = Attendance::where('date', $now->toDateString())
                    ->where('user_id', $user->id)
                    ->first();

                if (!$attended) {
                    $smtpConfig = SettingCompany::byCompany($user->company_id)
                        ->get()
                        ->pluck('field_value', 'field_title');

                    $clock_in = $shift->shiftingOb->clock_in ?? $smtpConfig['clock_in'];
                    $late_point = $smtpConfig['late_point'] ?? 0;
                    $on_time_poin = $smtpConfig['on_time_poin'] ?? 0;

                    $attendanceTime = Carbon::createFromTimeString($clock_in);
                    $points = $now->gt($attendanceTime) ? $late_point : $on_time_poin;
                    

                    $attendance = new Attendance();
                    $attendance->user_id = $user->id;
                    $attendance->date = $now->toDateString();
                    $attendance->clock_in = $now->toTimeString();
                    $attendance->point = $points;
                    $attendance->save();

                    session()->flash('status', 'Attendance recorded');
                }
            } else {
                // If no shift exists, logout the user and show a message
                Auth::logout();
                return redirect('/login')->with('error', 'Tidak ada shift hari ini.');
            }
        }

        return redirect()->intended($this->redirectPath());
    }


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
