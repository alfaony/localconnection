<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\ItemPurchase;
use App\Models\Payment;
use App\Models\ItemRequest;
use App\Models\User;
use App\Models\ProductSupplier;
use App\Models\PotentialVendor;

use App\Jobs\ProcessItemRequestCreated;

use App\Helpers\InboxHelper;

use App\Models\Role;
use App\Schemas\RoleSchema;

use App\Events\ChatMessageSent;
use App\Jobs\ItemRequestClose;

class ItemPurchaseController extends Controller
{
    public function store(Request $request)
    {   
        // Validate the request

        $validatedData = $request->validate([
            "payment_term_date" => "required|date",
            'item_request_id' => 'required|integer|exists:item_requests,id',
            'product_supplier_id' => 'nullable|integer|exists:product_suppliers,id',
            'actual_price' => 'required|numeric|min:0',
            'bon_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'payment_method' => "nullable|string",
            'rekening_number' => "nullable|string",
            "note" => "nullable|string"
        ]);

        if($request->product_supplier_id == null || $request->product_supplier_id == "")
        {
            $validatedData = array_merge($validatedData, $request->validate([
                   'owner_name' => 'required|string|max:255',
                   'store_name' => 'required|string|max:255',
                   'phone_number' => 'required|string|max:20',
                   'location' => 'required|string|max:255',
               ]));
        }

        DB::beginTransaction();
        try {
            $itemRequest = ItemRequest::find($validatedData['item_request_id']);

            if($request->product_supplier_id == null || $request->product_supplier_id == "")
            {
                $newVendor = $this->addVendor($request, $itemRequest);
                $validatedData['product_supplier_id'] = $newVendor->id;
            }
            
            // dd($request->all());
            $bonPhoto = $request->file('bon_photo');
            $bonPhotoName = "bon-photo-{$itemRequest->id}.{$bonPhoto->getClientOriginalExtension()}";
            $path = $bonPhoto->storeAs('public/item-purchase-bon-photos', $bonPhotoName);
    
            $validatedData['bon_photo'] = $path;
            $validatedData['company_id'] = auth()->user()->company_id;
            $validatedData['sprinter_id'] = auth()->user()->id;
    
            // Create a new item purchase
            $itemPurchase = new ItemPurchase();
            $itemPurchase->fill($validatedData);
            $itemPurchase->save();
            
            ItemRequest::where('id', $request->item_request_id)->update(['status' => 'WAITING_PAYMENT']);
    
            if($request->is_finished)
            {
                $itemRequest->is_open = 0;
                $itemRequest->save();
                Dispatch(new ItemRequestClose($itemRequest, Auth::user()->id)); // itemRequestClose($itemRequest);
            }
    
                
            DB::commit();
            return response()->json(['message' => 'Item purchase created successfully', 'data' => $itemPurchase], 201);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            // dd($th);
            Log::error($th);
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $validatedData = $request->validate([
            'company_id' => 'sometimes|required|integer|exists:companies,id',
            'item_request_id' => 'sometimes|required|integer|exists:item_requests,id',
            'vendor_id' => 'sometimes|required|integer|exists:vendors,id',
            'sprinter_id' => 'sometimes|required|integer|exists:sprinters,id',
            'actual_price' => 'sometimes|required|numeric|min:0',
            'bon_photo' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        try {
            //code...
            // Find the item purchase
            $itemPurchase = ItemPurchase::find($id);
    
            // Update the item purchase
            if (isset($validatedData['company_id'])) {
                $itemPurchase->company_id = $validatedData['company_id'];
            }
            if (isset($validatedData['item_request_id'])) {
                $itemPurchase->item_request_id = $validatedData['item_request_id'];
            }
            if (isset($validatedData['vendor_id'])) {
                $itemPurchase->vendor_id = $validatedData['vendor_id'];
            }
            if (isset($validatedData['sprinter_id'])) {
                $itemPurchase->sprinter_id = $validatedData['sprinter_id'];
            }
            if (isset($validatedData['actual_price'])) {
                $itemPurchase->actual_price = $validatedData['actual_price'];
            }
            if (isset($validatedData['bon_photo'])) {
                $itemPurchase->bon_photo = $validatedData['bon_photo'];
            }
            $itemPurchase->save();
    
    
            return response()->json(['message' => 'Item purchase updated successfully', 'data' => $itemPurchase], 200);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function payment(Request $request, $id)
    {
        $request->validate([
            'proof_image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $itemPurchase = ItemPurchase::where('id', $id)->firstOrFail();
    
            $path = $request->file('proof_image')->store('bukti_transfer');
    
            Payment::create([
                'company_id' => auth()->user()->company_id,
                'item_purchase_id' => $itemPurchase->id,
                'finance_id' => auth()->id(),
                'proof_image' => $path,
                'paid_at' => now(),
            ]);
    
            // Update status if needed
            $itemPurchase->status='paid';
            $itemPurchase->save();
            

            if($itemPurchase->itemRequest->is_complete_payment)
            {
                $itemRequest = ItemRequest::where('id', $itemPurchase->item_request_id)->update(['status' => 'WAITING_DELIVERY_CONFIRMATION']);
    
                $message = "Request Penerbitan Air Way Bill (Resi) pada request #{$itemPurchase->itemRequest->item_name} #id {$itemPurchase->itemRequest->id}";
                $directUrl = route('item-request.show', $itemPurchase->itemRequest->id);
                $this->sentInbox($itemPurchase->itemRequest->user_id,$message, $directUrl, $itemPurchase->itemRequest->id);
            }

            
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollBack();
            Log::error($th);
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function complete($id)
    {
        try {
            $itemRequest = ItemRequest::findOrFail($id);
            // $this->itemRequestClose($itemRequest);
            $itemRequest->is_open = 0;
            $itemRequest->save();
            Dispatch(new ItemRequestClose($itemRequest, Auth::user()->id)); // itemRequestClose($itemRequest);
            return response()->json(['message' => 'Permintaan diselesaikan']);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            Log::error($th);
            return response()->json(['message' => 'Permintaan diselesaikan']);
        }
    }

    private function itemRequestClose($itemRequest)
    {
        // return ItemRequest::where('id',$id)->update(['is_open' => false]);
        $financeRole = Role::where('name', RoleSchema::FINANCE)->first() ?? NULL;
        $managerFinance = Role::where('name', "MANAGER FINANCE")->first() ?? NULL;

        $adminRole = Role::where('name', RoleSchema::ADMIN)->first();
        $rootRole = Role::where('name', RoleSchema::ROOT)->first();
        
         $financesApprove = User::whereHas('role.permissions', function ($q) {
            $q->where('method', 'as_finance')
            ->where('table', 'item_requests');
        })
        ->where(function ($q) use ($itemRequest) {
            $q->where('company_id', $itemRequest->company_id)
            ->orWhereHas('accessibleCompanies', function ($sub) use ($itemRequest) {
                $sub->where('companies.id', $itemRequest->company_id);
            });
        })
        ->get();

        if(!$financesApprove->isEmpty())
        {
            foreach ($financesApprove as $financeApprove)
            {
                $message = "Meminta pembayaran untuk item request #{$itemRequest->item_name}";
                $directUrl = route('item-request.show', $itemRequest->id);
                $this->sentInbox($financeApprove->id,$message, $directUrl);
            }
        }else
        {
            $finances = User::where('company_id', $itemRequest->company_id)
            ->where(function ($query) use ($financeRole, $managerFinance, $adminRole, $rootRole) {
                if ($financeRole) {
                    $query->where('role_id', $financeRole->id);
                }
                if ($managerFinance) 
                {
                    $query->orWhere('role_id', $managerFinance->id);
                }
                if(!$financeRole && !$managerFinance)
                {
                    $query->orWhere('role_id', $adminRole->id)->orWhere('role_id', $rootRole->id);
                }
                ;
            })
            ->get();
            foreach ($finances as $finance)
            {
                $message = "Meminta pembayaran untuk item request #{$itemRequest->item_name}";
                $directUrl = route('item-request.show', $itemRequest->id);
                $this->sentInbox($finance->id,$message, $directUrl);
            }
        }

            broadcast(new ChatMessageSent(
                    "",
                    $message,
                    now(),
                    $itemRequest->id,
                    Auth::user()->id
                ))->toOthers();

            $itemRequest->is_open = 0;
            return $itemRequest->save();
    }

    private function sentInbox($to,$message,$directUrl, $itemRequest = null)
    {
       
        if($itemRequest)
        {
            broadcast(new ChatMessageSent(
                "",
                $message,
                now(),
                $itemRequest,
                Auth::user()->id
            ))->toOthers();
        }

        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $to, 
            Auth::user()->id, 
            $message, 
            $directUrl
        );
        return true;
    }

    private function addVendor(Request $request, ItemRequest $itemRequest)
    {
        try {
            $productSupplier = ProductSupplier::create([
                'owner_name' => $request->owner_name,
                'company_id' => $itemRequest->company_id,
                'store_name' => $request->store_name,
                'phone_number' => $request->phone_number,
                'location' => $request->location,
            ]);
    
            $productSupplier->supplierCategories()->sync($itemRequest->supplier_category_id);
    
    
            PotentialVendor::create([
                'company_id' => $itemRequest->company_id,
                'item_request_id' => $itemRequest->id,
                'product_supplier_id' => $productSupplier->id,
            ]);

            return $productSupplier;
        } catch (\Throwable $th) {
            throw $th;
            Log::error($th);
        }
        
    }

}
