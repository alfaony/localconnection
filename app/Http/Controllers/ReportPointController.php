<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;

use App\Models\User;
use App\Models\TaskAssign;
use App\Models\Attendance;
use App\Models\TaskStatus;
use App\Models\Role;
use App\Models\SettingCompany;

use Carbon\Carbon;

class ReportPointController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('start_date') && $request->start_date != '' && $request->has('end_date') && $request->end_date != '' )
        {
            $startOfMonth = Carbon::parse($request->start_date);
            $endOfMonth = Carbon::parse($request->end_date);
        }else
        {
            $date = Carbon::now()->format('Y-m'); // Default to current month
            $startOfMonth = Carbon::parse($date)->startOfMonth();
            $endOfMonth = Carbon::parse($date)->endOfMonth();
        }
        
        $obId = Role::select('id')->where('name',RoleSchema::OB)->first()->id;

        $users = User::with(['taskAssigns' => function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('date', [$startOfMonth, $endOfMonth]);
            }, 'attendances' => function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('date', [$startOfMonth, $endOfMonth]);
            }])
            ->where('role_id',$obId)
            ->paginate(10);
            
        $complate = TaskStatus::select('id')->where('name',ParamSchema::COMPLATE)->firstOrFail()->id;
        $notComplate = TaskStatus::select('id')->where('name',ParamSchema::NOTCOMPLATE)->firstOrFail()->id;

        $reports = $users->map(function ($user) use ($startOfMonth, $endOfMonth, $complate, $notComplate) 
        {
            $onTimeAttendance = $user->attendances->where('point',ParamSchema::ZERO)->count();

            $completedTasks = $user->taskAssigns->where('task_status_id', '==', $complate)->count();
            $notCompletedTasks = $user->taskAssigns->where('task_status_id', '==', $notComplate)->count();
            $attendancePoints = $user->attendances->sum('point');
            $attendBonusPoints = $onTimeAttendance >= ParamSchema::ONEMONTH ? 100 : 0;
            $totalPoints = $completedTasks + $notCompletedTasks + $attendancePoints + $attendBonusPoints;
            
            $settingCompany = SettingCompany::byCompany($user->company_id)->get()->pluck('field_value','field_title');
            $convertionPoint = $totalPoints * $settingCompany['reward_point_conversion'];

            return [
                'Name' => $user->name,
                'Complete' => $completedTasks,
                'Not Complete' => $notCompletedTasks,
                'Attend Point' => $attendancePoints,
                'Attend Bonus Point' => $attendBonusPoints,
                'Total' => $totalPoints,
                'convertion_point' => $convertionPoint > 0 ? 'Rp. '.number_format($convertionPoint,0,',','.') : 'Rp. 0'
            ];
        });

        return view('report_point.index', compact('reports', 'users'));
    }
}
