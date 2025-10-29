<?php

namespace App\Observers;

use App\Models\Bast;
use App\Models\Invoice;
use App\Http\Controllers\InvoiceController;
use App\Jobs\MergeInvoicePdfJob;
use Illuminate\Support\Facades\Log;

class BastObserver
{
    protected $invoiceController;

    public function __construct(InvoiceController $invoiceController)
    {
        $this->invoiceController = $invoiceController;
    }

    public function saved(Bast $bast)
    {
        if($bast->invoice && $bast->file_merge_path) 
        {
            $invoice = $bast->invoice;
            // // Call the mergePdf method from InvoiceController
            // $mergedPdfPath = $this->invoiceController->mergePdf($invoice, $bast->file_merge_path);
            
            // // Update the file_merge_path in the Invoice
            // if ($mergedPdfPath) 
            // {
            //     $invoice->file_merge_path = $mergedPdfPath;
            //     $invoice->save();
            // }
        MergeInvoicePdfJob::dispatch(
            $invoice->id, 
            $bast->file_merge_path,
            $invoice->userCreate->company_id
        )->delay(now()->addSeconds(5));
            
            Log::info("Merge PDF job dispatched", [
                'bast_id' => $bast->id,
                'invoice_id' => $bast->invoice->id,
                'company_id' => $bast->invoice->company_id,
                'bast_file_path' => $bast->file_merge_path
            ]);
        }
    }
}
