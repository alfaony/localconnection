<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

// Models
use App\Models\UsedLaptop;
use App\Models\UsedItem;
use App\Models\ProductStore;
use App\Models\InternetCustomer;
use App\Models\Quote;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SettingCompany;

class QrScanApiController extends Controller
{
    /**
     * Get Detail Used Laptop by Slug
     */
    public function getUsedLaptopDetail($slug)
    {
        try {
            $laptop = UsedLaptop::where('slug', $slug)
                ->byCompany(Auth::user()->company_id)
                ->with([
                    'media' => fn($q) => $q->orderBy('order', 'asc'),
                    'rack.zone.warehouse',
                    'checks.item',
                    'repairs',
                    'user'
                ])
                ->firstOrFail();

            $data = [
                ...$laptop->toArray(),

                // MEDIA 
                'medias' => $laptop->media->map(fn($m) => [
                    'file_path' => $m->file_path,
                    'caption'   => $m->caption,
                    'order'     => $m->order,
                ]),

                // LOCATION
                'warehouse_name' => optional($laptop->rack?->zone?->warehouse)->name,
                'zone_name'      => optional($laptop->rack?->zone)->name,
                'rack_name'      => optional($laptop->rack)->name,

                // CHECKLIST
                'checks' => $laptop->checks->map(fn($c) => [
                    'name'   => $c->item?->name,
                    'status' => $c->status,
                    'notes'  => $c->notes,
                ]),

                // REPAIRS
                'repairs' => $laptop->repairs->map(fn($r) => [
                    'repair_item' => $r->repair_item,
                    'cost'        => $r->cost,
                ]),

                // PRICE
                'suggested_price' => $laptop->suggested_selling_price,
                'jakarta_price'   => $laptop->jakarta_price,
                'jambi_price'     => $laptop->jambi_price,

                'sale_status' => $laptop->sale_status,
                'qc_status'   => $laptop->qc_status,
                'user_name'   => optional($laptop->user)->name,
            ];

            return response()->json(['success' => true, 'data' => $data], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Laptop tidak ditemukan'], 404);
        }
    }

    /**
     * Get Detail Used Item by Slug
     */
    public function getUsedItemDetail($slug)
    {
        try {
            $item = UsedItem::where('slug', $slug)
                ->byCompany(Auth::user()->company_id)
                ->with([
                    'media',
                    'rack.zone.warehouse',
                    'checks.item',
                    'repairs',
                    'user'
                ])
                ->firstOrFail();

            $data = array_merge(
                $item->toArray(),
                [
                    'medias' => $item->media->map(fn($m) => [
                        'file_path' => $m->file_path,
                        'caption'   => $m->caption,
                        'order'     => $m->order,
                    ])->values(),

                    'warehouse_name' => optional($item->rack?->zone?->warehouse)->name,
                    'zone_name'      => optional($item->rack?->zone)->name,
                    'rack_name'      => optional($item->rack)->name,

                    'checks' => $item->checks->map(fn($c) => [
                        'name'   => $c->item?->name,
                        'status' => $c->status,
                        'notes'  => $c->notes,
                    ])->values(),

                    'repairs' => $item->repairs->map(fn($r) => [
                        'repair_item' => $r->repair_item,
                        'cost'        => $r->cost,
                    ])->values(),
                    'suggested_price' => $item->suggested_selling_price,
                    'sale_status' => $item->sale_status,
                    'user_name'   => optional($item->user)->name,
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan'], 404);
        }
    }

    /**
     * Get Detail Produk Toko by Barcode
     */
    public function getProductStoreDetail($code)
    {
        try {
            $product = ProductStore::where('barcode', $code)
                ->byCompany(Auth::user()->company_id)
                ->with([
                    'category',
                    'brand',
                    'media' => fn($q) => $q->orderBy('order', 'asc'),
                    'creator'
                ])
                ->firstOrFail();

            $data = [
                ...$product->toArray(),

                'medias' => $product->media->map(fn($m) => [
                    'file_path' => $m->file_path,
                    'caption'   => $m->caption,
                    'order'     => $m->order,
                ]),
                'user_name' => optional($product->creator)->name,
            ];

            return response()->json(['success' => true, 'data' => $data], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        }
    }

    /**
     * Get Detail Internet Customer by Code (Sesuai request dropdown)
     */
    public function getInternetCustomerDetail($code)
    {
        try {
            // Mencari berdasarkan customer_code (atau sesuaikan nama kolom kodenya)
            $customer = InternetCustomer::where('code', $code)
                ->byCompany(Auth::user()->company_id)
                ->with([
                    'province:id,name', 'city:id,name', 'district:id,name', 'subdistrict:id,name',
                    'promo:id,name', 'odp:id,name', 'router:id,name', 'internetPackage',
                    'userCustomer', 'installation', 'installation.medias'
                ])->firstOrFail();

            return response()->json(['success' => true, 'data' => $customer], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Customer tidak ditemukan'], 404);
        }
    }

    public function getQuotationPdf($quoteNumber)
    {
        try {
            $searchNumber = str_replace('-', '/', $quoteNumber);
            $quote = Quote::with(['customer', 'userCreate', 'quoteProduct'])->where('number_result', $searchNumber)->firstOrFail();

            $userCompanyId = Auth::user()->company_id;

            $product = Product::withTrashed()->byCompany($userCompanyId)->get();
            $company = SettingCompany::byCompany($userCompanyId)->get()->pluck('field_value', 'field_title');
            $customer = Customer::byCompany($userCompanyId)->get();
            
            $nomorQuote = $quote->number_result;
            $today = \Carbon\Carbon::now()->format('d / m / Y');
            $userCreate = $quote->userCreate ? $quote->userCreate->name : 'System';

            $counting = $this->generateCountingArray($quote);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('quote.pdfApi', compact(
                'product', 'customer', 'nomorQuote', 'quote', 
                'userCreate', 'company', 'today', 'counting'
            ));

            $safeFileName = str_replace('/', '-', $nomorQuote);
            return $pdf->download("Quotation_{$safeFileName}.pdf");

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal generate PDF: ' . $e->getMessage()], 500);
        }
    }

    private function generateCountingArray($quote)
    {
        $total = $quote->quoteProduct()->sum('sub_total') ?? 0;
        $taxable_total = $quote->quoteProduct()->where('is_taxable', true)->sum('sub_total') ?? 0;
        $charges = $quote->charges ?? 0;
        $discount = $quote->discount ?? 0;
        
        $totalAll = ($total + $charges) - $discount;
        $serviceFee = $quote->service_fee != 0 ? round(($totalAll * $quote->service_fee) / 100) : 0;
        
        $taxableRatio = $total > 0 ? $taxable_total / $total : 0;
        $taxableServiceFee = round($serviceFee * $taxableRatio);
        $taxableBase = ($taxable_total + $charges - $discount) + $taxableServiceFee;
        
        $ppn = $quote->tax != 0 ? round(($taxableBase * $quote->tax) / 100) : 0;
        $grandTotal = $totalAll + $serviceFee + $ppn;

        return [
            'total'                  => $total,
            'taxable_total'          => $taxable_total,
            'tax_percentage'         => $quote->tax ?? 0,
            'ppn'                    => 'Rp. '.number_format($ppn, 0, ',', '.'), // Ini yang tadi error
            'service_fee_percentage' => $quote->service_fee ?? 0,
            'service_fee'            => 'Rp. '.number_format($serviceFee, 0, ',', '.'),
            'discount'               => $discount,
            'charges'                => $charges,
            'subtotal'               => $total,
            'grand_total'            => 'Rp. '.number_format($grandTotal, 0, ',', '.'),
            'grand_total_raw'        => $grandTotal,
            'remaining_budget'       => 0,
        ];
    }
}