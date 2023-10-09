<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\Project;
use App\Models\Suplier;
use App\Models\Manager;
use App\Models\Employee;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $totalActiveProjects = Project::byDateRange()->count() ?? 0;

        $activeProjectsBudget = Project::byDateRange()->sum('budget') ?? 0;

        $totalPurchaseBudget = Suplier::byProjectActive()->sum('total_price') ?? 0;

        $activeEmployeeBudget = Manager::byProjectActive()->sum('total_job') ?? 0;

        $totalActiveWorkers = Employee::byActiveEmployee()->count() ?? 0;

        return view('home',compact('totalActiveProjects','activeProjectsBudget','totalPurchaseBudget','activeEmployeeBudget','totalActiveWorkers'));
    }
}
