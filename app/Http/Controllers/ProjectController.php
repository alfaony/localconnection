<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\ProjectRequest;

use App\Models\Project;
use App\Models\WorkOrder;
use App\Models\Manager;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $order = 'desc'; if($request->order == 'asc') { $order = 'asc'; }

        $project = Project::byCompany(Auth::user()->company_id)
        ->where(function ($query) use ($request) {
            $searchTerm = '%' . $request->get('search') . '%';
            // Search in the manager's name
            $query->where('title', 'like', $searchTerm)
                // Or search in related project's title
                ->orWhereHas('workOrder', function ($query) use ($searchTerm) {
                    $query->where('number_result', 'like', $searchTerm);
                });
        })
        ->OrderBy('created_at',$order)->paginate(10);

        $totalProject = Project::byCompany(Auth::user()->company_id)->count();
        $workOrder = WorkOrder::whereDoesntHave('project')
        ->byCompany(Auth::user()->company_id)
        ->orderBy('created_at','desc')
        ->get();

        return view('project.index',compact('project','totalProject', 'workOrder'));
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProjectRequest $request)
    {

        $project = new Project();
        $project->user_id = Auth::user()->id;
        $project->title = $request->post('title');
        $project->budget = $request->post('budget');
        $project->work_order_id = $request->post('work_order');
        $project->start_date = $request->post('start_date');
        $project->end_date = $request->post('end_date');
        $project->description = $request->post('description');

        // recurring
        $project->recurring = $request->post('recurring') ? 1 : 0;

        // Alert
        $project->alert_expired = $request->post('alertCheckbox') ? 1 : 0;
        $project->alert_one_week = $request->post('one_week') ? 1 : 0;
        $project->alert_two_week = $request->post('two_week') ? 1 : 0;
        $project->alert_one_month = $request->post('one_month') ? 1 : 0;

        $project->save();

        return redirect()->back()->with('store',true);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $totalProject = Project::byCompany(Auth::user()->company_id)->count();
        $projectEdit = Project::where('slug', $slug)->firstOrFail();
        $project = Project::byCompany(Auth::user()->company_id)->OrderBy('created_at','asc')->paginate(10);
        // $workOrder = WorkOrder::all();
        $workOrder = WorkOrder::whereDoesntHave('project')
        ->byCompany(Auth::user()->company_id)
        ->orWhere('id', $projectEdit->work_order_id)
        ->orderBy('created_at','desc')
        ->get();
    

        $directManager = Manager::select('slug')->where('project_id',$projectEdit->id)->first();

        return view('project.index', compact('projectEdit','project','totalProject', 'workOrder' ,'directManager'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $projectEdit = Project::where('slug', $slug)->firstOrFail();
        $workOrder = $projectEdit->workOrder;


        return view('project.show', compact('projectEdit', 'workOrder'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(ProjectRequest $request, $slug)
    {
        $project = Project::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $project->user_id = Auth::user()->id;
        $project->title = $request->post('title');
        $project->budget = $request->post('budget');
        $project->work_order_id = $request->post('work_order');
        $project->start_date = $request->post('start_date');
        $project->end_date = $request->post('end_date');
        $project->description = $request->post('description');

        // recurring
        $project->recurring = $request->post('recurring') ? 1 : 0;

        // Alert
        $project->alert_expired = $request->post('alertCheckbox') ? 1 : 0;
        $project->alert_one_week = $request->post('one_week') ? 1 : 0;
        $project->alert_two_week = $request->post('two_week') ? 1 : 0;
        $project->alert_one_month = $request->post('one_month') ? 1 : 0;
        
        $project->save();

        return redirect()->to(route('project.index'))->with('update',true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $project = Project::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $project->delete();
        return redirect()->back()->with('delete',true);
    }
}
