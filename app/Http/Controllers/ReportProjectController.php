<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Http\Requests\ReportProjectRequest;

use Carbon\Carbon;
use App\Helpers\Access;
use App\Models\ReportProject;
use App\Models\ReportProjectDetail;
use App\Models\WorkOrder;
use App\Models\Project;
use App\Models\SortUrl;
use App\Models\User;
use App\Models\SettingCompany;

use ZipArchive;

use App\Helpers\InboxHelper;
use App\Helpers\EmailNotifHelper;

use App\Schemas\RoleSchema;

use PDF;
use setasign\Fpdi\Fpdi;
use App\Mail\SendBastEmail;
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
    public function createsuggest($slug)
    {
        $selectedProject = Project::byCompany(Auth::user()->company_id)->whereDoesntHave('reportProject')->where('slug',$slug)->first();
        if(!$selectedProject)
        {
            return redirect()->to(route('report-project.index'))->with('dataprojectnotfound',true);
        }

        if(!$selectedProject->workOrder)
        {
            return redirect()->to(route('report-project.index'))->with('datanotfound',true);
        }


        $nomorReportProject = $this->reportProjectNumber()['result'];
        $project = Project::byCompany(Auth::user()->company_id)
                    ->whereDoesntHave('reportProject')
                    ->orderBy('created_at', 'desc')->get();
        $workOrder = WorkOrder::byCompany(Auth::user()->company_id)->orderBy('created_at','desc')->get();

        $userCreate = Auth::user()->name;

        return view('report_project.createOrEdit',compact('project','nomorReportProject','userCreate','workOrder','selectedProject'));
    }

    public function create()
    {
        $nomorReportProject = $this->reportProjectNumber()['result'];
        $project = Project::byCompany(Auth::user()->company_id)
        ->whereDoesntHave('reportProject')
        ->orderBy('created_at', 'desc')->get();

        $workOrder = WorkOrder::byCompany(Auth::user()->company_id)->orderBy('created_at','desc')->get();
        $userCreate = Auth::user()->name;

        return view('report_project.createOrEdit',compact('project','nomorReportProject','userCreate','workOrder'));
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
            $project = Project::byCompany(Auth::user()->company_id)->find($request->post('project'));
    
            $reportProject->date = $request->post('date');
            $reportProject->report_project_number = $nomorReportProject['number'];
            $reportProject->number_result = $nomorReportProject['result'];
            $reportProject->work_order_id = $project->work_order_id;
            $reportProject->project_id = $request->post('project');
            $reportProject->is_approve = NULL;
            // $reportProject->link_report = $request->post('link_report');
            $reportProject->user_created_id = Auth::user()->id;
            $reportProject->user_updated_id = Auth::user()->id;
            
            $reportProject->save();

            $is_report = $request->post('is_report');
            $name = $request->post('name');
            $link = $request->post('link');
            $file = $request->file('file');
            
            for ($i = 0; $i < count($name); $i++) 
            {
                $reportProjectDetail = new ReportProjectDetail;
                $reportProjectDetail->order = $i+1;                
                $reportProjectDetail->name = $name[$i];
                $reportProjectDetail->is_report = $is_report[$i];
                $reportProjectDetail->link = $link[$i];
                
                if ($file[$i]) 
                {
                    $filename = time() . '_' . $file[$i]->getClientOriginalName();
                    $filePath = $file[$i]->storeAs('reports', $filename, 'public');
                    $reportProjectDetail->file = $filename;
                }

                $reportProject->reportProjectDetail()->save($reportProjectDetail);

                if($file[$i])
                {
                    $urlTarget = "/reports/". $filename;
                    $originalName = $file[$i]->getClientOriginalName();

                    $sortUrl = new SortUrl();
                    $sortUrl->name = $name[$i];
                    $sortUrl->link_target = $urlTarget;
                    $sortUrl->source = "App\Models\ReportProjectDetail";
                    $sortUrl->source_id = $reportProjectDetail->id;
                    $sortUrl->save();
                }
            }
    
            
            $this->sendNotification($reportProject, 'store', Auth::user()->company_id);

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
        $nomorReportProject = $reportProject->number_result;
        $project = Project::byCompany(Auth::user()->company_id)
        ->whereDoesntHave('reportProject')
        ->orWhere('id', $reportProject->project_id)
        ->orderBy('created_at', 'desc')->get();
        $workOrder = WorkOrder::byCompany(Auth::user()->company_id)->orderBy('created_at','desc')->get();
        $userCreate = $reportProject->userCreate ? $reportProject->userCreate->name : '';

        return view('report_project.createOrEdit',compact('project','nomorReportProject','userCreate','reportProject','workOrder'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReportProject  $ReportProject
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $reportProject = ReportProject::where('slug',$slug)->first();
        $nomorReportProject = $reportProject->number_result;
        $project = Project::byCompany(Auth::user()->company_id)
        ->whereDoesntHave('reportProject')
        ->orWhere('id', $reportProject->project_id)
        ->orderBy('created_at', 'desc')->get();
        $workOrder = WorkOrder::byCompany(Auth::user()->company_id)->orderBy('created_at','desc')->get();
        $userCreate = $reportProject->userCreate ? $reportProject->userCreate->name : '';

        return view('report_project.show',compact('project','nomorReportProject','userCreate','reportProject','workOrder'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReportProject  $ReportProject
     * @return \Illuminate\Http\Response
     */
    public function update(ReportProjectRequest $request, $slug)
    {
        DB::beginTransaction();
        try {
            $project = Project::byCompany(Auth::user()->company_id)->find($request->post('project'));

            
            $reportProject = ReportProject::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            if($reportProject->user_create_id == Auth::user()->id || $reportProject->user_updated_id == Auth::user()->id )
            {
                $reportProject->is_approve = NULL;
                $this->sendNotification($reportProject, 'update', Auth::user()->company_id);
            }
            $reportProject->date = $request->post('date');
            $reportProject->work_order_id = $project->work_order_id;
            $reportProject->project_id = $request->post('project');  
            $reportProject->user_updated_id = Auth::user()->id;
            $reportProject->save();
    
            $ids = $request->post('ids');
            $is_report = $request->post('is_report');
            $name = $request->post('name');
            $link = $request->post('link');
            $file = $request->file('file');
            
            for ($i = 0; $i < count($name); $i++) 
            {
                $id = $ids[$i];
                $checkFile = $file[$i] ?? false;

                if(!$id)
                {
                    $reportProjectDetail = new ReportProjectDetail;
                    $reportProjectDetail->order = $i+1;                
                    $reportProjectDetail->is_report = $is_report[$i];
                    $reportProjectDetail->name = $name[$i];
                    $reportProjectDetail->link = $link[$i];
                    
                    if ($checkFile) 
                    {
                        $filename = time() . '_' . $file[$i]->getClientOriginalName();
                        $filePath = $file[$i]->storeAs('reports', $filename, 'public');
                        $reportProjectDetail->file = $filename;
                    }
    
                    $reportProject->reportProjectDetail()->save($reportProjectDetail);

                    if($checkFile)
                    {
                        $urlTarget = "/reports/". $filename;
                        $originalName = $file[$i]->getClientOriginalName();
                        
                        $sortUrl = new SortUrl();
                        $sortUrl->name = $name[$i];
                        $sortUrl->link_target = $urlTarget;
                        $sortUrl->source = "App\Models\ReportProjectDetail";
                        $sortUrl->source_id = $reportProjectDetail->id;
                        $sortUrl->save();
                    }

                }else
                {
                    $reportProjectDetail = ReportProjectDetail::find($id);
                    $reportProjectDetail->is_report = $is_report[$i];
                    $reportProjectDetail->name = $name[$i];
                    $reportProjectDetail->link = $link[$i];
                    
                    if ($checkFile) 
                    {
                        $filename = time() . '_' . $file[$i]->getClientOriginalName();
                        $filePath = $file[$i]->storeAs('reports', $filename, 'public');
                        $reportProjectDetail->file = $filename;
                    }

                    $reportProjectDetail->save();

                    if($checkFile)
                    {
                        $urlTarget = "/reports/". $filename;
                        $originalName = $file[$i]->getClientOriginalName();
                        
                        $sortUrl = new SortUrl();
                        $sortUrl->name = $name[$i];
                        $sortUrl->link_target = $urlTarget;
                        $sortUrl->source = "App\Models\ReportProjectDetail";
                        $sortUrl->source_id = $reportProjectDetail->id;
                        $sortUrl->save();
                    }
                }
    
            }
            
            $this->sendNotification($reportProject, 'update', Auth::user()->company_id);

            if($reportProject->project->bast)
            {
                $mergedFilePath = $this->mergePdfFiles($reportProject->project->bast, $reportProject);
            }

            DB::commit();
            return redirect()->to(route('report-project.index'))->with('update', true);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            // dd($th);
            return redirect()->to(route('report-project.index'))->with('update', false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReportProject  $ReportProject
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $ReportProject = ReportProject::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
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
     * approvement
     */
    public function approvement(Request $request, $id)
    {
        $project = ReportProject::find($id);

        if (!$project) {
            return response()->json(['message' => 'Proyek tidak ditemukan.'], 404);
        }

        try {
            // Update approvement status dan note
            if($request->is_approve == 0)
            {
                $inboxHelper = new InboxHelper();
                if($project->user_created_id != $project->user_updated_id)
                {
                    $directUrl = route('report-project.edit', $project->slug);
    
                    $inboxHelper->sent
                    (
                        $project->user_created_id, 
                        Auth::user()->id, 
                        'Report Tertolak, dengan catatan' . $request->note, 
                        $directUrl
                    );
    
    
                    $inboxHelper->sent
                    (
                        $project->user_created_id, 
                        Auth::user()->id, 
                        'Report Tertolak, dengan catatan' . $request->note, 
                        $directUrl
                    );
                    
                }
            }
    
            $project->is_approve = $request->is_approve;
            $project->note = $request->note;
            $project->save();
    
            $approvement = $request->is_approve == 1 ? 'approve' : 'notapprove';
            
            // Not APprove Email
            if($approvement == 'notapprove')
            {
                $this->sendNotification($project, $approvement, Auth::user()->company_id,true,$request->note);
            }
    
            return response()->json(['message' => 'Approvement berhasil disimpan.']);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th->getMessage());
            return response()->json(['message' => 'Approvement gagal disimpan.'], 500);
        }
    }

    /**
     * Data table for load AgreementLetter
     */
    public function dataTableJson()
    {
        // Fetch data for the DataTable
        $query = ReportProject::query();
        $query->byCompany(Auth::user()->company_id)->with('project','workOrder')->orderBy('is_approve', 'asc')->orderBy('created_at', 'desc');

        // Map column indexes to column names (this may vary based on your table structure)
        $columnNames = ['date','is_approve','number_result', 'slug'];

        // Define searchable columns
        $searchable = 
        [
            0 => 'number_result',
            0 => 'is_approve',
            1 => 'date',
            2 => 'workOrder.number_result',
            3 => 'project.title',
        ];

        // define your bootstrap version (4 or 5)
        $bootstrap = 4;

        // Add action buttons to each row
        $actionButtons = [
            
        ];

        if(Access::can('show','report_projects'))
        {
            $edit = 
            [
                'name' => 'Show',
                'route' => 'report-project.show',
                'id' => true,
            ];

            array_push($actionButtons,$edit);
        }

        if(Access::can('edit','report_projects'))
        {
            $edit = 
            [
                'name' => 'Edit',
                'route' => 'report-project.edit',
                'id' => true,
            ];

            array_push($actionButtons,$edit);
        }

        if(Access::can('downloadall','report_projects'))
        {
            $edit = 
            [
                'name' => 'Download All File',
                'route' => 'report-project.downloadall',
                'id' => true,
            ];

            array_push($actionButtons,$edit);
        }

        if(Access::can('destroy','report_projects'))
        {
            $destroy = 
            [
                'name' => 'Delete',
                'route' => 'report-project.destroy',
                'id' => true,
            ];

            array_push($actionButtons,$destroy);
        }
        

        $response = datatablesFormaterWithSearchRelasion($query, $columnNames, $actionButtons, $searchable, $bootstrap);

        $data = $response->getData();
        foreach ($data->data as $index => $item) 
        {
            $status = NULL;
            $badgeClass = ''; // Menyimpan kelas badge

            // Cek nilai is_approve dan set status dan kelas badge
            if (is_null($item->is_approve)) 
            {
                $status = "Waiting Approve";
                $badgeClass = 'badge-warning';
            } 
            elseif ($item->is_approve == 1) 
            {
                $status = "Approve";
                $badgeClass = 'badge-success';
            } 
            elseif ($item->is_approve == 0) 
            {
                $status = "Declined";
                $badgeClass = 'badge-danger';
            }

            // Mengubah status menjadi badge
            $item->is_approve = "<span class='badge $badgeClass'>$status</span>";
        }


        return response()->json($data);
    }

    public function dataTableJsonWorkOrderWithoutReportProject()
    {
        // Fetch data for the DataTable
        $query = Project::query();
        $query->byCompany(Auth::user()->company_id); // Filter by the company of the logged-in user
        $query->doesntHave('reportProject');
        $query->with('workOrder')->orderBy('created_at', 'desc');

        // Map column indexes to column names (modify these based on your actual database structure)
        $columnNames = ['title', 'work_order_number', 'description'];

        // Define searchable columns
        $searchable = [
            'title',
            'workOrder.number_result', // Relasi: mencari di dalam kolom work_order
        ];

        // Define action buttons
        $actionButtons = [];
        // Conditionally add buttons based on permissions
        if (Access::can('createsuggest', 'report_projects')) {
            $actionButtons[] = [
                'name' => 'Membuat Laporan Proyek',
                'route' => 'report-project.createsuggest',
                'id' => true,
            ];
        }

        $response = datatablesFormaterWithSearchRelasion($query, $columnNames, $actionButtons, $searchable, 4); // assuming bootstrap version 4

        $data = $response->getData();
        foreach ($data->data as $index => $item) 
        {
            $item->work_order->total = 'Rp. '.number_format($item->work_order->total, 0,',','.'); // Format angka dengan 2 desimal
        }
        
        return response()->json($data);
    }

    public function downloadall($slug)
    {
        // Ambil semua file berdasarkan ID reportProject
        $reportProject = ReportProject::with('reportProjectDetail')->where('slug',$slug)->firstOrFail();
        if (!$reportProject) {
            return redirect()->back()->with('error', 'Report Project not found.');
        }
        
        // Nama file ZIP
        $zipFileName = 'reports_' . $reportProject->number_result . '.zip';
        $zipFileName = str_replace('/', '_', $zipFileName);
        $zipPath = storage_path('app/public/' . $zipFileName);

        // Membuat ZIP
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($reportProject->reportProjectDetail->sortBy('order') as $detail) {
                $filePath = storage_path('app/public/reports/' . $detail->file);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $detail->file);
                }
            }
            $zip->close();
        } else {
            return redirect()->back()->with('error', 'Failed to create ZIP file.');
        }

        // Mengunduh file ZIP
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }


    private function reportProjectNumber()
    {
        $date = Carbon::now()->format('m/Y');
        $nomor = ReportProject::byCompany(Auth::user()->company_id)->withTrashed()->max('report_project_number') + 1;

        return 
        [
            'number' => $nomor ?? 0,
            'result' => $nomor.'/'.$date ?? '' 
        ];
    }

    private function sendNotification($reportProject, $timeNotify, $companyId, $approval = null,  $notes = null)
    {
        $data = [
            'work_order' => $reportProject->workOrder->number_result,
            'project' => $reportProject->project->title,
            'user_create' => $reportProject->userCreate->name,
            'created_at' => Carbon::parse($reportProject->created_at)->format('d-m-Y'),
            'notes' => $notes
        ];
        
        $toEmails = [];
        $toUserId = [];
        $toNames = [];
        
        if(!$approval)
        {
            $ccEmails = [Auth::user()->email];
            $usersAdmin = User::where('company_id',Auth::user()->company_id)->whereHas('role', function($q){
                $q->where('name',RoleSchema::ADMIN);
            })->get();

            if($usersAdmin->isEmpty())
            {
                return false;
            }

            $lead = User::byCompany(Auth::user()->company_id)->where('id',Auth::user()->approvement_user_id)->first();
            foreach ($usersAdmin as $user) 
            {
                $toEmails[] = $user->email;
                $toUserId[] = $user->id;
                $toNames[] = $user->name;
            }

            if($lead) $toEmails[] = $lead->email;
        }else
        {
            $toEmails[] = $reportProject->userCreate->email;
            $toUserId[] = $reportProject->userCreate->id;
            $toNames[] = $reportProject->userCreate->name;
            $ccEmails = [Auth::user()->email];
        }

        $smtpConfig = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
        $fromEmail = $smtpConfig['username'] ?? '';
        $fromName = $smtpConfig['name'] ?? '';

        $directUrl = route('report-project.show', $reportProject->slug);
        $data['url'] = $directUrl;

        switch ($timeNotify) 
        {
            case "store":
                $subject = 'Laporan Proyek Baru untuk Persetujuan';
                $tamplate = 'email.notif_create_report_project';

                $this->sentInbox($toUserId,$subject, $directUrl);
                break;

            case "update":
                $subject = 'Notifikasi Pembaruan Laporan Proyek – Revisi Telah Diupload';
                $tamplate = 'email.notif_create_report_project';

                $this->sentInbox($toUserId,$subject, $directUrl);
                break;

            case "approve":
                $subject = 'Anggaran '.$budget->name.' Disetujui';
                $tamplate = 'email.notif_budget_approval';
                $this->sentInbox($toUserId,$subject, $directUrl);
                break;

            case "notapprove":
                $subject = 'Laporan Proyek – Revisi Diperlukan';
                $tamplate = 'email.notif_decline_report_project';

                $this->sentInbox($toUserId,$subject, $directUrl);
                break;
        }

        $data['title'] = $subject;
        
        // Email Helper Notification
         EmailNotifHelper::sentEmail(
            $fromEmail,
            $fromName,
            $toEmails, 
            $toNames, 
            $subject,
            $tamplate,
            $data, 
            $smtpConfig, 
            $companyId, 
            $ccEmails
        );
    }

    private function sentInbox($to,$message,$directUrl)
    {
        foreach ($to as $key => $value) 
        {
            $inboxHelper = new InboxHelper();
            $inboxHelper->sent(
                $value, 
                Auth::user()->id, 
                $message, 
                $directUrl
            );
        }

        return;
    }
    private function mergePdfFiles($bast, $reportProject)
    {
        try {
            // Check if a file already exists and delete it before updating
            if (!empty($bast->file_merge_path) && Storage::exists($bast->file_merge_path)) 
            {
                Storage::delete($bast->file_merge_path);
            }
            
            // Initialize an array to store PDF file paths
            $pdfFiles = [];

            // Collect only PDF files from reportProjectDetail
            foreach ($reportProject->reportedDetails as $detail) 
            {
                $filePath = storage_path('app/public/reports/' . $detail->file);
                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

                if (file_exists($filePath) && strtolower($fileExtension) === 'pdf') {
                    $pdfFiles[] = $filePath;
                }
            }
            // Generate a new PDF from the 'bast.pdf_download' view
            $today = Carbon::now()->format('d M Y');
            $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');

            $additionalPdf = PDF::loadView('bast.'.$bast->template, compact('bast', 'today','company'));

            // Convert generated PDF to a string
            $additionalPdfContent = $additionalPdf->output();

            // Save the additional PDF as a temporary file
            $tempFilePath = 'public/temp_additional.pdf';
            Storage::put($tempFilePath, $additionalPdfContent);

            // Initialize FPDI to merge PDFs
            $mergedPdf = new Fpdi();

            // Add the additional PDF first
            $additionalPdfPath = Storage::path($tempFilePath);
            if (file_exists($additionalPdfPath)) {
                $pageCount = $mergedPdf->setSourceFile($additionalPdfPath);

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $mergedPdf->importPage($pageNo);
                    $size = $mergedPdf->getTemplateSize($templateId);

                    $mergedPdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $mergedPdf->useTemplate($templateId);
                }
            } else {
                throw new \Exception('Temporary additional PDF file not found.');
            }

            // Merge the collected PDF files
            foreach ($pdfFiles as $pdfFile) {
                $pageCount = $mergedPdf->setSourceFile($pdfFile);

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $mergedPdf->importPage($pageNo);
                    $size = $mergedPdf->getTemplateSize($templateId);

                    $mergedPdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $mergedPdf->useTemplate($templateId);
                }
            }

            // Create the final merged PDF path
            $finalFileName = 'merged_' . str_replace('/', '_', $bast->number_result) . '.pdf';
            $finalFilePath = 'public/reports/' . $finalFileName;

            // Output the final merged PDF to storage
            $finalPdfContent = $mergedPdf->Output('', 'S');
            Storage::put($finalFilePath, $finalPdfContent);

            // Delete temporary files
            Storage::delete($tempFilePath);
            
            $bast->file_merge_path = $finalFilePath;
            $bast->save();

            return true;
        } catch (\Exception $e) {
            \Log::error('Error in merging PDF files: ' . $e->getMessage());
            return false;
        }
    }
}
