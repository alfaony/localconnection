<?php

namespace App\Http\Controllers;

use App\Models\HotspotVoucherBatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class HotspotVoucherController extends Controller
{
    /**
     * Print semua voucher dalam satu batch sebagai PDF.
     */
    public function printBatch(string $batchId): Response
    {
        $batch = HotspotVoucherBatch::with(['internetPackage', 'hotspotServer', 'vouchers'])
            ->where('company_id', auth()->user()->company_id)
            ->findOrFail($batchId);

        $pdf = Pdf::loadView('pdf.hotspot-voucher-batch', compact('batch'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("voucher-{$batch->id}.pdf");
    }
}
