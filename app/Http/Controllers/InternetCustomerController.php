<?php

namespace App\Http\Controllers;

use App\Exports\InternetCustomerExport;
use App\Jobs\ExportInternetCustomerJob;
use App\Models\InternetCustomerPurchase;
use App\Models\SettingCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;
use Carbon\Carbon;

class InternetCustomerController extends Controller
{
    public function downloadInvoice($purchaseId)
    {
        $purchase = InternetCustomerPurchase::with([
            'customer.internetPackage',
            'customer.userCustomer',
            'customer.company',
            'customer.province',
            'customer.city',
            'customer.district',
            'customer.subdistrict'
        ])->findOrFail($purchaseId);

        $company = SettingCompany::byCompany($purchase->customer->company_id)
            ->get()
            ->pluck('field_value', 'field_title');

        $invoiceNumber = 'INV-' . $purchase->id . '-' . $purchase->created_at->format('Ymd');
        $invoiceDate   = Carbon::now()->format('d M Y');

        $logoBase64 = null;
        if (isset($company['internet_icon']) && $company['internet_icon']) {
            try {
                if (Storage::exists($company['internet_icon'])) {
                    $imageContent = Storage::get($company['internet_icon']);
                    $mimeType     = Storage::mimeType($company['internet_icon']);
                    $logoBase64   = 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
                }
            } catch (\Exception $e) {
                $logoBase64 = null;
            }
        }

        $pdf      = PDF::loadView('internet-customer.invoice-pdf', compact(
            'purchase', 'company', 'invoiceNumber', 'invoiceDate', 'logoBase64'
        ));
        $filename = str_replace('/', '-', $invoiceNumber) . '.pdf';

        return $pdf->stream($filename);
    }

    public function export(Request $request, string $format)
    {
        $filters = $request->only([
            'search', 'selectedPackage', 'statusFilter',
            'customerTypeFilter', 'dateFrom', 'dateTo', 'dateType',
        ]);
        
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push(Auth::user()->company_id)->unique();
        ExportInternetCustomerJob::dispatch($filters, Auth::user(), $companyIds, $format);

        return redirect()->route('internet-customer.index')
            ->with('storeWithMessage', 'Export Internet Customer sedang diproses. Anda akan menerima notifikasi inbox setelah selesai.');
    }
}
