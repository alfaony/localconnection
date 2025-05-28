<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemPurchase;
use App\Models\Payment;
use App\Models\ItemRequest;
use App\Jobs\ProcessItemRequestCreated;

class ItemPurchaseController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            "payment_term_date" => "required|date",
            'item_request_id' => 'required|integer|exists:item_requests,id',
            'product_supplier_id' => 'required|integer|exists:product_suppliers,id',
            'actual_price' => 'required|numeric|min:0',
            'bon_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'payment_method' => "nullable|string",
            'rekening_number' => "nullable|string",
            "note" => "nullable|string"
        ]);
        try {
            $itemRequest = ItemRequest::find($validatedData['item_request_id']);
    
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
                return $this->itemRequestClose($validatedData['item_request_id']);
            }
    
    
            return response()->json(['message' => 'Item purchase created successfully', 'data' => $itemPurchase], 201);
        } catch (\Throwable $th) {
            //throw $th;
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

        try {
            $itemPurchase = ItemPurchase::where('id', $id)->firstOrFail();
    
            $path = $request->file('proof_image')->store('bukti_transfer', 'public');
    
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
    
            $itemRequest = ItemRequest::where('id', $itemPurchase->item_request_id)->update(['status' => 'WAITING_DELIVERY_CONFIRMATION']);
    
    
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    private function itemRequestClose($id)
    {
        return ItemRequest::where('id',$id)->update(['is_open' => false]);
    }

}
