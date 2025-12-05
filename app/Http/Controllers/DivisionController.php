<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Division;
use App\Models\Objective;
use App\Models\ObjectiveKeyResult;
use App\Models\DailyTask;
use App\Models\DivisionQuotaLock;
use App\Models\SettingCompany;

use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;
use App\Helpers\Access;

use Carbon\Carbon;

class DivisionController extends Controller
{
    public function index()
    {
        // Mengambil user yang sedang login
        $user = Auth::user();
        
        // Menggunakan relasi untuk mengambil divisi yang terkait dengan user tersebut
        if($user->role->name == RoleSchema::ADMIN || $user->role->name == RoleSchema::ROOT || ( Access::can('create','divisions') && Access::can('store','divisions')))
        {
            $divisions = Division::byCompany($user->company_id)->paginate(10);
        }
        else
        {
            $divisions = $user->divisions()->paginate(10);
        }
        
        // Mengirim data divisi ke view
        return view('division.index', compact('divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'point_quota_monthly' => 'required|integer|min:0|max:200',
        ]);

        DB::beginTransaction();

        try {
            $division = Division::create([
                'user_id' => Auth::user()->id,
                'name' => $request->name,
                'point_quota_monthly' => $request->point_quota_monthly,
            ]);

            // Create DivisionQuotaLock untuk bulan ini
            if($request->point_quota_monthly > 0)
            {
                $this->ensureQuotaLockFor($division);
            }

            DB::commit();

            return back()->with('success', 'Divisi berhasil ditambahkan.');
        } catch (\Throwable $e) {
            // dd($e);
            Log::error($e);
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        $statusSelect = config('custom.statusSelect');
        return view('division.create', compact('statusSelect'));
    }

    public function show($slug)
    {
        $division = Division::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();

        $today = \Carbon\Carbon::today();
    
        // Get users with their overdue tasks
        $overdueTasks = User::byCompany(Auth::user()->company_id)
        ->whereHas('divisions', function ($query) use ($division) {
            $query->where('divisions.id', $division->id);
        })
        ->withCount(['dailyTaskAssigns' => function($query) use ($today) 
        {
            $query->where('end_date', '<', $today);
            $query->whereHas('taskStatus', function ($query)
            {
                $query->where('name',ParamSchema::DOING)->orWhere('name',ParamSchema::INREVIEW)->orWhere('name',ParamSchema::TODO)->orWhere('name',ParamSchema::NOTCOMPLATE);
            });
        }])
        ->orderBy('daily_task_assigns_count', 'desc')
        ->get();

        
        // Get users with their tasks due today or upcoming
        $upcomingTasks = User::byCompany(Auth::user()->company_id)
        ->whereHas('divisions', function ($query) use ($division) {
            $query->where('divisions.id', $division->id);
        })
        ->withCount(['dailyTaskAssigns' => function($query) use ($today) {
            $query->where('end_date', '>=', $today);
            $query->whereHas('taskStatus', function ($query)
            {
                $query->where('name',ParamSchema::DOING)->orWhere('name',ParamSchema::INREVIEW)->orWhere('name',ParamSchema::TODO)->orWhere('name',ParamSchema::NOTCOMPLATE);
            });
        }])
        ->orderBy('daily_task_assigns_count', 'desc')
        ->get();
        return view('division.show', compact('overdueTasks', 'upcomingTasks', 'division'));
    }

    public function showDivision(Request $request, $slug)
    {
        $userFilter = $request->input('user');
        $statusFilter = $request->input('status');
        $search = $request->input('objective_name');

        $users = User::byCompany(Auth::user()->company_id)->get();
        $taskStatuss = TaskStatus::all();
        $division = Division::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $customFields = $division->objectives;

        $query = Objective::query();

        if ($userFilter) {
            if ($userFilter != ParamSchema::ALL) {
                $query->whereHas('assign', function ($q) use ($userFilter) {
                    $q->where('name', $userFilter);
                });
            }
        }

        if ($statusFilter) {
            $query->whereHas('taskStatus', function ($q) use ($statusFilter) {
                $q->where('name', $statusFilter);
            });
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $tasks = $query->where('division_id', $division->id)->with(['user', 'customFieldValues'])->paginate(10);

        return view('divisions.show_division', compact('tasks', 'customFields', 'division', 'users', 'taskStatuss'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'point_quota_monthly' => 'required|integer|min:0|max:200',
        ]);

        DB::beginTransaction();

        try {
            $division = Division::byCompany(Auth::user()->company_id)->where('slug', $id)->firstOrFail();
            $division->name = $request->name;

            if($division->point_quota_monthly != $request->point_quota_monthly)
            {
                $newQuota = (int) $request->point_quota_monthly;
                $check = $this->validateAndUpdateQuotaLock($division, $newQuota);
    
                $division->point_quota_monthly = $newQuota;
                $division->save();
            }

            DB::commit();
            return back()->with('success', 'Divisi berhasil diperbarui.');
        } catch (\Throwable $e) {
            // dd($e);
            Log::error($e);
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($slug)
    {
        $division = Division::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $division->delete();
        return redirect()->route('division.index')->with('success', 'Division deleted successfully.');
    }

    // Objective Custom Field
    public function objectiveStore(Request $request, $slug)
    {
        $request->validate([
            'objective_name' => 'required|string|max:255',
            'objective_value' => 'required|array',
            'objective_value.*' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $division = Division::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();

            $objective = Objective::create([
                'division_id' => $division->id,
                'name' => $request->objective_name,
            ]);

            foreach ($request->objective_value as $index => $value) {
                ObjectiveKeyResult::create([
                    'objective_id' => $objective->id,
                    'value' => $value,
                    'ordering' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Objective created successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('divisions.show', $slug)->with('error', 'An error occurred: ' . $th->getMessage());
        }
    }

    public function objectiveUpdate(Request $request, $id)
    {
        $request->validate([
            'objective_name' => 'required|string|max:255',
            'objective_value' => 'required|array',
            'objective_value.*' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $objective = Objective::findOrFail($id);
            $objective->update([
                'name' => $request->objective_name,
            ]);

            $existingValueIds = [];
            foreach ($request->objective_value as $index => $value) {
                if (isset($request->objective_value_id[$index])) {
                    $objectiveValue = ObjectiveKeyResult::findOrFail($request->objective_value_id[$index]);
                    $objectiveValue->update([
                        'value' => $value,
                        'ordering' => $index,
                    ]);
                    $existingValueIds[] = $objectiveValue->id;
                } else {
                    $newValue = ObjectiveKeyResult::create([
                        'objective_id' => $objective->id,
                        'value' => $value,
                        'ordering' => $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $existingValueIds[] = $newValue->id;
                }
            }

            $objective->values()->whereNotIn('id', $existingValueIds)->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Objective updated successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('divisions.show', $objective->division->slug)->with('error', 'An error occurred: ' . $th->getMessage());
        }
    }

    public function objectiveDestroy($id)
    {
        DB::beginTransaction();
        try {
            $objective = Objective::findOrFail($id);
            $divisionSlug = $objective->division->slug;
            $objective->values()->delete();
            $objective->delete();

            DB::commit();
            return redirect()->route('divisions.show', $divisionSlug)->with('success', 'Objective deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('divisions.show', $divisionSlug)->with('error', 'An error occurred: ' . $th->getMessage());
        }
    }

    public function getObjectiveField(Request $request, $divisionId)
    {
        $division = Division::byCompany(Auth::user()->company_id)->where('id', $divisionId)->firstOrFail();
        $objectives = $division->objectives;
        $selectedValues = [];
        $dailyTaskId = $request->dailyTaskId;
        $index = $request->index;

        if ($dailyTaskId) {
            $dailyTask = DailyTask::with('customFieldValues')->find($dailyTaskId);
            foreach ($dailyTask->customFieldValues as $value) {
                $selectedValues[$value->custom_field_id][] = $value->custom_field_value_id;
            }
        }

        return view('partials.objective-fields', compact('objectives', 'selectedValues', 'index'));
    }

    public function fetchusertask($userId,$filter)
    {
        $today = \Carbon\Carbon::today();
        
        $query = DailyTask::with('taskStatus')
            ->where('assignment_user_id', $userId)
            ->whereHas('taskStatus', function ($query) {
                $query->where('name', ParamSchema::DOING)
                    ->orWhere('name', ParamSchema::INREVIEW)
                    ->orWhere('name', ParamSchema::TODO)
                    ->orWhere('name', ParamSchema::NOTCOMPLATE);
            });

        if ($filter === 'overdue') {
            $query->where('end_date', '<', $today);
        } elseif ($filter === 'upcoming') {
            $query->where('end_date', '>=', $today);
        }
        
        $tasks = $query->get()->map(function ($task) {
            $url = NULL;
            if (Access::can('show', 'dailytasks')) {
                $url = route('dailytask.show', $task->slug);
            }
            
            $headName = $task->head ? "< ". Str::limit($task->head->name,40) : '';

            return [
                'is_overdue' => $task->isOverdue(),
                'name_show' => $task->nameShow.' '.$headName,
                'task_status' => $task->taskStatus,
                'date_show' => $task->date_show,
                'user_create' => $task->user ? $task->user->name : '',
                'url' => $url,
            ];
        });
        return response()->json($tasks);
    }

    public function ajaxDivisionTasks(Request $request, Division $division)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $lock = DivisionQuotaLock::where('division_id', $division->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$lock) return response()->json([
            'quota' => 0,
            'used' => 0,
            'remaining' => 0,
            'tasks' => []
        ]);

       $tasks = DailyTask::with(['user', 'assign']) // eager load relasi
        ->where('division_id', $division->id)
        ->where('division_quota_lock_id', $lock->id)
        ->get()
        ->map(function ($task) {
            return [
                'name' => $task->name,
                'point' => $task->point,
                'description' => $task->description,
                'created_at' => $task->created_at,
                'user_name' => optional($task->user)->name,
                'assign_name' => optional($task->assign)->name,
            ];
        });

        $used = $tasks->sum('point');

        return response()->json([
            'quota' => $lock->locked_quota,
            'used' => $used,
            'remaining' => $lock->locked_quota - $used,
            'tasks' => $tasks
        ]);
    }

    protected function ensureQuotaLockFor(Division $division)
    {
        $month = now()->month;
        $year = now()->year;

        $exists = DivisionQuotaLock::where('division_id', $division->id)
            ->where('month', $month)
            ->where('year', $year)
            ->exists();

        if (!$exists) {
            DivisionQuotaLock::create([
                'division_id' => $division->id,
                'month' => $month,
                'year' => $year,
                'locked_quota' => $division->point_quota_monthly,
            ]);
        }
    }

    protected function validateAndUpdateQuotaLock(Division $division, int $newQuota)
    {
        $setting = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
        $periodStartDay = $setting && $setting['range_start_date'] ? (int) $setting['range_start_date'] : 21;
        $now = Carbon::now();

        // Calculate period month and year
        if ($now->day >= $periodStartDay) {
            $month = $now->copy()->subMonth()->month;
            $year = $now->copy()->subMonth()->year;
        } else {
            $month = $now->month;
            $year = $now->year;
        }

        $quotaLock = DivisionQuotaLock::where('division_id', $division->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$quotaLock) {
            // Auto-buat kalau belum ada
            DivisionQuotaLock::create([
                'division_id' => $division->id,
                'month' => $month,
                'year' => $year,
                'locked_quota' => $newQuota,
            ]);
            return;
        }

        // Jika quota diturunkan, pastikan aman
        if ($newQuota < $quotaLock->locked_quota) {
            $usedPoints = DailyTask::where('division_quota_lock_id', $quotaLock->id)->sum('point');

            if ($newQuota < $usedPoints) {
                throw new \Exception("Kuota tidak bisa diturunkan ke $newQuota karena sudah terpakai $usedPoints poin.");
            }

            $quotaLock->locked_quota = $newQuota;
            $quotaLock->save();
        }

        // Jika quota dinaikkan, bebas update
        if ($newQuota > $quotaLock->locked_quota) {
            $quotaLock->locked_quota = $newQuota;
            $quotaLock->save();
        }
    }
}
