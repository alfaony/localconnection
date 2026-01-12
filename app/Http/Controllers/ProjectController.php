<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\ProjectRequest;

use App\Models\Project;
use App\Models\WorkOrder;
use App\Models\Manager;
use App\Exports\ProjectsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Schemas\ParamSchema;
use Illuminate\Pagination\LengthAwarePaginator;
class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $order = $request->order == 'asc' ? 'asc' : 'desc';
        $page = $request->input('page', 1); // Halaman saat ini
        $perPage = 10; // Jumlah item per halaman

        $query = Project::byRole()
            ->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->get('search') . '%';
                
                // Pencarian dalam judul proyek dan nomor work order
                $query->where('title', 'like', $searchTerm)
                    ->orWhereHas('workOrder', function ($query) use ($searchTerm) {
                        $query->where('number_result', 'like', $searchTerm);
                    })
                    ->orWhereHas('user', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', $searchTerm);
                    });
            })
            ->orderBy('created_at', $order);

        // Ambil semua proyek
        $projects = $query->get();

        // Hitung total sebelum filtering

        // Filter proyek berdasarkan `status_project`
        if ($request->filled('status')) {
            $projects = $projects->filter(function ($project) use ($request) {
                return $project->status_project === $request->get('status');
            });
        }

        $total = $projects->count();
        // Gunakan LengthAwarePaginator untuk membuat pagination lengkap
        $project = new LengthAwarePaginator(
            $projects->forPage($page, $perPage), // Data untuk halaman saat ini
            $total, // Total item
            $perPage, // Item per halaman
            $page, // Halaman saat ini
            ['path' => $request->url(), 'query' => $request->query()] // URL dan query string untuk link pagination
        );

        $totalProject = Project::byRole()->count();
        $workOrder = WorkOrder::byCompany(Auth::user()->company_id)
        ->whereDoesntHave('project')
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
        $totalProject = Project::byRole()->count();
        $projectEdit = Project::where('slug', $slug)->firstOrFail();
        $project = Project::byRole()->OrderBy('created_at','asc')->paginate(10);
        // $workOrder = WorkOrder::all();
        $workOrder = WorkOrder::byCompany(Auth::user()->company_id)
        ->whereDoesntHave('project')
        ->orWhere('id', $projectEdit->work_order_id)
        ->orderBy('created_at','desc')
        ->get();
    

        if($projectEdit->status_project == ParamSchema::CLOSE)
        {
            return redirect()->to(route('project.index'))->with('project_close',true);
        }

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
        $project = Project::byRole()->where('slug', $slug)->firstOrFail();

        if($project->status_project == ParamSchema::CLOSE)
        {
            return redirect()->to(route('project.index'))->with('project_close',true);
        }

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
        $project = Project::byRole()->where('slug', $slug)->firstOrFail();

        if($project->status_project == ParamSchema::CLOSE)
        {
            return redirect()->back()->with('project_close',true);
        }

        $project->delete();
        return redirect()->back()->with('delete',true);
    }

    /**
     * Export data
     */
    public function export()
    {
        return Excel::download(new ProjectsExport, 'projects.xlsx');
    }

    /**
     * Get SPK details for modal display
     */
    public function getSpkDetails($id)
    {
        $workOrder = WorkOrder::with([
            'workOrderProduct.product',
            'quote',
            'userCreate'
        ])->findOrFail($id);
        
        $products = $workOrder->workOrderProduct->map(function($item) {
            return [
                'name' => $item->product->name ?? '-',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total
            ];
        });

        return response()->json([
            'products' => $products,
            'quotation_number' => $workOrder->quote->number_result ?? '-',
            'quote_name' => $workOrder->quote->userUpdate->name ?? '-',
            'creator_name' => $workOrder->userCreate->name ?? '-',
            'spk_number' => $workOrder->number_result,
            'spk_date' => $workOrder->date,
            'spk_total' => $workOrder->total
        ]);
    }
}
