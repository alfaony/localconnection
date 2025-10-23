<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Schemas\RoleSchema;
use App\Schemas\ParamSchema;

use App\Models\TaskStatus;
use App\Models\TaskAssign;
use App\Models\Assign;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskAssignReport;

use Carbon\Carbon;

class TaskAssignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $taskStatuss = TaskStatus::get();
        $query = TaskAssign::query();

        if ($request->has('date') && $request->date != '') 
        {
            $query->whereDate('date', '=', $request->date);
        }

        if ($request->task == NULL || $request->task == 'today') 
        {
            $query->whereDate('date', '=', Carbon::now());
        }
    
        if ($request->has('status') && $request->status != '') {
            $query->whereHas('taskStatus', function ($q) use ($request) {
                $q->where('name', '=', $request->status);
            });
        }
    
        if ($request->has('user') && $request->user != '') {
            $query->whereHas('assign', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user . '%');
            });
        }
        $users = User::byCompany(Auth::user()->company_id)->byRole(RoleSchema::OB)->get();        
        $assigns = $query->byCompany(Auth::user()->company_id)->orderBy('date','desc')->paginate(10);
        return view('task_assign.index',compact('assigns','taskStatuss','users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $taskStatuss = TaskStatus::get();
        $tasks = Task::byCompany(Auth::user()->company_id)->get();
        $users = User::byCompany(Auth::user()->company_id)->byRole(RoleSchema::OB)->get();        
        return view('task_assign.create',compact('taskStatuss','users','tasks'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'task_id' => 'required|array',
            'task_id.*' => 'required|exists:tasks,id',
            'user_assign_task' => 'required|array',
            'user_assign_task.*' => 'required|exists:users,id'
        ]);

        DB::beginTransaction();
        try 
        {
            $task_ids = $request->post('task_id');
            $user_assign_tasks = $request->post('user_assign_task');

            foreach ($request->task_id as $key => $task) 
            {

                $taskAssign = new TaskAssign();
                $taskAssign->user_id = Auth::user()->id;
                $taskAssign->date = $request->post('date');
                $taskAssign->task_status_id = $request->post('task_status_id');
                $taskAssign->task_id = $task_ids[$key];
                $taskAssign->user_assign_id = $user_assign_tasks[$key];
                $taskAssign->save();
            }

            DB::commit();
            return redirect()->route('task-assign.index')->with('store', true);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error($th->getMessage());
            DB::rollback();
            return redirect()->route('task-assign.index')->with('store', false);
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TaskAssign  $taskAssign
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $taskAssign= TaskAssign::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        return view('task_assign.show',compact('taskAssign'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TaskAssign  $taskAssign
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $taskStatuss = TaskStatus::get();
        $tasks = Task::byCompany(Auth::user()->company_id)->get();
        $users = User::byCompany(Auth::user()->company_id)->byRole(RoleSchema::OB)->get();        
        $taskAssign= TaskAssign::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();

        return view('task_assign.edit',compact('taskStatuss','users','tasks','taskAssign'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TaskAssign  $taskAssign
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $request->validate([
            'date' => 'required|date',
            'task_id' => 'required|exists:tasks,id',
            'user_assign_task' => 'required|exists:users,id'
        ]);

        DB::beginTransaction();
        try 
        {
            $taskAssign = TaskAssign::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
            $taskAssign->user_id = Auth::user()->id;
            $taskAssign->date = $request->post('date');
            $taskAssign->task_status_id = $request->post('task_status_id');
            $taskAssign->task_id = $request->post('task_id');
            $taskAssign->user_assign_id = $request->post('user_assign_task');
            $taskAssign->save();

            DB::commit();
            return redirect()->route('task-assign.index')->with('update', true);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error($th->getMessage());
            DB::rollback();
            return redirect()->route('task-assign.index')->with('update', false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TaskAssign  $taskAssign
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $assign = TaskAssign::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $assign->delete();

        return redirect()->route('task-assign.index')->with('delete', true);
    }

    /**
     * Report From User Assigned
     */

     public function report(Request $request, $slug)
     {
        $validatedData = $request->validate([
            'note' => 'required|string', // Validasi untuk catatan
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048' // Validasi untuk foto, max 2MB
        ]);
    
        $inReview = TaskStatus::where('name',ParamSchema::INREVIEW)->firstOrFail();

        DB::beginTransaction();
        try {
            //code...
            $taskAssign = TaskAssign::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
            $taskAssign->task_status_id = $inReview->id;
            $taskAssign->save();
    
            $taskReport = new TaskAssignReport();
            $taskReport->note = $validatedData['note'];
            $taskReport->task_assign_id = $taskAssign->id;
            $taskReport->user_id = Auth::user()->id;
            // Menangani file foto jika diupload
            if ($request->hasFile('photo')) 
            {
                // Hapus file lama jika ada        
                $file = $request->file('photo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('task', $filename);
                $taskReport->picture = $filename;
            }
            $taskReport->save();
            DB::commit();
            return redirect()->route('task-assign.index')->with('report', true);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            Log::error($th->getMessage());
            return redirect()->route('task-assign.index')->with('report', false);
        }
     }

     /**
      * Approvement
      */

      public function approvement(Request $request, $slug)
      {
        $this->validate($request,
        [
            'status' => 'required|in:complete,not complete'
        ]);
        $status = TaskStatus::where('name',$request->status)->firstOrFail();

        $taskAssign = TaskAssign::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $taskAssign->task_status_id = $status->id;
        $taskAssign->save();

        return redirect()->route('task-assign.index')->with('report', true);
      }

}
