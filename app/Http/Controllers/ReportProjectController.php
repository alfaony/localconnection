<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\ReportProjectRequest;

use Carbon\Carbon;

use App\Models\ReportProject;
use App\Models\ReportProjectDetail;
use App\Models\WorkOrder;
use App\Models\Project;


class ReportProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('report_project.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $nomorReportProject = $this->reportProjectNumber()['result'];
        $project = Project::orderBy('created_at','desc')->get();
        // $workOrder = WorkOrder::orderBy('created_at','desc')->get();
        $userCreate = Auth::user()->name;

        return view('report_project.createOrEdit',compact('project','nomorReportProject','userCreate'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ReportProjectRequest $request)
    {

        DB::beginTransaction();
        try {
            $nomorReportProject = $this->reportProjectNumber();
            $reportProject = new ReportProject();
    
            $reportProject->date = $request->post('date');
            $reportProject->report_project_number = $nomorReportProject['number'];
            $reportProject->number_result = $nomorReportProject['result'];
            $reportProject->work_order_id = $request->post('work_order');
            $reportProject->project_id = $request->post('project');
            // $reportProject->link_report = $request->post('link_report');
            $reportProject->user_created_id = Auth::user()->id;
            $reportProject->user_updated_id = Auth::user()->id;
            
            $reportProject->save();

            $name = $request->post('name');
            $link = $request->post('link');
            $file = $request->file('file');
            

            for ($i = 0; $i < count($name); $i++) 
            {
                $reportProjectDetail = new ReportProjectDetail;
                $reportProjectDetail->name = $name[$i];
                $reportProjectDetail->link = $link[$i];
                
                if ($file[$i]) 
                {
                    $filename = time() . '_' . $file[$i]->getClientOriginalName();
                    $filePath = $file[$i]->storeAs('reports', $filename, 'public');
                    $reportProjectDetail->file = $filename;
                }

                $reportProject->reportProjectDetail()->save($reportProjectDetail);
            }
    
            
            
            DB::commit();
            return redirect()->to(route('report-project.index'))->with('store', true);
        } catch (\Throwable $th) 
        {
            //throw $th;
            // dd($th);
            Log::error($th);
            DB::rollback();
            return redirect()->to(route('report-project.index'))->with('store', false);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReportProject  $ReportProject
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $reportProject = ReportProject::where('slug',$slug)->first();
        $nomorReportProject = $this->reportProjectNumber()['result'];
        $project = Project::orderBy('created_at','desc')->get();
        // $workOrder = WorkOrder::orderBy('created_at','desc')->get();
        $userCreate = $reportProject->userCreate ? $reportProject->userCreate->name : '';

        return view('report_project.createOrEdit',compact('project','nomorReportProject','userCreate','reportProject'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReportProject  $ReportProject
     * @return \Illuminate\Http\Response
     */
    public function update(ReportProjectRequest $request, ReportProject $reportProject)
    {
        DB::beginTransaction();
        try {
            $reportProject->date = $request->post('date');
            $reportProject->work_order_id = $request->post('work_order');
            $reportProject->project_id = $request->post('project');
            // $reportProject->link_report = $request->post('link_report');        
            $reportProject->user_updated_id = Auth::user()->id;
            $reportProject->save();
    
            $ids = $request->post('ids');
            $name = $request->post('name');
            $link = $request->post('link');
            $file = $request->file('file');
            
    
            for ($i = 0; $i < count($name); $i++) 
            {
                $id = $ids[$i];
                if(!$id)
                {
                    $reportProjectDetail = new ReportProjectDetail;
                    $reportProjectDetail->name = $name[$i];
                    $reportProjectDetail->link = $link[$i];
                    
                    if ($file && $file[$i]) 
                    {
                        $filename = time() . '_' . $file[$i]->getClientOriginalName();
                        $filePath = $file[$i]->storeAs('reports', $filename, 'public');
                        $reportProjectDetail->file = $filename;
                    }
    
                    $reportProject->reportProjectDetail()->save($reportProjectDetail);
                }else
                {
                    $reportProjectDetail = ReportProjectDetail::find($id);
                    $reportProjectDetail->name = $name[$i];
                    $reportProjectDetail->link = $link[$i];
                    
                    if ($file && $file[$i]) 
                    {
                        $filename = time() . '_' . $file[$i]->getClientOriginalName();
                        $filePath = $file[$i]->storeAs('reports', $filename, 'public');
                        $reportProjectDetail->file = $filename;
                    }
                    $reportProjectDetail->save();
                }
    
            }
            
            DB::commit();
            return redirect()->to(route('report-project.index'))->with('update', true);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            dd($th);
            return redirect()->to(route('report-project.index'))->with('update', false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReportProject  $ReportProject
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReportProject $ReportProject)
    {
        $ReportProject->delete();
        return redirect()->back()->with('delete',true);
    }

    /**
     * Remove Project Details
     */
    public function destroyDetail($id)
    {
        ReportProjectDetail::find($id)->delete();
        return true;
    }
    /**
     * Data table for load AgreementLetter
     */
    public function dataTableJson()
    {
        // Fetch data for the DataTable
        $query = ReportProject::query();

        // Map column indexes to column names (this may vary based on your table structure)
        $columnNames = ['date','number_result', 'slug'];

        // Define searchable columns
        $searchable = 
        [
            0 => 'number_result',
            1 => 'date',
        ];

        // define your bootstrap version (4 or 5)
        $bootstrap = 4;

        // Add action buttons to each row
        $actionButtons = [
            // [
            //     'name' => 'Pdf',
            //     'route' => 'bast.download.pdf',
            //     'id' => true,
            // ],
            [
                'name' => 'Edit',
                'route' => 'report-project.edit',
                'id' => true,
            ],
            [
                'name' => 'Delete',
                'route' => 'report-project.destroy',
                'id' => true,
            ],
        ];

        return datatablesFormater($query, $columnNames, $actionButtons, $searchable, $bootstrap);
    }

    private function reportProjectNumber()
    {
        $date = Carbon::now()->format('m/Y');
        $nomor = ReportProject::withTrashed()->max('report_project_number') + 1;

        return 
        [
            'number' => $nomor ?? 0,
            'result' => $nomor.'/'.$date ?? '' 
        ];
    }
}
