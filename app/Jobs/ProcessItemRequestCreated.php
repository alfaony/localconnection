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
use App\Models\Role;
use App\Schemas\RoleSchema;

use App\Events\ChatMessageSent;

use App\Helpers\InboxHelper;

class ProcessItemRequestCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $itemRequestId;
    protected $assigned_pic_id;
    protected $product_supplier_id;

    public function __construct($itemRequestId, $assigned_pic_id = null, $product_supplier_id = null)
    {
        $this->itemRequestId = $itemRequestId;
        $this->assigned_pic_id = $assigned_pic_id;
        $this->product_supplier_id = $product_supplier_id;
    }

    public function handle()
    {
        try {
            $itemRequest = ItemRequest::find($this->itemRequestId);
            $adminRole = Role::where('name', RoleSchema::ADMIN)->first();
            $rootRole = Role::where('name', RoleSchema::ROOT)->first();
            
            // ✅ Cari Sprinter (semua user) jika assigned_pic_id belum diisi
            if (!$this->assigned_pic_id) {
                $users = User::where('company_id', $itemRequest->company_id)
                ->whereHas('role.permissions', function ($q) {
                    $q->where('method', 'as_sprinter')
                    ->where('table', 'item_requests');
                })
                ->withCount('assignedRequests') // hitung semua assigned tanpa filter status
                ->get();

                if (!$users->isEmpty()) 
                {
                    $min = $users->min('assigned_requests_count');
                    $candidates = $users->where('assigned_requests_count', $min);
                    $selected = $candidates->random();
                    // dd($selected->name);
            
                    $itemRequest->assigned_pic_id = $this->assigned_pic_id ?? $selected->id;
                    $itemRequest->save();
                    
                }else
                {
                    $selected = $itemRequest->requester->approvement_user_id ? $itemRequest->requester->approver : $itemRequest->requester ;
                    $itemRequest->assigned_pic_id = $this->assigned_pic_id ?? $selected->id;
                    $itemRequest->save();
                }
            } else 
            {
                $selected = $this->assigned_pic_id ? User::find($this->assigned_pic_id) : $itemRequest->requester;
                $itemRequest->assigned_pic_id = $this->assigned_pic_id;
                $itemRequest->save();
            }

             $user = User::where('company_id', $itemRequest->company_id)
                    ->where(function ($query) use ($rootRole, $adminRole) {
                        $query->where('role_id', $rootRole->id)
                            ->orWhere('role_id', $adminRole->id);
                    })
                    ->first();
            $directUrl = route('item-request.show',$itemRequest->id);
            $message = "Ada item request baru! Silahkan klik link berikut untuk melihat detail:";

            $inboxHelper = new InboxHelper();
            $inboxHelper->sent(
                $selected->id, 
                $user->id,
                $message, 
                $directUrl,
                false,
                'high'
            );

            // ✅ Cari Vendor Potensial (berdasarkan kategori)
            $vendors = collect();
            if (!$itemRequest->product_supplier_id || (is_array($itemRequest->product_supplier_id) && count($itemRequest->product_supplier_id) === 0)) 
            {
                $vendors = ProductSupplier::whereHas('supplierCategories', function ($q) use ($itemRequest) {
                    $q->where('supplier_category_id', $itemRequest->supplier_category_id);
                })
                ->where('supplier_type_id', $itemRequest->supplier_type_id)
                ->where('company_id', $itemRequest->company_id)
                ->get();
            }else
            {
                $vendors = ProductSupplier::whereIn('id', $itemRequest->product_supplier_id)->get();
            }
            
            foreach ($vendors as $vendor) 
            {
                PotentialVendor::firstOrCreate([
                    'company_id' => $itemRequest->company_id,
                    'item_request_id' => $itemRequest->id,
                    'product_supplier_id' => $vendor->id,
                ], [
                    'responded' => false
                ]);
            }

            broadcast(new ChatMessageSent(
                "Admin",
                "Sprinter terpilih adalah " . $selected->name,
                now(),
                $itemRequest->id,
                $user->id
            ))->toOthers();
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error($th);
        }
    }
}
