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

//new
use App\Models\UsedItemMedia;
use App\Models\UsedLaptopMedia;
use App\Models\UsedItemCheck;
use App\Models\UsedLaptopCheck;
use App\Models\UsedItemRepair;
use App\Models\UsedLaptopRepair;
use App\Helpers\WebhookHelper;
use App\Models\WebhookSetting;
use App\Http\Resources\UsedLaptopResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrScanApiController extends Controller
{

    protected $appName = 'used_laptops';

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


    public function updateUsedLaptop(Request $request, $slug)
    {
        $laptop = UsedLaptop::where('slug', $slug)->byCompany(Auth::user()->company_id)->first();

        if (!$laptop) {
            return response()->json(['success' => false, 'message' => 'Laptop tidak ditemukan'], 404);
        }

        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'warehouse_id'     => 'nullable|exists:warehouses,id',
                'zone_id'          => 'required_with:warehouse_id|nullable|exists:zones,id',
                'rack_id'          => 'required_with:warehouse_id|nullable|exists:racks,id',
                'weight'           => 'nullable|numeric|min:0',
                'name'             => 'required|string|max:255',
                'brand'            => 'required|string|max:255',
                'serial_number'    => [
                    'required', 'string', 'max:255',
                    function ($attribute, $value, $fail) use ($laptop) {
                        if (UsedLaptop::where('serial_number', $value)->byCompany(Auth::user()->company_id)->where('id', '!=', $laptop->id)->exists()) {
                            $fail('Serial number sudah terdaftar di sistem.');
                        }
                    }
                ],
                'processor'        => 'required|string|max:255',
                'ram'              => 'required|string|max:255',
                'ssd'              => 'required|string|max:255',
                'gpu'              => 'nullable|string|max:255',
                'operating_system' => 'nullable|string|max:255',
                'purchase_price'   => 'required|numeric|min:0',
                'notes'            => 'nullable|string',
                'photos'           => 'nullable|array',
                'photos.*'         => 'image|mimes:jpeg,png,jpg,gif|max:10240',
                'check_items'      => 'nullable|array', // Sudah nullable
                'repairs'          => 'nullable|array',
                'is_sold'          => 'nullable|string',
            ]);

            $oldRackId = $laptop->rack_id;

            $laptop->update([
                'is_sold'          => $validated['is_sold'] ?? $laptop->is_sold,
                'weight'           => $validated['weight'] ?? $laptop->weight,
                'serial_number'    => $validated['serial_number'],
                'name'             => $validated['name'],
                'brand'            => $validated['brand'],
                'processor'        => $validated['processor'],
                'ram'              => $validated['ram'],
                'ssd'              => $validated['ssd'],
                'gpu'              => $validated['gpu'] ?? $laptop->gpu,
                'operating_system' => $validated['operating_system'] ?? $laptop->operating_system,
                'purchase_price'   => $validated['purchase_price'],
                'notes'            => $validated['notes'] ?? $laptop->notes,
                'rack_id'          => $validated['rack_id'] ?? $laptop->rack_id
            ]);

            $rackChanged = $oldRackId != $laptop->rack_id;

            // Handle Photos
            if ($request->hasFile('photos')) {
                $currentMaxOrder = $laptop->media()->max('order') ?? -1;
                foreach ($request->file('photos') as $offset => $photo) {
                    $path = $photo->store('used-laptop');
                    UsedLaptopMedia::create([
                        'used_laptop_id' => $laptop->id,
                        'file_path'      => $path,
                        'order'          => $currentMaxOrder + $offset + 1,
                    ]);
                }
            }

            // Checklist logic - Ditambah pengecekan has() agar tidak error jika null
            if ($request->has('check_items')) {
                foreach ($request->input('check_items', []) as $checkItemId => $checkData) {
                    if (isset($checkData['condition']) && !empty($checkData['condition'])) {
                        UsedLaptopCheck::updateOrCreate(
                            ['used_laptop_id' => $laptop->id, 'master_check_item_id' => $checkItemId],
                            ['status' => $checkData['condition'], 'notes' => $checkData['notes'] ?? null, 'checked_at' => now()]
                        );
                    } else {
                        UsedLaptopCheck::where('used_laptop_id', $laptop->id)
                            ->where('master_check_item_id', $checkItemId)
                            ->delete();
                    }
                }
            }

            // Repair logic
            $submittedRepairIds = [];
            
            if ($request->has('repairs')) {
                foreach ($request->input('repairs') as $repairData) {
                    if (!empty($repairData['description'])) {
                        $repair = UsedLaptopRepair::updateOrCreate(
                            ['id' => $repairData['id'] ?? null],
                            ['used_laptop_id' => $laptop->id, 'repair_item' => $repairData['description'], 'cost' => $repairData['cost'] ?? 0]
                        );
                        $submittedRepairIds[] = $repair->id;
                    }
                }
            }
            $laptop->repairs()->whereNotIn('id', $submittedRepairIds)->delete();
            $appName = 'used_laptops';
            // Webhook trigger
            $shouldRun = WebhookSetting::byCompany(Auth::user()->company_id)->hasApp($appName)->exists();
            if ($shouldRun && ($laptop->rack_id || $rackChanged)) {
                $payload = (new UsedLaptopResource($laptop))->resolve();
                WebhookHelper::sendWebhook(Auth::user()->company_id, $appName, 'update', $payload);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Laptop berhasil diperbarui']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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


    public function updateUsedItem(Request $request, $slug)
{
    $item = UsedItem::where('slug', $slug)->byCompany(Auth::user()->company_id)->first();

    if (!$item) {
        return response()->json(['success' => false, 'message' => 'Item tidak ditemukan'], 404);
    }

    DB::beginTransaction();
    try {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'serial_number'  => 'nullable|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'notes'          => 'nullable|string',
            'rack_id'        => 'nullable|exists:racks,id',
            'category_ids'   => 'nullable|array',
            'category_ids.*' => 'nullable|exists:item_categories,id',
            'check_items'    => 'nullable|array', 
            'repairs'        => 'nullable|array', // Repairs nullable
        ]);

        $item->update([
            'name'           => $validated['name'],
            'serial_number'  => $validated['serial_number'],
            'purchase_price' => $validated['purchase_price'],
            'notes'          => $validated['notes'] ?? null,
            'rack_id'        => $validated['rack_id'] ?? $item->rack_id,
        ]);

        $item->categories()->sync($request->category_ids ?? []);

        // Photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('used-items');
                UsedItemMedia::create(['used_item_id' => $item->id, 'file_path' => $path]);
            }
        }

        // 1. Checklist Logic (Nullable)
        $checkItems = $request->input('check_items', []);
        foreach ($checkItems as $id => $data) {
            if (isset($data['condition'])) {
                UsedItemCheck::updateOrCreate(
                    ['used_item_id' => $item->id, 'master_check_item_id' => $id],
                    [
                        'status' => $data['condition'], 
                        'notes' => $data['notes'] ?? null, 
                        'checked_at' => now()
                    ]
                );
            }
        }

        // 2. Repairs Logic (Nullable & Sync)
        $existingRepairIds = $item->repairs()->pluck('id')->toArray();
        $submittedRepairIds = [];

        if ($request->has('repairs')) {
            foreach ($request->input('repairs') as $repairData) {
                // Hanya proses jika deskripsi tidak kosong
                if (!empty($repairData['description'])) {
                    $repair = UsedItemRepair::updateOrCreate(
                        ['id' => $repairData['id'] ?? null],
                        [
                            'used_item_id' => $item->id,
                            'repair_item'  => $repairData['description'],
                            'cost'         => $repairData['cost'] ?? 0,
                        ]
                    );
                    $submittedRepairIds[] = $repair->id;
                }
            }
        }

        // Hapus repair yang ada di DB tapi tidak ada di input (User menghapus baris repair)
        $repairsToDelete = array_diff($existingRepairIds, $submittedRepairIds);
        if (!empty($repairsToDelete)) {
            UsedItemRepair::destroy($repairsToDelete);
        }

        DB::commit();
        return response()->json(['success' => true, 'message' => 'Item berhasil diperbarui']);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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

    public function updateProductStore(Request $request, $code)
    {
        $product = ProductStore::where('barcode', $code)
            ->byCompany(Auth::user()->company_id)
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        }

        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'name'                      => 'required|string|max:255',
                'category_product_store_id' => 'nullable|exists:category_product_stores,id', // Diubah ke nullable
                'brand_product_store_id'    => 'nullable|exists:brand_product_stores,id',
                'selling_price'             => 'required|integer',
                'warehouse_id'              => 'nullable|exists:warehouses,id',
                'zone_id'                   => 'nullable|exists:zones,id',
                'rack_id'                   => 'nullable|exists:racks,id',
                'variant'                   => 'nullable|string|max:255',
                'specification'             => 'nullable|string',
                'length'                    => 'nullable|numeric',
                'width'                     => 'nullable|numeric',
                'height'                    => 'nullable|numeric',
                'weight'                    => 'nullable|numeric',
                'photos'                    => 'nullable|array',
                'photos.*'                  => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'photo_captions'            => 'nullable|array',
            ]);

            $oldRackId = $product->rack_id;

            // Update Data Produk
            $product->update([
                'name'                      => $validated['name'],
                'category_product_store_id' => $validated['category_product_store_id'] ?? $product->category_product_store_id,
                'brand_product_store_id'    => $validated['brand_product_store_id'] ?? $product->brand_product_store_id,
                'selling_price'             => $validated['selling_price'],
                'variant'                   => $validated['variant'] ?? null,
                'specification'             => $validated['specification'] ?? null,
                'length'                    => $validated['length'] ?? 0,
                'width'                     => $validated['width'] ?? 0,
                'height'                    => $validated['height'] ?? 0,
                'weight'                    => $validated['weight'] ?? 0,
                'dimension'                 => ($validated['length'] ?? 0) . ' x ' . ($validated['width'] ?? 0) . ' x ' . ($validated['height'] ?? 0),
                'rack_id'                   => $validated['rack_id'] ?? $product->rack_id,
                'user_modified_id'          => Auth::id(),
            ]);

            $rackChanged = $oldRackId != $product->rack_id;

            // Handle Photo Uploads ke S3 (Sesuai logic awal Mas Heri)
            if ($request->hasFile('photos')) {
                $currentMaxOrder = $product->media()->max('order') ?? -1;
                
                foreach ($request->file('photos') as $index => $photo) {
                    // Generate filename unik
                    $fileName = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    
                    // Upload langsung ke S3
                    $path = $photo->storeAs(
                        'product-store-media', 
                        $fileName, 
                        's3'
                    );

                    // Simpan ke table media (Asumsi model: ProductStoreMedia)
                    $product->media()->create([
                        'file_path' => $path,
                        'caption'   => $request->photo_captions[$index] ?? null,
                        'order'     => $currentMaxOrder + $index + 1,
                    ]);
                }
            }

            

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => 'Produk toko berhasil diperbarui',
                'data'    => $product->load('media')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("API ProductStore Update Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui produk: ' . $e->getMessage()], 500);
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
            $quote = Quote::with(['customer', 'userCreate', 'quoteProduct.product'])->where('number_result', $searchNumber)->firstOrFail();
            $userCompanyId = $quote->company_id ?? Auth::user()->company_id;

            $company = SettingCompany::byCompany($userCompanyId)->get()->pluck('field_value', 'field_title');
            $userCreate = $quote->userCreate ? $quote->userCreate->name : 'System';
            $today = \Carbon\Carbon::now()->format('d / m / Y');
            $counting = $this->generateCountingArray($quote);
            $path = public_path('logo/paraf.png');
            $base64 = '';

            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }

            $nomorQuote = $quote->number_result;
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('quote.pdfMobile', compact(
                'nomorQuote', 
                'quote', 
                'userCreate', 
                'company', 
                'today', 
                'counting',
                'base64'
            ));
            $pdf->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true, 
                    'defaultFont' => 'sans-serif'
                ]);

            $safeFileName = str_replace('/', '-', $quote->number_result);
            return $pdf->stream("Quotation_{$safeFileName}.pdf");

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
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