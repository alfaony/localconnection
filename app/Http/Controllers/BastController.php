<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\BastRequest;
use Carbon\Carbon;
use App\Helpers\Access;
use App\Models\Bast;
use App\Models\Project;
use App\Models\WorkOrder;
use App\Models\SettingCompany;
use App\Helpers\InboxHelper;

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
    public function createsuggest($slug)
    {
        $selectedWorkOrder = WorkOrder::select('id','number_result')->with('reportProject')->byCompany(Auth::user()->company_id)->with('project')->where('slug',$slug)->first();
        if(!$selectedWorkOrder)
        {
            return redirect()->to(route('bast.index'))->with('datanotfound',true);
        }

        if(!$selectedWorkOrder->project)
        {
            return redirect()->to(route('bast.index'))->with('dataprojectnotfound',true);
        }

        $workOrder = WorkOrder::byCompany(Auth::user()->company_id)->whereHas('reportProject')->orderBy('created_at','desc')->get();
        $project = Project::byCompany(Auth::user()->company_id)
        ->whereHas('reportProject')
        ->whereDoesntHave('bast')
        ->orderBy('created_at','desc')->get();
        $userCreate = Auth::user()->name;
        $nomorBast = $this->bastNumber()['result'];
        $signature = config('custom.customerSignature');

        return view('bast.createOrEdit',compact('nomorBast','userCreate','project','signature','workOrder','selectedWorkOrder'));
    }

    public function create(Request $request)
    {
        $workOrder = WorkOrder::byCompany(Auth::user()->company_id)->whereHas('reportProject')->orderBy('created_at','desc')->get();
        $project = Project::byCompany(Auth::user()->company_id)
        //  ->whereHas('reportProject')
        ->whereDoesntHave('bast')
        ->orderBy('created_at','desc')->get();
        $userCreate = Auth::user()->name;
        $nomorBast = $this->bastNumber()['result'];
        $signature = config('custom.customerSignature');
        return view('bast.createOrEdit',compact('nomorBast','userCreate','project','signature','workOrder'));
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
        $project = Project::byCompany(Auth::user()->company_id)->where('id',$request->post('project'))->firstOrFail();


        $number = $this->bastNumber();

        $bast->date = $request->input('date');
        $bast->basts_number = $number['number'];
        $bast->number_result = $number['result'];
        $bast->work_order_id = $project->work_order_id;
        $bast->project_id = $request->input('project');
        $bast->number_purchase = $request->input('number_purchase');
        $bast->pic = $request->input('pic');
        $bast->customer_signature = $request->input('customer_signature');

        $bast->user_created_id = Auth::user()->id;
        $bast->user_updated_id = Auth::user()->id;
        
        $bast->save();
        $this->updateBudget($project->work_order_id, $request->input('project'));

        return redirect()->to(route('bast.download.pdf',$bast->slug))->with('store', true);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Basts  $basts
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        
        $bast = Bast::where('slug',$slug)->firstOrFail();

        $project = Project::byCompany(Auth::user()->company_id)
        ->whereHas('reportProject')
        ->whereDoesntHave('bast')
        ->orWhere('id',$bast->project_id)
        ->orderBy('created_at','desc')->get();

        $userCreate = $bast->userCreate ? $bast->userCreate->name : '';
        $nomorBast = $bast->number_result ?? '';
        $signature = config('custom.customerSignature');
        $workOrder = WorkOrder::byCompany(Auth::user()->company_id)->whereDoesntHave('bast')
        ->whereHas('reportProject')
        ->orWhere('id', $bast->work_order_id)
        ->orderBy('created_at','desc')->get();

        return view('bast.createOrEdit',compact('nomorBast','userCreate','project','bast','signature','workOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Basts  $basts
     * @return downloadPDF
     */
    public function downloadPdf($slug)
    {
        $workOrder = WorkOrder::byCompany(Auth::user()->company_id)->get();
        $project = Project::byCompany(Auth::user()->company_id)->get();
        $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');

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
    public function update(BastRequest $request, $slug)
    {
        $bast = Bast::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $project = Project::byCompany(Auth::user()->company_id)->where('id',$request->post('project'))->firstOrFail();
        
        $bast->date = $request->input('date');
        $bast->work_order_id = $project->work_order_id;
        $bast->project_id = $request->input('project');
        $bast->number_purchase = $request->input('number_purchase');
        $bast->pic = $request->input('pic');
        $bast->customer_signature = $request->input('customer_signature');

        $bast->user_updated_id = Auth::user()->id;

        $bast->save();
        $this->updateBudget($project->work_order_id, $request->input('project'));
        
        return redirect()->to(route('bast.download.pdf',$bast->slug))->with('update', true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Basts  $basts
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $bast = Bast::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $bast->delete();
        return redirect()->back()->with('delete',true);
    }

    /**
     * Data table for load AgreementLetter
     */
    public function dataTableJson()
    {
        // Fetch data for the DataTable
        $query = Bast::with('workOrder')->byCompany(Auth::user()->company_id);


        // Map column indexes to column names (this may vary based on your table structure)
        $columnNames = ['date', 'number_result', 'slug'];

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
        ];

        if(Access::can('downloadPdf','basts'))
        {
            $pdf = 
            [
                'name' => 'Pdf',
                'route' => 'bast.download.pdf',
                'id' => true,
            ];

            array_push($actionButtons,$pdf);
        }

        if(Access::can('edit','basts'))
        {
            $edit = 
            [
                'name' => 'Edit',
                'route' => 'bast.edit',
                'id' => true,
            ];

            array_push($actionButtons,$edit);
        }

        if(Access::can('destroy','basts'))
        {
            $destroy = 
            [
                'name' => 'Delete',
                'route' => 'bast.destroy',
                'id' => true,
            ];

            array_push($actionButtons,$destroy);
        }

        return datatablesFormater($query, $columnNames, $actionButtons, $searchable, $bootstrap);
    }

    public function dataTableJsonWorkOrderWithoutBast()
    {
        // Fetch data for the DataTable
        $query = WorkOrder::query();
        $query->byCompany(Auth::user()->company_id); // Filter by the company of the logged-in user
        // $query->whereHas('reportProject'); // Only fetch WorkOrders with an associated ReportProject
        // $query->whereDoesntHave('bast'); // Only fetch WorkOrders with an associated ReportProject
        $query->whereHas('project', function($q) {
            // Filter project yang tidak memiliki reportProject (HasOne)
            $q->doesntHave('bast');
            $q->has('reportProject');
        });

        // Map column indexes to column names (modify these based on your actual database structure)
        $columnNames = ['date', 'number_result', 'project_name'];

        // Define searchable columns
        $searchable = [
            0 => 'number_result',
            1 => 'date',
        ];

        // Define action buttons
        $actionButtons = [];
        // Conditionally add buttons based on permissions
        if (Access::can('createsuggest', 'basts')) {
            $actionButtons[] = [
                'name' => 'Membuat Bast',
                'route' => 'bast.createsuggest',
                'id' => true,
            ];
        }

        $response = datatablesFormater($query, $columnNames, $actionButtons, $searchable, 4); // assuming bootstrap version 4

        $data = $response->getData();
        foreach ($data->data as $index => $item) 
        {
            $item->total = 'Rp. '.number_format($item->total, 0,',','.'); // Format angka dengan 2 desimal
        }

        return response()->json($data);
    }

    public function requestReport(Request $request)
    {
        $projectId = $request->input('project_id');
        $project = Project::byCompany(Auth::user()->company_id)->findOrFail($projectId);

        $directUrl = route('report-project.createsuggest', $project->workOrder->slug, $project->slug);
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $project->user_id, 
            Auth::user()->id, 
            'Request Report for ' . $project->name, 
            $directUrl
        );
        
        return response()->json(['message' => 'Request successfully sent']);
    }
    private function bastNumber()
    {
        $date = Carbon::now()->format('m/Y');
        $nomor = Bast::byCompany(Auth::user()->company_id)->withTrashed()->max('basts_number') + 1;

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
