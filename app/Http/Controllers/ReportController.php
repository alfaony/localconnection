<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $project = Project::byCompany(Auth::user()->company_id)->bySearchReport($request->get('project'),$request->get('start_date'),$request->get('end_date'))->paginate(10);
        return view('report.index',compact('project'));
    }
}
