<?php

namespace App\Http\Controllers;

use App\Models\InternetCustomerPurchase;
use App\Models\SettingCompany;
use Illuminate\Http\Request;
use PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class InternetCustomerController extends Controller
{
    /**
     * Download invoice PDF for a purchase
     */
    public function downloadInvoice($purchaseId)
    {
        // Load purchase with relationships
        $purchase = InternetCustomerPurchase::with([
            'customer.internetPackage',
            'customer.userCustomer',
            'customer.company',
            'customer.province',
            'customer.city',
            'customer.district',
            'customer.subdistrict'
        ])->findOrFail($purchaseId);

        // Get company settings
        $company = SettingCompany::byCompany($purchase->customer->company_id)
            ->get()
            ->pluck('field_value', 'field_title');

        // Generate invoice number
        $invoiceNumber = 'INV-' . $purchase->id . '-' . $purchase->created_at->format('Ymd');
        $invoiceDate = Carbon::now()->format('d M Y');

        // Convert S3 logo to base64 for PDF embedding
        $logoBase64 = null;
        if (isset($company['internet_icon']) && $company['internet_icon']) {
            try {
                // Check if file exists in S3
                if (Storage::exists($company['internet_icon'])) {
                    $imageContent = Storage::get($company['internet_icon']);
                    $mimeType = Storage::mimeType($company['internet_icon']);
                    $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
                }
            } catch (\Exception $e) {
                // If error, just skip the logo
                $logoBase64 = null;
            }
        }

        // Load PDF view
        $pdf = PDF::loadView('internet-customer.invoice-pdf', compact(
            'purchase',
            'company',
            'invoiceNumber',
            'invoiceDate',
            'logoBase64'
        ));

        // Filename
        $filename = str_replace('/', '-', $invoiceNumber) . '.pdf';

        // Stream PDF
        return $pdf->stream($filename);
    }
}
