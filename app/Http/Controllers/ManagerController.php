<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

use App\Schemas\ParamSchema;
use App\Http\Requests\ManagerRequest;

use App\Models\Manager;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Job;


class ManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $manager = Manager::where('name','like', '%' . $request->get('manager') . '%')
        ->OrderBy('created_at','asc')->paginate(10);

        $totalManager = count(Manager::get());
        return view('manager.index',compact('manager','totalManager'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $nomor = $request->get('nomor') ?? 0;

        $project = Project::get();
        $employee = Employee::get();
        $dateNow = Carbon::now();
        $paymentMode = config('custom.paymentMode');
        $dateCreate = Carbon::now()->format('Y-m-d');

        return view('manager.createOrEdit',compact('project','employee','nomor','paymentMode','dateNow','dateCreate'));
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ManagerRequest $request)
    {
        // dd($request->all());
        try {
            DB::beginTransaction();
            
            $manager = new Manager();
            $manager->project_id = $request->input('project');
            $manager->user_id = Auth::user()->id;
            $manager->date = $request->input('date');
            $manager->name = $request->input('name');
            $manager->phone = $request->input('phone');
            $manager->payment_method = $request->input('payment_method');
            
            $manager->save();
    
            $employee = $request->input('employee');
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            $total = $request->input('total');
            $idChild = $request->input('idChild');
            
            // Loop melalui salah satu array (karena semua memiliki panjang yang sama)
            for ($i = 0; $i < count($employee); $i++) 
            {
                $job = new Job();
                $job->employee_id = $employee[$i];
                $job->start_date = $start_date[$i];
                $job->end_date = $end_date[$i];
                $job->total = $total[$i];

                $manager->job()->save($job);
            }

            $manager->total_job = $manager->job()->sum('total');
            $manager->save();


            DB::commit();

            return redirect()->to(route('manager.index'))->with('store',true);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            Log::error($th);
            // dd($th);
            return redirect()->to(route('manager.index'))->with('store',false);

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Managers  $managers
     * @return \Illuminate\Http\Response
     */
    public function show(Managers $managers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Managers  $managers
     * @return \Illuminate\Http\Response
     */
    public function edit($slug,Request $request)
    {
        $nomor = $request->get('nomor') ?? 0;

        $project = Project::get();
        $employee = Employee::get();
        $paymentMode = config('custom.paymentMode');
        $manager = Manager::where('slug', $slug)->firstOrFail();
        $dateNow = $manager->date ?? Carbon::now();
        $dateCreate = Carbon::parse($manager->created_at)->format('Y-m-d');
        

        return view('manager.createOrEdit',compact('project','employee','nomor','paymentMode','manager','dateNow','dateCreate'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Managers  $managers
     * @return \Illuminate\Http\Response
     */
    public function update(ManagerRequest $request, Manager $manager)
    {
        // dd($request->all());
        try {
            DB::beginTransaction();

            $manager->project_id = $request->input('project');
            $manager->user_id = Auth::user()->id;
            $manager->date = $request->input('date');
            $manager->name = $request->input('name');
            $manager->phone = $request->input('phone');
            $manager->payment_method = $request->input('payment_method');
            
            $manager->save();
    
            $employee = $request->input('employee');
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            $total = $request->input('total');
            $idChild = $request->input('idChild');
            
            // Loop melalui salah satu array (karena semua memiliki panjang yang sama)
            for ($i = 0; $i < count($employee); $i++) 
            {
                $id = $idChild[$i];
                // dd($request->all());
                if(!$id)
                {
                    $job = new Job();
                    $job->employee_id = $employee[$i];
                    $job->start_date = $start_date[$i];
                    $job->end_date = $end_date[$i];
                    $job->total = $total[$i];
                    $manager->job()->save($job);
                }else
                {
                    $job = Job::find($id);
                    $job->employee_id = $employee[$i];
                    $job->start_date = $start_date[$i];
                    $job->end_date = $end_date[$i];
                    $job->total = $total[$i];
                    $job->save();
                }
            }


            $manager->total_job = $manager->job()->sum('total');
            $manager->save();


            DB::commit();
            return redirect()->to(route('manager.index'))->with('update',true);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            Log::error($th);
            // dd($th);
            return redirect()->to(route('manager.index'))->with('update',false);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Managers  $managers
     * @return \Illuminate\Http\Response
     */
    public function destroy(Manager $manager)
    {
        $manager->job()->delete();
        $manager->delete();

        return redirect()->back()->with('delete',true);
    }

    /**
     * function to count work time
     */
    public function counting(Request $request)
    {
        $messages = [
            'endDate.required' => 'Tanggal akhir wajib diisi.',
            'endDate.date' => 'Tanggal akhir harus dalam format tanggal yang valid.',
            'endDate.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal mulai.',
            
            'startDate.required' => 'Tanggal mulai wajib diisi.',
            'startDate.date' => 'Tanggal mulai harus dalam format tanggal yang valid.',
            'startDate.before_or_equal' => 'Tanggal mulai harus sama dengan atau sebelum tanggal akhir.',
            
            'paymentMethod.required' => 'Metode pembayaran wajib dipilih.',
            'paymentMethod.in' => 'Metode pembayaran yang dipilih tidak valid.',
        
            'salaryDaily.required' => 'Gaji harian wajib diisi.',
            'salaryDaily.numeric' => 'Gaji harian harus berupa angka.',
            'salaryDaily.min' => 'Gaji harian tidak boleh kurang dari 0.',
        
            'salaryMontly.required' => 'Gaji bulanan wajib diisi.',
            'salaryMontly.numeric' => 'Gaji bulanan harus berupa angka.',
            'salaryMontly.min' => 'Gaji bulanan tidak boleh kurang dari 0.',
        
            'useemployeeIdrId.required' => 'ID pengguna wajib diisi.',
            'employeeId.uuid' => 'ID pengguna tidak valid.',
            'employeeId.exists' => 'Pengguna tidak ditemukan.'
        ];


        // return $request->all();
        $validatedData = $request->validate([
            'endDate' => 'required|date|after_or_equal:startDate',
            'startDate' => 'required|date|before_or_equal:endDate',
            'paymentMethod' => 'required|in:daily,monthly',
            // 'salaryDaily' => 'required|numeric|min:0',
            // 'salaryMontly' => 'required|numeric|min:0',
            'employeeId' => 'required|uuid|exists:employees,id'
        ],$messages);


        $salary = 0;
        $start = Carbon::parse($request->get('startDate'));
        $end = Carbon::parse($request->get('endDate'));

        $duration = $start->diffInDays($end) + ParamSchema::ONEDAY ;

        $employee = Employee::find($request->get('employeeId'));



        switch ($request->get('paymentMethod')) 
        {
            case ParamSchema::DAILY :
                $salary = $employee->salary_daily * $duration;
                break;

            case ParamSchema::MONTHLY :
                $salaryDaily = intval($employee->salary_monthly / ParamSchema::ONEMONTH);
                $salary = $salaryDaily * $duration;
                break;
        }

        $data = 
        [
            'total'=> $salary,
            'duration' => $duration
        ];

        return [
            'status' => 200,
            'message' => 'okay',
            'data' => $data
        ];

    }

    /**
     * delete job
     */
    public function destroyJob(Job $job)
    {
        // return "okay";
        
        $managerId = $job->manager_id;
        $job->delete();
        
        $manager = Manager::find($managerId);
        $total = $manager->job->sum('total');
        $manager->total_job = $total;
        $manager->save();

        return "berhasil";
    }

}
