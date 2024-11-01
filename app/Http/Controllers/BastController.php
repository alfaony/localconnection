<?php

namespace App\Http\Controllers;

use App\Http\Requests\BastRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Helpers\Access;
use App\Helpers\InboxHelper;

use App\Models\Bast;
use App\Models\Project;
use App\Models\WorkOrder;
use App\Models\SettingCompany;
use App\Models\BastEmailRecord;
use App\Models\BastFileMerge;

use PDF;
use setasign\Fpdi\Fpdi;
use App\Mail\SendBastEmail;
use Carbon\Carbon;
use App\Helpers\EmailNotifHelper;

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
        DB::beginTransaction();
        try {
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
            $bast->period = $request->input('period');
    
            $bast->user_created_id = Auth::user()->id;
            $bast->user_updated_id = Auth::user()->id;
            
            $bast->save();
            $this->updateBudget($project->work_order_id, $request->input('project'));
    
            if($bast->project->reportProject)
            {
                $mergedFilePath = $this->mergePdfFiles($bast, $bast->project->reportProject);
            }

            DB::commit();
            return redirect()->to(route('bast.show',$bast->slug))->with('store', true);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollBack();
            \Log::error($th);
            return redirect()->back()->with('store', false);
        }

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
     * Show PDF
     */
    // public function showPdf($slug)
    // {
    //     $workOrder = WorkOrder::byCompany(Auth::user()->company_id)->get();
    //     $project = Project::byCompany(Auth::user()->company_id)->get();
    //     $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value', 'field_title');

    //     $bast = Bast::where('slug', $slug)->first();
    //     $userCreate = $bast->userCreate ? $bast->userCreate->name : '';
    //     $nomorBast = $bast->number_result ?? '';
    //     $today = Carbon::now()->format('d M Y');

    //     $pdf = PDF::loadView('bast.pdf_download', compact(
    //         'nomorBast', 'workOrder', 'userCreate', 'project', 'bast', 'today', 'company'
    //     ));
        
    //     $filename = str_replace('/', '_', $nomorBast);
    //     return $pdf->stream("BAST_{$filename}.pdf");
    // }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Basts  $basts
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        
        $bast = Bast::where('slug',$slug)->firstOrFail();

        $project = Project::byCompany(Auth::user()->company_id)
        ->whereHas('reportProject')
        ->whereDoesntHave('bast')
        ->orWhere('id',$bast->project_id)
        ->orderBy('created_at','desc')->get();

        $fileMerges = BastFileMerge::where('bast_id', $bast->id)
                                   ->orderBy('version', 'desc')
                                   ->paginate(3);

        $fileMergesChooice = BastFileMerge::where('bast_id', $bast->id)
            ->orderBy('version', 'desc')->get();

        $userCreate = $bast->userCreate ? $bast->userCreate->name : '';
        $nomorBast = $bast->number_result ?? '';
        $signature = config('custom.customerSignature');
        $workOrder = WorkOrder::byCompany(Auth::user()->company_id)->whereDoesntHave('bast')
        ->whereHas('reportProject')
        ->orWhere('id', $bast->work_order_id)
        ->orderBy('created_at','desc')->get();

        $bastEmailRecords = BastEmailRecord::where('bast_id', $bast->id)
        ->orderBy('created_at', 'desc')
        ->paginate(2);

        return view('bast.show',compact('nomorBast','userCreate','project','bast','signature','workOrder','fileMerges','fileMergesChooice','bastEmailRecords'));
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

        $pdf = PDF::loadView('bast.pdf_download', compact(
            'bast','today','company'
        ));

        return $pdf->stream("test" . '.pdf');
        // return view('bast.pdf',compact('nomorBast','workOrder','userCreate','project','bast', 'today', 'company'));
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
        
        DB::beginTransaction();
        try {
            $bast->date = $request->input('date');
            $bast->work_order_id = $project->work_order_id;
            $bast->project_id = $request->input('project');
            $bast->number_purchase = $request->input('number_purchase');
            $bast->pic = $request->input('pic');
            $bast->customer_signature = $request->input('customer_signature');
            $bast->period = $request->input('period');
            
            $bast->user_updated_id = Auth::user()->id;
    
            $bast->save();
            $this->updateBudget($project->work_order_id, $request->input('project'));
            if($bast->project->reportProject)
            {
                $mergedFilePath = $this->mergePdfFiles($bast, $bast->project->reportProject);
            }
            
            DB::commit();
            return redirect()->to(route('bast.show',$bast->slug))->with('update', true);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollBack();
            Log::error($th->getMessage());
            return redirect()->back()->with('update',false);
        }
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
        $query = Bast::with('workOrder')->byCompany(Auth::user()->company_id)->orderBy('date','desc');


        // Map column indexes to column names (this may vary based on your table structure)
        $columnNames = ['date', 'number_result', 'slug'];

        // Define searchable columns
        $searchable = [
            'number_result',
            'date',
            'workOrder.number_result', // Relasi: mencari di dalam kolom work_order
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

        if(Access::can('show','basts'))
        {
            $pdf = 
            [
                'name' => 'Show',
                'route' => 'bast.show',
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


        $destroy = 
            [
                'name' => 'Create Invoice',
                'route' => 'invoice.store',
                'id' => true,
            ];

            array_push($actionButtons,$destroy);

        return datatablesFormaterWithSearchRelasion($query, $columnNames, $actionButtons, $searchable, $bootstrap);
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
        })->orderBy('created_at', 'desc');

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

    public function sendBastEmail(Request $request, $slug)
    {
        $request->validate([
            'to' => 'required|array',
            'to.*' => 'email',
            'cc' => 'nullable|array',
            'cc.*' => 'email',
            'subject' => 'required|string',
            'content' => 'required|string',
        ]);

        try {
            // Retrieve the BAST and the selected merged file
            $bast = Bast::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
            
            $smtpConfig = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');


            // Retrieve the file path and ensure it exists
            $filePath = Storage::path($bast->file_merge_path);
            if (!Storage::exists($bast->file_merge_path)) {
                return redirect()->back()->with('error', 'Selected file does not exist.');
            }

            // Send the email with the attachment
            $data = 
            [
                'subject' => $request->subject,
                'content' => $request->content,
            ];

            $nameFile = "BAST_".str_replace('/','-', $bast->number_result). '.pdf';
            $attachments = [
                $filePath => $nameFile,
            ];

            $smtpConfig = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
            $fromEmail = $smtpConfig['username'] ?? '';
            $fromName = $smtpConfig['name'] ?? '';
            $toEmails = $request->to;
            $toNames = $request->to;
            $ccEmails = $request->cc;
            $subject = $request->subject;
            $tamplate = "email.bast_email";
            $companyId = Auth::user()->company_id;
            
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
                $ccEmails,
                [],
                $attachments
            );

            // Simpan record ke database
            $bastEmailRecord = new BastEmailRecord();
            $bastEmailRecord->bast_id = $bast->id;
            $bastEmailRecord->user_id = Auth::user()->id;
            $bastEmailRecord->to = json_encode($request->to);
            $bastEmailRecord->cc = json_encode($request->cc);
            $bastEmailRecord->subject = $request->subject;
            $bastEmailRecord->content = $request->content;
            $bastEmailRecord->save();

            return redirect()->back()->with('successEmail', true);
        } catch (\Exception $e) {
            // dd($e);
            \Log::error('Failed to send BAST email: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to send the email.');
        }
    }

    // Main controller method
    // private function merge($bast)
    // {
    //     try {
    //         // Get BAST by company and slug
    //         $reportProject = $bast->project->reportProject;

    //         if (!$reportProject) {
    //             throw new \Exception('No report project found for the selected BAST.');
    //         }

    //         // Call the private method to handle PDF merging
    //         $mergedFilePath = $this->mergePdfFiles($bast, $reportProject);

    //         if ($mergedFilePath) 
    //         {
    //             // Save record to bast_file_merges table

    //             return true;
    //         }

    //         return false;

    //     } catch (\Exception $e) {
    //         \Log::error('Failed to merge PDFs for BAST: ' . $e->getMessage());
    //         return redirect()->back()->with('error', 'An error occurred while merging PDF files.');
    //     } catch (\Throwable $th) {
    //         \Log::error('Unexpected error in PDF merging: ' . $th->getMessage());
    //         return redirect()->back()->with('error', 'An unexpected error occurred.');
    //     }
    // }

    // Private function to merge PDF files
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
            foreach ($reportProject->reportProjectDetail as $detail) {
                $filePath = storage_path('app/public/reports/' . $detail->file);
                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

                if (file_exists($filePath) && strtolower($fileExtension) === 'pdf') {
                    $pdfFiles[] = $filePath;
                }
            }
            // Generate a new PDF from the  array of PDF view
            $today = Carbon::now()->format('d M Y');
            $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');

            $additionalPdf = PDF::loadView('bast.pdf_download', compact('bast', 'today', 'company'));

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
