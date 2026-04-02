<?php

namespace App\Http\Controllers;

use App\Models\EmployeeXpHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class EmployeeXpController extends Controller
{
    /**
     * Master admin: semua riwayat XP perusahaan.
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $query = EmployeeXpHistory::byCompany($companyId)
            ->with('user:id,name')
            ->latest();

        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        if ($request->type === 'reward') {
            $query->where('xp', '>', 0);
        } elseif ($request->type === 'penalty') {
            $query->where('xp', '<', 0);
        }

        if ($request->filled('source_type')) {
            $query->where('source_type', $request->source_type);
        }

        $histories = $query->paginate(25)->withQueryString();

        $users = User::byCompany($companyId)->isActive()
            ->orderBy('name')->select('id', 'name', 'total_xp')->get();

        $sourceTypes = EmployeeXpHistory::byCompany($companyId)
            ->distinct()->pluck('source_type')->sort()->values();

        $stats = [
            'total_xp'    => EmployeeXpHistory::byCompany($companyId)->where('xp', '>', 0)->sum('xp'),
            'total_penalty'=> EmployeeXpHistory::byCompany($companyId)->where('xp', '<', 0)->sum('xp'),
            'total_txn'   => EmployeeXpHistory::byCompany($companyId)->count(),
            'users_with_xp'=> User::byCompany($companyId)->where('total_xp', '>', 0)->count(),
        ];

        return view('employee_xp.index', compact('histories', 'users', 'sourceTypes', 'stats'));
    }

    /**
     * Manual award / deduct XP ke seorang employee.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|uuid',
            'xp'          => 'required|integer|not_in:0|min:-9999|max:9999',
            'description' => 'required|string|max:255',
        ]);

        $companyId = Auth::user()->company_id;
        $user = User::byCompany($companyId)->findOrFail($request->user_id);

        DB::transaction(function () use ($request, $user, $companyId) {
            EmployeeXpHistory::create([
                'id'          => Uuid::uuid4()->toString(),
                'user_id'     => $user->id,
                'company_id'  => $companyId,
                'xp'          => $request->xp,
                'source_type' => 'Manual',
                'source_id'   => null,
                'description' => $request->description,
            ]);

            if ($request->xp > 0) {
                $user->increment('total_xp', $request->xp);
            } else {
                $user->decrement('total_xp', abs($request->xp));
            }
        });

        return back()->with('success', 'XP berhasil diberikan kepada ' . $user->name);
    }

    /**
     * Hapus entri riwayat XP dan kembalikan total_xp user.
     */
    public function destroy(EmployeeXpHistory $employeeXp)
    {
        $companyId = Auth::user()->company_id;

        if ($employeeXp->company_id !== $companyId) {
            abort(403);
        }

        DB::transaction(function () use ($employeeXp) {
            $user = User::find($employeeXp->user_id);
            if ($user) {
                // Revert: jika xp positif, kurangi; jika negatif, tambah
                if ($employeeXp->xp > 0) {
                    $user->decrement('total_xp', $employeeXp->xp);
                } else {
                    $user->increment('total_xp', abs($employeeXp->xp));
                }
            }
            $employeeXp->delete();
        });

        return back()->with('success', 'Riwayat XP dihapus dan poin dikembalikan.');
    }

    /**
     * History XP milik user yang sedang login.
     */
    public function myHistory(Request $request)
    {
        $user = Auth::user();

        $histories = EmployeeXpHistory::forUser($user->id)
            ->byCompany($user->company_id)
            ->latest()
            ->paginate(20);

        $totalXp = $user->total_xp ?? 0;

        return view('employee_xp.history', compact('histories', 'totalXp'));
    }

    /**
     * Leaderboard — ranking user berdasarkan total_xp.
     */
    public function leaderboard(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $canAccessUserHistory = \App\Helpers\Access::can('userHistory','employee_xps');

        $users = User::byCompany($companyId)
            ->isActive()
            ->where('total_xp', '>', 0)
            ->orderBy('total_xp', 'desc')
            ->select('id', 'name', 'total_xp', 'avatar', 'slug')
            ->paginate(20);

        $myRank = User::byCompany($companyId)
            ->isActive()
            ->where('total_xp', '>', 0)
            ->where('total_xp', '>', Auth::user()->total_xp)
            ->count() + 1;

        return view('employee_xp.leaderboard', compact('users', 'myRank','canAccessUserHistory'));
    }

    /**
     * History XP seorang user (untuk admin/manajer).
     */
    public function userHistory(Request $request, $userId)
    {
        $user = User::byCompany(Auth::user()->company_id)->findOrFail($userId);

        $histories = EmployeeXpHistory::forUser($userId)
            ->byCompany(Auth::user()->company_id)
            ->latest()
            ->paginate(20);

        $totalXp = $user->total_xp ?? 0;

        return view('employee_xp.history', compact('histories', 'totalXp', 'user'));
    }
}
