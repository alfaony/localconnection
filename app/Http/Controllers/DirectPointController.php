<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Models\DirectPoint;
use App\Models\Division;
use App\Models\User;
use App\Models\DivisionQuotaLock;
use App\Models\Inbox;
use App\Models\SettingCompany;

use App\Helpers\InboxHelper;
use App\Schemas\RoleSchema;

class DirectPointController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = DirectPoint::byCompany($user->company_id)
            ->with(['fromUser', 'toUser', 'division', 'approvedBy'])
            ->where(function($q) use ($user) {
                // 1. Direct Points dari divisi yang user ikuti
                $q->whereHas('division', function($divQuery) use ($user) {
                    $divQuery->whereHas('users', function($userQuery) use ($user) {
                        $userQuery->where('users.id', $user->id);
                    });
                })
                // 2. Direct Points yang user buat sendiri
                ->orWhere('from_user_id', $user->id)
                // 3. Direct Points yang dibuat untuk user
                ->orWhere('to_user_id', $user->id);
            });

        // Filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('division_id') && $request->division_id != '') {
            $query->where('division_id', $request->division_id);
        }

        if ($request->has('start_date') && $request->start_date != '' && 
            $request->has('end_date') && $request->end_date != '') {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $directPoints = $query->latest()->paginate(15);
        
        // Get divisions for filter - only user's divisions
        $divisions = $user->divisions;

        return view('direct_point.index', compact('directPoints', 'divisions'));
    }

    public function create()
    {
        // Get divisions that user has access to
        $user = Auth::user();
        
        if ($user->role->name == RoleSchema::ADMIN || $user->role->name == RoleSchema::ROOT) {
            $divisions = Division::byCompany($user->company_id)->get();
        } else {
            $divisions = $user->divisions;
        }

        $users = User::byCompany($user->company_id)
            ->where('id', '!=', $user->id)
            ->get();

        return view('direct_point.create', compact('divisions', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'division_id' => 'required|exists:divisions,id',
            'point' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $division = Division::findOrFail($request->division_id);

            // Determine auto-approval status
            $isAutoApproved = $this->checkAutoApproval($user, $division);
            
            // If auto-approved, allocate quota now
            $quotaLockId = null;
            if ($isAutoApproved) {
                $quotaLock = $this->getOrCreateQuotaLock($division);
                
                // Check quota availability for auto-approved
                $usedPoints = $this->getUsedPointsForLock($quotaLock);
                $remainingQuota = $quotaLock->locked_quota - $usedPoints;

                if ($request->point > $remainingQuota) {
                    return back()->withErrors([
                        'point' => "Quota divisi tidak mencukupi. Tersisa: {$remainingQuota} point"
                    ])->withInput();
                }
                
                $quotaLockId = $quotaLock->id;
            }

            $status = $isAutoApproved ? DirectPoint::STATUS_APPROVED : DirectPoint::STATUS_PENDING;

            // Create Direct Point
            // If pending: division_quota_lock_id = NULL (will be allocated on approval)
            // If auto-approved: division_quota_lock_id = current period lock
            $directPoint = DirectPoint::create([
                'from_user_id' => $user->id,
                'to_user_id' => $request->to_user_id,
                'division_id' => $division->id,
                'division_quota_lock_id' => $quotaLockId,
                'point' => $request->point,
                'approved_point' => $isAutoApproved ? $request->point : null, // Set approved_point for auto-approved
                'reason' => $request->reason,
                'status' => $status,
                'approved_by' => $isAutoApproved ? $user->id : null,
                'approved_at' => $isAutoApproved ? now() : null,
            ]);

            // Send inbox notifications
            $this->sendInboxNotifications($directPoint);

            DB::commit();

            $message = $isAutoApproved 
                ? 'Direct Point berhasil diberikan (auto-approved)' 
                : 'Direct Point berhasil dibuat dan menunggu persetujuan';

            return redirect()->route('direct-point.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DirectPoint Store Error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $directPoint = DirectPoint::with([
            'fromUser', 
            'toUser', 
            'division', 
            'approvedBy',
            // 'divisionQuotaLock'
        ])->findOrFail($id);

        // Check if user can approve
        $canApprove = $this->canUserApprove(Auth::user(), $directPoint);

        return view('direct_point.show', compact('directPoint', 'canApprove'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'approved_point' => 'required|integer|min:1',
        ]);

        $directPoint = DirectPoint::findOrFail($id);

        if (!$directPoint->isPending()) {
            return back()->withErrors(['error' => 'Direct Point sudah diproses']);
        }

        if (!$this->canUserApprove(Auth::user(), $directPoint)) {
            return back()->withErrors(['error' => 'Anda tidak memiliki akses untuk approve']);
        }

        DB::beginTransaction();

        try {
            $division = Division::findOrFail($directPoint->division_id);
            $approvedPoint = $request->approved_point;
            
            // Get quota lock for CURRENT period (approval period)
            $quotaLock = $this->getOrCreateQuotaLock($division);
            
            // Re-check quota availability at approval time with approved_point
            $usedPoints = $this->getUsedPointsForLock($quotaLock);
            $remainingQuota = $quotaLock->locked_quota - $usedPoints;

            if ($approvedPoint > $remainingQuota) {
                DB::rollBack();
                return back()->withErrors([
                    'error' => "Quota tidak mencukupi untuk approve Direct Point ini. Tersisa: {$remainingQuota} point, dibutuhkan: {$approvedPoint} point"
                ])->withInput();
            }

            // Allocate quota and approve
            $directPoint->update([
                'division_quota_lock_id' => $quotaLock->id, // Assign quota lock at approval
                'approved_point' => $approvedPoint, // Store approved point
                'status' => DirectPoint::STATUS_APPROVED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Send notification to recipient
            $message = $approvedPoint == $directPoint->point 
                ? "Direct Point sebesar {$approvedPoint} dari {$directPoint->fromUser->name} telah disetujui oleh " . Auth::user()->name
                : "Direct Point dari {$directPoint->fromUser->name} disetujui sebesar {$approvedPoint} point (diminta: {$directPoint->point}) oleh " . Auth::user()->name;
                
            $this->sentInbox(
                $directPoint->to_user_id,
                $message,
                route('direct-point.show', $directPoint->id)
            );

            DB::commit();

            return redirect()->route('direct-point.show', $directPoint->id)
                ->with('success', 'Direct Point berhasil disetujui');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DirectPoint Approve Error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $directPoint = DirectPoint::findOrFail($id);

        if (!$directPoint->isPending()) {
            return back()->withErrors(['error' => 'Direct Point sudah diproses']);
        }

        if (!$this->canUserApprove(Auth::user(), $directPoint)) {
            return back()->withErrors(['error' => 'Anda tidak memiliki akses untuk reject']);
        }

        DB::beginTransaction();

        try {
            $directPoint->update([
                'status' => DirectPoint::STATUS_REJECTED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            // Send notification to sender and recipient
            $this->sentInbox(
                $directPoint->from_user_id,
                "Direct Point sebesar {$directPoint->point} kepada {$directPoint->toUser->name} ditolak. Alasan: {$request->rejection_reason}",
                route('direct-point.show', $directPoint->id)
            );

            $this->sentInbox(
                $directPoint->to_user_id,
                "Direct Point sebesar {$directPoint->point} dari {$directPoint->fromUser->name} ditolak",
                route('direct-point.show', $directPoint->id)
            );

            DB::commit();

            return redirect()->route('direct-point.show', $directPoint->id)
                ->with('success', 'Direct Point berhasil ditolak');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DirectPoint Reject Error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    // ========== HELPER METHODS ==========

    public function checkQuota(Request $request)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'point' => 'nullable|integer|min:1',
        ]);

        $divisionId = $request->division_id;
        $point = $request->point ?? 0;

        // Get current period - SAME LOGIC AS getOrCreateQuotaLock
        $rangeStart = SettingCompany::byCompany(auth()->user()->company_id)
            ->where('field_title', 'range_start_date')
            ->value('field_value') ?? 1;

        $currentDate = now();
        
        // Calculate period month and year (MUST MATCH getOrCreateQuotaLock logic!)
        if ($currentDate->day >= $rangeStart) {
            $month = $currentDate->copy()->addMonth()->month;
            $year = $currentDate->copy()->addMonth()->year;
        } else {
            $month = $currentDate->month;
            $year = $currentDate->year;
        }

        // Get quota lock
        $quotaLock = DivisionQuotaLock::where('division_id', $divisionId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        // If no quota lock exists, return error with guidance
        if (!$quotaLock) {
            return response()->json([
                'success' => false,
                'error' => 'no_quota',
                'message' => 'Quota belum tersedia untuk divisi ini pada periode saat ini.',
                'guidance' => 'Silakan hubungi admin atau edit divisi untuk menambahkan quota di menu Divisi.'
            ], 422);
        }

        // Calculate used points - use approved_point if set, otherwise point
        $directPointsUsed = DirectPoint::where('division_quota_lock_id', $quotaLock->id)
            ->where('status', DirectPoint::STATUS_APPROVED)
            ->get()
            ->sum(function($dp) {
                return $dp->approved_point ?? $dp->point;
            });

        $dailyTasksUsed = \App\Models\DailyTask::where('division_quota_lock_id', $quotaLock->id)
            ->sum('point');

        $totalUsed = $directPointsUsed + $dailyTasksUsed;
        $remaining = $quotaLock->locked_quota - $totalUsed;
        $isSufficient = $remaining >= $point;

        return response()->json([
            'success' => true,
            'quota' => $quotaLock->locked_quota,
            'used' => $totalUsed,
            'task_used' => $dailyTasksUsed,
            'direct_point_used' => $directPointsUsed,
            'remaining' => $remaining,
            'is_sufficient' => $isSufficient,
        ]);
    }

    protected function getOrCreateQuotaLock(Division $division)
    {
        $setting = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value', 'field_title');
        $periodStartDay = $setting && $setting['range_start_date'] ? (int) $setting['range_start_date'] : 21;
        $now = Carbon::now();

        // Calculate period month and year (same logic as DailyTask)
        if ($now->day >= $periodStartDay) {
            $month = $now->copy()->addMonth()->month;
            $year = $now->copy()->addMonth()->year;
        } else {
            $month = $now->month;
            $year = $now->year;
        }

        $quotaLock = DivisionQuotaLock::firstOrCreate(
            [
                'division_id' => $division->id,
                'month' => $month,
                'year' => $year,
            ],
            [
                'locked_quota' => $division->point_quota_monthly,
            ]
        );

        return $quotaLock;
    }

    protected function getUsedPointsForLock(DivisionQuotaLock $quotaLock)
    {
        // Calculate used points from Direct Points (approved only) - use approved_point if set, otherwise point
        $directPointsUsed = DirectPoint::where('division_quota_lock_id', $quotaLock->id)
            ->where('status', DirectPoint::STATUS_APPROVED)
            ->get()
            ->sum(function($dp) {
                return $dp->approved_point ?? $dp->point;
            });

        // Calculate used points from DailyTasks
        $dailyTasksUsed = \App\Models\DailyTask::where('division_quota_lock_id', $quotaLock->id)
            ->sum('point');

        return $directPointsUsed + $dailyTasksUsed;
    }

    protected function checkAutoApproval(User $user, Division $division)
    {
        // Check if user is member of division with weekly_report_required = true
        return $user->divisions()
            ->wherePivot('weekly_report_required', true)
            ->where('divisions.id', $division->id)
            ->exists();
    }

    protected function canUserApprove(User $user, DirectPoint $directPoint)
    {
        // User can approve if they are member of division with weekly_report_required = true
        return $user->divisions()
            ->wherePivot('weekly_report_required', true)
            ->where('divisions.id', $directPoint->division_id)
            ->exists();
    }

    protected function sendInboxNotifications(DirectPoint $directPoint)
    {
        // 1. Always send to recipient
        $this->sentInbox(
            $directPoint->to_user_id,
            "Anda menerima {$directPoint->point} point dari {$directPoint->fromUser->name} menggunakan quota divisi {$directPoint->division->name}",
            route('direct-point.show', $directPoint->id)
        );

        // 2. If pending, send to approvers
        if ($directPoint->isPending()) {
            $approvers = User::whereHas('divisions', function ($query) use ($directPoint) {
                $query->where('divisions.id', $directPoint->division_id)
                    ->where('division_user.weekly_report_required', true);
            })->get();

            foreach ($approvers as $approver) {
                $this->sentInbox(
                    $approver->id,
                    "{$directPoint->fromUser->name} ingin memberikan {$directPoint->point} point kepada {$directPoint->toUser->name} menggunakan quota divisi {$directPoint->division->name}. Menunggu persetujuan Anda.",
                    route('direct-point.show', $directPoint->id)
                );
            }
        }
    }

    protected function sentInbox($to, $message, $directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $to,
            Auth::id(),
            $message,
            $directUrl,
        );
    }
}
