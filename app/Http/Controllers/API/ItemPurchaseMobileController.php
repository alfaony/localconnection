<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\ItemPurchase;
use App\Models\ItemRequest;
use App\Models\ProductSupplier;
use App\Jobs\ItemRequestClose;
use App\Models\Payment;
use App\Models\PotentialVendor;

class ItemPurchaseMobileController extends Controller
{
    public function store(Request $request)
    {
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

        if (empty($request->product_supplier_id)) {
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

            // Add vendor if needed
            if (empty($request->product_supplier_id)) {
                $newVendor = ProductSupplier::create([
                    'company_id' => Auth::user()->company_id,
                    'owner_name' => $request->owner_name,
                    'store_name' => $request->store_name,
                    'phone_number' => $request->phone_number,
                    'location' => $request->location,
                ]);
                $validatedData['product_supplier_id'] = $newVendor->id;
            }

            // Upload photo
            $bonPhoto = $request->file('bon_photo');
            $bonPhotoName = "bon-photo-{$itemRequest->id}." . $bonPhoto->getClientOriginalExtension();
            $path = $bonPhoto->storeAs('public/item-purchase-bon-photos', $bonPhotoName);

            $validatedData['bon_photo'] = $path;
            $validatedData['company_id'] = Auth::user()->company_id;
            $validatedData['sprinter_id'] = Auth::user()->id;

            // Save purchase
            $itemPurchase = ItemPurchase::create($validatedData);

            // Update request status
            $itemRequest->status = 'WAITING_PAYMENT';
            $itemRequest->save();

            if ($request->is_finished) {
                $itemRequest->is_open = 0;
                $itemRequest->save();
                dispatch(new ItemRequestClose($itemRequest, Auth::user()->id));
            }

            DB::commit();
            return response()->json([
                'message' => 'Item purchase created successfully',
                'data' => $itemPurchase
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'actual_price' => 'sometimes|required|numeric|min:0',
            'bon_photo' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg',
            'payment_method' => "nullable|string",
            'rekening_number' => "nullable|string",
            "note" => "nullable|string"
        ]);

        try {
            $itemPurchase = ItemPurchase::findOrFail($id);

            if ($request->hasFile('bon_photo')) {
                $bonPhoto = $request->file('bon_photo');
                $bonPhotoName = "bon-photo-{$itemPurchase->item_request_id}." . $bonPhoto->getClientOriginalExtension();
                $path = $bonPhoto->storeAs('public/item-purchase-bon-photos', $bonPhotoName);
                $validatedData['bon_photo'] = $path;
            }

            $itemPurchase->update($validatedData);

            return response()->json([
                'message' => 'Item purchase updated successfully',
                'data' => $itemPurchase
            ], 200);

        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function addVendor(Request $request, $id)
    {
        $request->validate([
            'owner_name' => 'required|string',
            'store_name' => 'required|string',
            'phone_number' => 'required|string',
            'location' => 'required|string',
            'supplier_category_id' => 'required|array',
        ]);

        $itemRequest = ItemRequest::findOrFail($id);

        try {
            DB::beginTransaction();

            $productSupplier = ProductSupplier::create([
                'owner_name' => $request->owner_name,
                'company_id' => $itemRequest->company_id,
                'store_name' => $request->store_name,
                'phone_number' => $request->phone_number,
                'location' => $request->location,
            ]);

            $productSupplier->supplierCategories()->sync($request->supplier_category_id);

            PotentialVendor::create([
                'company_id' => $itemRequest->company_id,
                'item_request_id' => $itemRequest->id,
                'product_supplier_id' => $productSupplier->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'supplier' => $productSupplier
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
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
            $itemPurchase = ItemPurchase::findOrFail($id);

            $path = $request->file('proof_image')->store('bukti_transfer');

            Payment::create([
                'company_id' => auth()->user()->company_id,
                'item_purchase_id' => $itemPurchase->id,
                'finance_id' => auth()->id(),
                'proof_image' => $path,
                'paid_at' => now(),
            ]);

            $itemPurchase->update(['status' => 'paid']);

            if ($itemPurchase->itemRequest->is_complete_payment) {
                ItemRequest::where('id', $itemPurchase->item_request_id)
                    ->update(['status' => 'WAITING_DELIVERY_CONFIRMATION']);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
