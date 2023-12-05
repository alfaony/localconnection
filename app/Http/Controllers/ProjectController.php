<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\ProjectRequest;

use App\Models\Project;
use App\Models\WorkOrder;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $project = Project::byCompany(Auth::user()->company_id)->where('title','like', '%' . $request->get('project') . '%')
        ->OrderBy('created_at','asc')->paginate(10);

        $totalProject = count(Project::byCompany(Auth::user()->company_id)->get());
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
        $totalProject = count(Project::byCompany(Auth::user()->company_id)->get());
        $projectEdit = Project::where('slug', $slug)->firstOrFail();
        $project = Project::byCompany(Auth::user()->company_id)->OrderBy('created_at','asc')->paginate(10);
        // $workOrder = WorkOrder::all();
        $workOrder = WorkOrder::whereDoesntHave('project')
        ->byCompany(Auth::user()->company_id)
        ->orWhere('id', $projectEdit->work_order_id)
        ->orderBy('created_at','desc')
        ->get();
    
        // Rest of your code for editing the project...

        return view('project.index', compact('projectEdit','project','totalProject', 'workOrder'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(ProjectRequest $request, Project $project)
    {
        $project->user_id = Auth::user()->id;
        $project->title = $request->post('title');
        $project->budget = $request->post('budget');
        $project->work_order_id = $request->post('work_order');
        $project->start_date = $request->post('start_date');
        $project->end_date = $request->post('end_date');
        $project->description = $request->post('description');
        $project->save();

        return redirect()->to(route('project.index'))->with('update',true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->back()->with('delete',true);
    }
}
