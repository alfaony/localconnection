<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\ItemRequest;
use App\Models\User;
use App\Models\Supplier;
use App\Models\PotentialVendor;
use App\Models\SupplierCategory;
use App\Models\ProductSupplier;

class ProcessItemRequestCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $itemRequestId;

    public function __construct($itemRequestId)
    {
        $this->itemRequestId = $itemRequestId;
    }

    public function handle()
    {
        try {
            $itemRequest = ItemRequest::find($this->itemRequestId);
            
            // ✅ Cari Sprinter (semua user)
            $users = User::where('company_id', $itemRequest->company_id)
                ->withCount(['assignedRequests' => function ($q) {
                    $q->whereIn('status', ['REQUESTED','FIND_PIC','FIND_VENDOR','WAITING_PAYMENT', 'PAID', 'READY_TO_SEND']);
                }])
                ->get();
    
            $min = $users->min('assigned_requests_count');
            $candidates = $users->where('assigned_requests_count', $min);
            $selected = $candidates->random();
    
            $itemRequest->assigned_pic_id = $selected->id;
            $itemRequest->save();
    
            // ✅ Cari Vendor Potensial (berdasarkan kategori)
            $vendors = ProductSupplier::whereHas('supplierCategories', function ($q) use ($itemRequest) {
                $q->where('supplier_categories.id', $itemRequest->supplier_category_id);
            })->get();
            
            foreach ($vendors as $vendor) {
                PotentialVendor::firstOrCreate([
                    'company_id' => $itemRequest->company_id,
                    'item_request_id' => $itemRequest->id,
                    'product_supplier_id' => $vendor->id,
                ], [
                    'responded' => false
                ]);
            }
        } catch (\Throwable $th) {
            //throw $th;
            dd($th);
            Log::error($th);
        }
    }
}
