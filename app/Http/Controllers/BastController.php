<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\BastRequest;
use Carbon\Carbon;

use App\Models\Bast;
use App\Models\Project;
use App\Models\WorkOrder;
use App\Models\SettingCompany;

class BastController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('bast.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $workOrder = WorkOrder::orderBy('created_at','desc')->get();
        $project = Project::orderBy('created_at','desc')->get();
        $userCreate = Auth::user()->name;
        $nomorBast = $this->bastNumber()['result'];
        return view('bast.createOrEdit',compact('nomorBast','userCreate','project'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BastRequest $request)
    {
        $bast = new Bast();

        $number = $this->bastNumber();

        $bast->date = $request->input('date');
        $bast->basts_number = $number['number'];
        $bast->number_result = $number['result'];
        $bast->work_order_id = $request->input('work_order');
        $bast->project_id = $request->input('project');
        $bast->number_purchase = $request->input('number_purchase');
        $bast->pic = $request->input('pic');

        $bast->user_created_id = Auth::user()->id;
        $bast->user_updated_id = Auth::user()->id;
        
        $bast->save();
        $this->updateBudget($request->input('work_order'), $request->input('project'));

        return redirect()->route('bast.download.pdf',$bast->slug)->with('store', true);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Basts  $basts
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        // $workOrder = WorkOrder::orderBy('created_at','desc')->get();
        $project = Project::orderBy('created_at','desc')->get();

        $bast = Bast::where('slug',$slug)->first();
        $userCreate = $bast->userCreate ? $bast->userCreate->name : '';
        $nomorBast = $bast->number_result ?? '';

        return view('bast.createOrEdit',compact('nomorBast','userCreate','project','bast'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Basts  $basts
     * @return downloadPDF
     */
    public function downloadPdf($slug)
    {
        $workOrder = WorkOrder::all();
        $project = Project::all();
        $company = SettingCompany::get()->pluck('field_value','field_title');

        $bast = Bast::where('slug',$slug)->first();
        $userCreate = $bast->userCreate ? $bast->userCreate->name : '';
        $nomorBast = $bast->number_result ?? '';
        $today = Carbon::now()->format('d M Y');

        return view('bast.pdf',compact('nomorBast','workOrder','userCreate','project','bast', 'today', 'company'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Basts  $basts
     * @return \Illuminate\Http\Response
     */
    public function update(BastRequest $request, Bast $bast)
    {
        $bast->date = $request->input('date');
        $bast->work_order_id = $request->input('work_order');
        $bast->project_id = $request->input('project');
        $bast->number_purchase = $request->input('number_purchase');
        $bast->pic = $request->input('pic');

        $bast->user_updated_id = Auth::user()->id;

        $bast->save();
        $this->updateBudget($request->input('work_order'), $request->input('project'));
        
        return redirect()->route('bast.download.pdf',$bast->slug)->with('update', true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Basts  $basts
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bast $bast)
    {
        $bast->delete();
        return redirect()->back()->with('delete',true);
    }

    /**
     * Data table for load AgreementLetter
     */
    public function dataTableJson()
    {
        // Fetch data for the DataTable
        $query = Bast::query();

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
            [
                'name' => 'Pdf',
                'route' => 'bast.download.pdf',
                'id' => true,
            ],
            [
                'name' => 'Edit',
                'route' => 'bast.edit',
                'id' => true,
            ],
            [
                'name' => 'Delete',
                'route' => 'bast.destroy',
                'id' => true,
            ],
        ];

        return datatablesFormater($query, $columnNames, $actionButtons, $searchable, $bootstrap);
    }

    private function bastNumber()
    {
        $date = Carbon::now()->format('m/Y');
        $nomor = Bast::withTrashed()->max('basts_number') + 1;

        return 
        [
            'number' => $nomor ?? 0,
            'result' => $nomor.'/'.$date ?? '' 
        ];
    }

    /**
     * update total value SPK to budget project
     */
    private function updateBudget($workOrderId,$projectId)
    {
        $workOrder = WorkOrder::findOrFail($workOrderId);
        $project = Project::findOrFail($projectId);


        // Get Total SPK
        $workOrderTotal = $workOrder->workOrderProduct() ? $workOrder->workOrderProduct()->sum('sub_total') : 0 ;

        // Update Budget
        $project->budget = $workOrderTotal;
        $project->save();

        return true;
    }
}
