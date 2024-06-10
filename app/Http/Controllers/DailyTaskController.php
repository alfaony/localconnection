<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Http\Requests\DailyTaskStoreRequest;
use App\Http\Requests\DailyTaskRequest;
use App\Http\Requests\DailyTaskSubTaskRequest;

use Carbon\Carbon;
use App\Helpers\Access;
use App\Schemas\ParamSchema;

use App\Models\User;
use App\Models\DailyTask;
use App\Models\DailyTaskCategory;
use App\Models\DailyTaskType;
use App\Models\TaskStatus;
use App\Models\DailyTaskMedia;
use App\Models\DailyTaskExtend;
use App\Models\DailyTaskMessage;
use App\Models\DailyTaskProject;
use App\Models\DailyTaskCustomFieldValue;
use App\Models\Objective;
use App\Models\DailyTaskStatusRecord;



class DailyTaskController extends Controller
{
    public function index(Request $request)
    {
        // Ambil user ID dari autentikasi
        $userId = Auth::user()->id;

        // Ambil filter dari request
        $taskFilter = $request->input('task') ?? 'today';
        $userFilter = $request->input('user');
        $statusFilter = $request->input('status');
        $dateFilter = $request->input('date');
        $search = $request->input('search');

        // Query dasar untuk tugas harian berdasarkan user ID
        $query = DailyTask::orderBy('created_at', 'desc');

        if(!$statusFilter && !$search && $taskFilter != 'all')
        {
            $query->whereHas('taskStatus', function ($query)
            {
                $query->where(function($query) 
                {
                    $query->where('name',ParamSchema::DOING)->orWhere('name',ParamSchema::INREVIEW)->orWhere('name',ParamSchema::TODO)->orWhere('name',ParamSchema::NOTCOMPLATE);
                });
                    
            })
            ;
        }
        // Filter berdasarkan task
        if ($taskFilter) {
            switch ($taskFilter) 
            {
                case 'overdue':
                    // $query->where('end_date', '<', Carbon::now());
                    $query->whereDate('start_date', '<', now())->whereDate('end_date', '<', now());

                    break;
                case 'today':
                    $query->whereDate('start_date', '<=', now())->whereDate('end_date', '>=', now());
                    break;
                case 'upcoming':
                    $query->where('start_date', '>', now());
                    break;
            }
        }

        // Filter berdasarkan user
        if ($userFilter) 
        {   
            if($userFilter != ParamSchema::ALL)
            {
                $query->whereHas('assign', function ($q) use ($userFilter) 
                {
                    $q->where('name', $userFilter);
                });
            }
        }
        else
        {
            $query->UserTasks($userId);
        }

        // Filter berdasarkan status
        if ($statusFilter) 
        {
            $query->whereHas('taskStatus', function ($q) use ($statusFilter) {
                $q->where('name', $statusFilter);
            });
        }

        // Filter berdasarkan tanggal
        if ($dateFilter) {
            $query->whereDate('created_at', $dateFilter);
        }
        
        if ($search) 
        {
            $query->where('name', 'like', "%{$search}%"); // Add other fields as necessary
        }
        // Paginate hasil query
        $dailyTasksTes = $query->get();

        // dd($dailyTasksTes);
        $dailyTasks = $query->paginate(10);

        // Ambil data lain yang diperlukan untuk form
        $taskTimeFrame = [
            'overdue' => 'Overdue',
            'today' => 'Today',
            'upcoming' => 'Upcoming'
        ];
        $users = User::byCompany(Auth::user()->company_id)->get(); // Ambil semua user, bisa disesuaikan
        $taskStatuss = TaskStatus::all(); // Ambil semua status tugas

        // Kembalikan view dengan data
        return view('dailytask.index', compact('dailyTasks', 'taskTimeFrame', 'users', 'taskStatuss'));
    }

    public function create()
    {
        $users = User::byCompany(Auth::user()->company_id)->get();
        $categories = DailyTaskCategory::byCompany(Auth::user()->company_id)->get();
        $childTasks = DailyTask::byCompany(Auth::user()->company_id)->get();
        $types = DailyTaskType::get();
        $projects = DailyTaskProject::byCompany(Auth::user()->company_id)->get();
        $objectives = Objective::byCompany(Auth::user()->company_id)->get();
        $user = Auth::user(); // Get the current authenticated user
        $divisionIds = $user->divisions->pluck('id');

        if ($divisionIds->isEmpty()) {
            // Handle the case where the user does not belong to any divisions
            // You can return an empty collection or a message, or redirect
            return redirect()->route('dailytask.index')->with('error', 'Anda tidak tergabung dalam divisi manapun. Hubungi admin atau manager Anda.');
        } else {
            // Proceed with fetching objectives related to the user's divisions
            $objectives = Objective::whereHas('division', function ($query) use ($divisionIds) {
                $query->whereIn('id', $divisionIds);
            })->get();
        }


        return view('dailytask.create',compact('categories', 'types', 'users', 'childTasks', 'projects', 'objectives'));
    }

    public function store(DailyTaskStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            
            $startDates = $request->start_date;
            $endDates = $request->end_date;
            $assignmentUserIds = $request->assignment_user_id;
            $categoryIds = $request->category_id;
            $typeIds = $request->type_id;
            $projectIds = $request->project_id;
            $dataProjects = $request->data_project_id;
            $names = $request->name;
            $descriptions = $request->description ?? [];

        $objectives = $request->objective ?? [];

            $doing = TaskStatus::where('name',ParamSchema::TODO)->firstOrFail();

            
            for ($i = 0; $i < count($names); $i++)
            {
                $dailyTask = new DailyTask();
                $dailyTask->user_id = Auth::user()->id;
                $dailyTask->task_status_id = $doing->id;
                $dailyTask->start_date = $startDates[$i];
                $dailyTask->end_date = $endDates[$i];
                $dailyTask->assignment_user_id = $assignmentUserIds[$i];
                $dailyTask->daily_task_category_id = $this->manageCategory($categoryIds[$i]);
                $dailyTask->daily_task_type_id = $typeIds[$i];
                $dailyTask->project_id = $dataProjects[$i];
                $dailyTask->daily_task_project_id = $projectIds[$i] ?? NULL;
                $dailyTask->name = $names[$i];
                $dailyTask->description = $descriptions[$i] ?? null;
                $dailyTask->point = 0; // Assuming default value is 0
                $dailyTask->objective_id = $objectives[$i] ?? NULL;
                $dailyTask->save();

                // Menyimpan custom_field
                if (isset($request->custom_field_values)) {
                    foreach ($request->custom_field_values as $customFieldId => $customFieldValueId) {
                        if(is_array($customFieldValueId))
                        {
                            // 
                            foreach($customFieldValueId as $valueId)
                            {
                                DailyTaskCustomFieldValue::create([
                                    'daily_task_id' => $dailyTask->id,
                                    'custom_field_id' => $customFieldId,
                                    'custom_field_value_id' => $valueId,
                                ]);
                            }
                        }else{
                            DailyTaskCustomFieldValue::create([
                                'daily_task_id' => $dailyTask->id,
                                'custom_field_id' => $customFieldId,
                                'custom_field_value_id' => $customFieldValueId,
                            ]);
                        }
                    }
                }

                $keyResults = $request->input('key_result_' . $i);
                if (!empty($keyResults)) 
                {
                    $dailyTask->keyResults()->attach($keyResults);
                }

                $this->message($dailyTask->id,'create',' Membuat Tugas '.$dailyTask->name);
                $this->statusrecord($dailyTask, $doing);

            }

            
            DB::commit();
            return redirect()->route('dailytask.index')->with('store', true);
        } catch (\Throwable $th) {

            // dd($th);
            Log::error($th->getMessage());
            DB::rollback();

            return redirect()->route('dailytask.index');
        }

        
    }

    public function show($slug)
    {
        $dailytask = DailyTask::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $doing = TaskStatus::where('name',ParamSchema::TODO)->firstOrFail();

        $approvement = TaskStatus::where('name',ParamSchema::COMPLATE)->orWhere('name',ParamSchema::NOTCOMPLATE)->get();

        $showProject = Access::can('showproject','daily_task_projects');

        $users = User::byCompany(Auth::user()->company_id)->get();
        $subTasks = DailyTask::byCompany(Auth::user()->company_id)->where('child_daily_task_id',$dailytask->id)->orderBy('created_at','desc')->get();
        $types = DailyTaskType::get();
        $categories = DailyTaskCategory::byCompany(Auth::user()->company_id)->get();

        
        return view('dailytask.show', compact('dailytask', 'users', 'types', 'categories', 'subTasks', 'showProject', 'doing','approvement'));
    }

    public function edit($slug)
    {   
        $dailytask = DailyTask::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $users = User::byCompany(Auth::user()->company_id)->get();
        $categories = DailyTaskCategory::byCompany(Auth::user()->company_id)->get();
        $childTasks = DailyTask::byCompany(Auth::user()->company_id)->get();
        $types = DailyTaskType::get();
        $projects = DailyTaskProject::byCompany(Auth::user()->company_id)->get();
        $user = Auth::user(); // Get the current authenticated user
        $divisionIds = $user->divisions->pluck('id');

        if ($divisionIds->isEmpty()) {
            // Handle the case where the user does not belong to any divisions
            // You can return an empty collection or a message, or redirect
            return redirect()->route('dailytask.index')->with('error', 'Anda tidak tergabung dalam divisi manapun. Hubungi admin atau manager Anda.');
        } else {
            // Proceed with fetching objectives related to the user's divisions
            $objectives = Objective::whereHas('division', function ($query) use ($divisionIds) {
                $query->whereIn('id', $divisionIds);
            })->get();
        }


        return view('dailytask.edit',compact('categories', 'types', 'users', 'childTasks', 'dailytask', 'projects','objectives'));

    }

    public function update(DailyTaskRequest $request, $slug)
    {
        DB::beginTransaction();
        try {
            $dailyTask = DailyTask::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
            
            $dailyTask->start_date = $request->start_date;
            $dailyTask->end_date = $request->end_date;
            $dailyTask->assignment_user_id = $request->assignment_user_id;
            $dailyTask->daily_task_category_id = $this->manageCategory($request->category_id);
            $dailyTask->daily_task_type_id = $request->type_id;
            $dailyTask->point = $request->point ?? 0;
            $dailyTask->name = $request->name;
            $dailyTask->description = $request->description;
            $dailyTask->daily_task_project_id = $request->project_id ?? NULL ;
            $dailyTask->project_id = $request->data_project_id[0] ?? NULL ;
            $dailyTask->daily_task_category_id = $request->category_id;
            $dailyTask->objective_id = $request->objective;

            $dailyTask->save();
    
            $dailyTask->customFieldValues()->delete();
    
            // Menyimpan custom_field
            if (isset($request->custom_field_values)) {
                // dd("here");
                foreach ($request->custom_field_values as $customFieldId => $customFieldValueId) {
                    if(is_array($customFieldValueId))
                    {
                        // 
                        foreach($customFieldValueId as $valueId)
                        {
                            DailyTaskCustomFieldValue::create([
                                'daily_task_id' => $dailyTask->id,
                                'custom_field_id' => $customFieldId,
                                'custom_field_value_id' => $valueId,
                            ]);
                        }
                    }else{
                        DailyTaskCustomFieldValue::create([
                            'daily_task_id' => $dailyTask->id,
                            'custom_field_id' => $customFieldId,
                            'custom_field_value_id' => $customFieldValueId,
                        ]);
                    }
                }
            }

            $keyResults = $request->input('key_result_0');
            $dailyTask->keyResults()->sync($keyResults);

            $this->message($dailyTask->id,'edit','Mengubah Task '.$dailyTask->name);

            DB::commit();
            return redirect()->route('dailytask.index')->with('update', true);
        } catch (\Throwable $th) {
            // dd($th); 
            DB::rollback();
            Log::error($th->getMessage());

            return redirect()->route('dailytask.index')->with('update', false);

        }
    }

    public function destroy($slug)
    {
        $dailytask = DailyTask::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $dailytask->delete();

        return redirect()->route('dailytask.index')->with('delete',true) ;
    }

    public function report(Request $request, $slug)
    {
        $request->validate([
            'note' => 'required|string',
            'media.*' => 'nullable|file|max:10240'
        ]);

        DB::beginTransaction();
        try {
            $dailytask = DailyTask::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            $inReview = TaskStatus::where('name', ParamSchema::INREVIEW)->firstOrFail();

            $dailytask->report_note = $request->note;
            $dailytask->task_status_id = $inReview->id;

            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $timestamp = time();
                    $randomString = rand(100, 999);
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $fileName = $originalName . '_' . $timestamp . '_' . $randomString . '.' . $extension;

                    $path = $file->storeAs('media', $fileName, 'public');
                    $mediaType = $file->getClientMimeType();

                    DailyTaskMedia::create([
                        'daily_task_id' => $dailytask->id,
                        'file_path' => $path,
                        'file_type' => $mediaType,
                    ]);
                }
            }

            $submitDate = Carbon::now();
            $dailytask->submit = $submitDate;

            // $startDate = Carbon::parse($dailytask->start_date)->startOfDay();
            $endDate = Carbon::parse($dailytask->end_date)->endOfDay();
            $submitDate = Carbon::parse($dailytask->submit)->startOfDay();

            $dailytask->status_submit = ($submitDate->lessThanOrEqualTo($endDate)) ? ParamSchema::ONTIME : ParamSchema::LATE;

            $this->message($dailytask->id, 'report', ' Membuat Laporan Tugas ' . $dailytask->name);
            $this->statusrecord($dailytask, $inReview);
            $dailytask->save();

            if ($dailytask->type->name == ParamSchema::RECURRING) 
            {
                $this->projectRecurring($dailytask);
            }

            DB::commit();
            return redirect()->route('dailytask.show', $dailytask->slug)->with('report', true);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th->getMessage());
            return redirect()->route('dailytask.show', $dailytask->slug);
        }
    }

    public function updatemedia(Request $request, $slug)
    {
        $request->validate([
            'media.*' => 'required|file|max:2048'
        ]);
        
        DB::beginTransaction();
        try {
            //code...
            $dailytask = DailyTask::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();

            if ($request->hasFile('media'))
            {
                foreach ($request->file('media') as $file) {
                    // Generate a unique file name
                    $timestamp = time();
                    $randomString = rand(100, 999);
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $fileName = $originalName . '_' . $timestamp . '_' . $randomString . '.' . $extension;
        
                    // Store the file with the new name
                    $path = $file->storeAs('media', $fileName, 'public');
                    $mediaType = $file->getClientMimeType();
        
                    DailyTaskMedia::create([
                        'daily_task_id' => $dailytask->id,
                        'file_path' => $path,
                        'file_type' => $mediaType,
                    ]);
                }
            }

            DB::commit();
            $dailytask->save();
            return redirect()->route('dailytask.show', $dailytask->slug)->with('updatemedia', true);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollback();
            Log::error($th->getMessage());
            return redirect()->route('dailytask.show', $dailytask->slug);
        }

    }
    public function deletemedia($id)
    {
        $media = DailyTaskMedia::findOrFail($id);

        // Delete the file from storage
        Storage::disk('public')->delete($media->file_path);

        // Delete the record from the database
        $media->delete();

        return back()->with('deletemedia', true);
    }

    public function approvement(Request $request, $slug)
    {
        $request->validate(
        [
            'point' => 'nullable|integer',
            'task_status' => 'required|exists:task_statuses,id',
        ]);
        
        $taskStatuss = TaskStatus::find($request->task_status);

        DB::beginTransaction();
        $dailytask = DailyTask::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        try {
            //code...
            $dailytask->point = $request->point;
            $dailytask->task_status_id = $taskStatuss->id;
            $dailytask->approved = $taskStatuss->name == ParamSchema::COMPLATE ? TRUE : FALSE;
            
            $taskStatuss->name == ParamSchema::COMPLATE ? $this->message($dailytask->id,'approvement','Membuat Persetujuan Tugas '.$dailytask->name) : $this->message($dailytask->id,'reject','Membuat Penolakan Tugas '.$dailytask->name);
            $this->statusrecord($dailytask, $taskStatuss);

            if($taskStatuss->name == ParamSchema::NOTCOMPLATE)
            {
                $dailytask->report_note = NULL;
                $dailytask->submit = NULL;
                $dailytask->status_submit = NULL;
                

                // if ($dailytask->media()->exists()) 
                // {
                //     foreach ($dailytask->media as $media) {
                //         $media->delete(); // This will also remove the file from storage due to the boot method in the Media model
                //     }
                // }
            }

            $dailytask->save();

            DB::commit();
            return redirect()->route('dailytask.show', $dailytask->slug)->with('approvement', true);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th->getMessage());
            DB::rollback();
            return redirect()->route('dailytask.show', $dailytask->slug)->with('approvement', false);
        }
    }

    public function extend(Request $request, $slug)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        DB::beginTransaction();
        try {
            //code...
            $dailytask = DailyTask::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();

            $dailytask->start_date = $request->start_date;
            $dailytask->end_date = $request->end_date;
            $dailytask->save();


            // save record
            $extendNumber = DailyTaskExtend::where('daily_task_id',$dailytask->id)->orderBy('created_at','desc')->first() ? DailyTaskExtend::where('daily_task_id',$dailytask->id)->orderBy('created_at','desc')->first()->extend + 1 :  1;
    
            $extend = new DailyTaskExtend();
            $extend->extend = $extendNumber;

            $dailytask->extend()->save($extend);

            $this->message($dailytask->id,'extend',"extend ".$extendNumber." memperpanjang tugas ".$dailytask->name." menjadi ".Carbon::parse($dailytask->end_date)->format('d-m-Y'));
            DB::commit();
            return redirect()->route('dailytask.show', $dailytask->slug)->with('extend', true);

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            Log::error($th->getMessage());

            return redirect()->route('dailytask.show', $dailytask->slug)->with('extend', false);
        }
    }

    protected function manageCategory($category)
    {
        $categoryResult = DailyTaskCategory::ByCompany(Auth::user()->company_id)->firstOrCreate(
            ['name' => trim($category)],
            ['user_id' => Auth::user()->id]
        );
        return $categoryResult->id;
    }

    public function comment(Request $request, $slug)
    {
        $request->validate([
            'message' =>  'required|string',
            'file_path' => 'nullable|file'
        ]);

        $dailytask = DailyTask::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();

        $path = NULL;
        if ($request->hasFile('file_path')) 
        {
            $file = $request->file('file_path');
            // Generate a unique file name
            $timestamp = time();
            $randomString = rand(100, 999);
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileName = $originalName . '_' . $timestamp . '_' . $randomString . '.' . $extension;

            // Store the file with the new name
            $path = $file->storeAs('comment', $fileName, 'public');
        }

        $this->message($dailytask->id,'comment',$request->message,$path);

        return redirect()->route('dailytask.show', $dailytask->slug)->with('comment', true);
    }

    public function storesubtask(DailyTaskSubTaskRequest $request,$slug)
    {
        $dailyTaskHead = DailyTask::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $doing = TaskStatus::where('name',ParamSchema::TODO)->firstOrFail();

        DB::beginTransaction();
        try {
            $dailyTask = new DailyTask();
            $dailyTask->user_id = Auth::user()->id;
            $dailyTask->task_status_id = $doing->id;
            $dailyTask->child_daily_task_id = $dailyTaskHead->id;
            $dailyTask->start_date = $request->start_date;
            $dailyTask->end_date = $request->end_date;
            $dailyTask->assignment_user_id = $request->user_id;
            $dailyTask->daily_task_category_id = $dailyTaskHead->daily_task_category_id;
            $dailyTask->daily_task_type_id = $dailyTaskHead->daily_task_type_id;
            $dailyTask->daily_task_project_id = $dailyTaskHead->daily_task_project_id;
            $dailyTask->name = $request->name;
            $dailyTask->description = $request->description_subtask;
            $dailyTask->point = 0; // Assuming default value is 0
            $dailyTask->save();
    
    
            // Duplicate Custom Field
            if (isset($request->custom_field_values)) {
                foreach ($request->custom_field_values as $customFieldId => $customFieldValueId) {
                    if(is_array($customFieldValueId))
                    {
                        // 
                        foreach($customFieldValueId as $valueId)
                        {
                            DailyTaskCustomFieldValue::create([
                                'daily_task_id' => $dailyTask->id,
                                'custom_field_id' => $customFieldId,
                                'custom_field_value_id' => $valueId,
                            ]);
                        }
                    }else{
                        DailyTaskCustomFieldValue::create([
                            'daily_task_id' => $dailyTask->id,
                            'custom_field_id' => $customFieldId,
                            'custom_field_value_id' => $customFieldValueId,
                        ]);
                    }
                }
            }

            foreach ($dailyTaskHead->keyResults as $okr) 
            {
                $dailyTask->keyResults()->attach($okr->id);
            }

            $this->message($dailyTask->id,'create','Membuat Tugas '.$dailyTask->name);
            $this->statusrecord($dailyTask, $doing);

            
            DB::commit();
            return redirect()->route('dailytask.show', $dailyTaskHead->slug)->with('Subtask', true);
        } catch (\Throwable $th) 
        {
            //throw $th;
            // dd($th);
            DB::rollback();
            Log::error($th->getMessage());
            
            return redirect()->route('dailytask.show', $dailyTaskHead->slug)->with('Subtask', false);
        }

    }

    public function statuschange(Request $request,$slug)
    {
        $dailyTask = DailyTask::byCompany(Auth::user()->company_id)->where('slug',$slug)->firstOrFail();
        $doing = TaskStatus::where('name',ParamSchema::DOING)->firstOrFail();
        $dailyTask->task_status_id = $doing->id;
        
        $dailyTask->save();

        $this->message($dailyTask->id,'report','Tugas '.$dailyTask->name.' dikerjakan');
        $this->statusrecord($dailyTask, $doing);
        return redirect()->route('dailytask.show', $dailyTask->slug)->with('Working', true);
    }

    protected function message($dailyTaskId, $template, $message, $filePath = null)
    {
        switch ($template) 
        {
            case 'create':
                $message = 
                '
                <div class="alert alert-primary d-flex align-items-center" role="alert" style="background-color: #cce5ff; border-color: #004085; color: #004085;">
                    <i class="fa fa-plus-circle mr-2" style="color: #004085;"></i>
                    <div>
                        '.$message.' 
                    </div>
                </div>
                ';
                break;
            case 'edit':
                $message = 
                '
                <div class="alert alert-warning d-flex align-items-center" role="alert" style="background-color: #fff3cd; border-color: #856404; color: #856404;">
                    <i class="fa fa-edit mr-2" style="color: #856404;"></i>
                    <div>
                        '.$message.' 
                    </div>
                </div>
                ';
                break;
            case 'report':
                $message = 
                '
                <div class="alert alert-primary d-flex align-items-center" role="alert" style="background-color: #cce5ff; border-color: #004085; color: #004085;">
                    <i class="fa fa-plus-circle mr-2" style="color: #004085;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            case 'approvement':
                $message = 
                '
                <div class="alert alert-success d-flex align-items-center" role="alert" style="background-color: #d4edda; border-color: #155724; color: #155724;">
                    <i class="fa fa-thumbs-up mr-2" style="color: #155724;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            case 'extend':
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #383d41; color: #383d41;">
                    <i class="fa fa-clock mr-2" style="color: #383d41;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;

            case 'reject':
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #ae2121; color: #ae2121;">
                    <i class="fa fa-times-circle mr-2" style="color: #ae2121;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            default:
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #383d41; color: #383d41;">
                    <i class="fa fa-comment mr-2" style="color: #383d41;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
        }

        $dailyTaskMessage = new DailyTaskMessage();
        $dailyTaskMessage->user_id = Auth::user()->id;
        $dailyTaskMessage->daily_task_id = $dailyTaskId;
        $dailyTaskMessage->message = $message;
        $dailyTaskMessage->file_path = $filePath ?? NULL;
        $dailyTaskMessage->save();

        return true;
    }   

    protected function statusrecord($dailyTask, $status)
    {
        DailyTaskStatusRecord::create([
            'daily_task_id' => $dailyTask->id,
            'task_status_id' => $status->id,
            'date' => now(),
        ]);

        return true;
    }

    public function projectRecurring($dailytask)
    {
        // Temukan tugas asli berdasarkan slug
        // Buat salinan tugas asli
        $doing = TaskStatus::where('name',ParamSchema::TODO)->firstOrFail();

        $newTask = $dailytask->replicate();

        // Sesuaikan start_date dan end_date menjadi satu minggu dari tanggal asli
        $newTask->start_date = Carbon::parse($dailytask->start_date)->addWeek();
        $newTask->end_date = Carbon::parse($dailytask->end_date)->addWeek();
        $newTask->slug = Str::slug($dailytask->name) . '-' . Str::random(10);
        $newTask->task_status_id = $doing->id;
        $newTask->report_note = NULL;
        $newTask->submit = NULL;
        $newTask->status_submit = NULL;
        $newTask->approved = FALSE;
        $newTask->point = 0; // Assuming default value is 0
        // Simpan tugas baru
        $newTask->save();

        $this->message($newTask->id,'create',' System Recurring Tugas '.$newTask->name,null);
        return true;
    }
}

