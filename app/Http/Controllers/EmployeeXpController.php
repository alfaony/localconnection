<?php

namespace App\Http\Controllers;

use App\Models\EmployeeXpHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeXpController extends Controller
{
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

        return view('employee_xp.leaderboard', compact('users', 'myRank'));
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
