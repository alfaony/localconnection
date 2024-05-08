<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $taskTypes = TaskType::get(); 
        $order = 'desc'; if($request->order == 'asc') { $order = 'asc'; }
        $tasks = Task::byCompany(Auth::user()->company_id)->where('name','like', '%' . $request->get('task') . '%')
        ->OrderBy('created_at',$order)->paginate(10);

        return view('task.index',compact('taskTypes','tasks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_type_id' => 'required|exists:task_types,id',
            'name' => 'required|string|max:255',
            'point' => 'required|integer|min:1'
        ]);

        $task = new Task();
        $task->task_type_id = $validated['task_type_id'];
        $task->name = $validated['name'];
        $task->point = $validated['point'];
        $task->user_id = Auth::user()->id;
        $task->save();

        return redirect()->route('task.index')->with('store', true);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        // $task = Task::where('slug',$slug)->first();
        // $taskTypes = TaskType::get(); 
        // $tasks = Task::byCompany(Auth::user()->company_id)->paginate(10);

        // return view('task.index',compact('taskTypes','tasks','task'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $task = Task::where('slug',$slug)->first();
        $taskTypes = TaskType::get(); 
        $tasks = Task::byCompany(Auth::user()->company_id)->paginate(10);

        return view('task.index',compact('taskTypes','tasks','task'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $validated = $request->validate([
            'task_type_id' => 'required|exists:task_types,id',
            'name' => 'required|string|max:255',
            'point' => 'required|integer|min:1'
        ]);

        $task = Task::where('slug',$slug)->first();
        $task->task_type_id = $validated['task_type_id'];
        $task->name = $validated['name'];
        $task->point = $validated['point'];
        $task->user_id = Auth::user()->id;
        $task->save();

        return redirect()->route('task.index')->with('update', true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $task = Task::where('slug',$slug)->first();
        $task->delete();

        return redirect()->route('task.index')->with('delete', true);
    }
}
