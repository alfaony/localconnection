<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\Vision;

class ProjectDashboardController extends Controller
{
    public function projectdashboard()
    {
        $visions = Vision::where('company_id',Auth::user()->company_id)->with(['missions.objectives.keyResults.dailyTasks'])->get();
        return view('report_project_tree.index', compact('visions'));
    }
}
