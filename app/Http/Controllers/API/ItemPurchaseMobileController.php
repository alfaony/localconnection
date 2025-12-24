<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\ChatMessageSent;
use App\Jobs\SentMessageToVendor;

use App\Models\ItemPurchase;
use App\Models\ItemRequest;
use App\Models\ProductSupplier;
use App\Jobs\ItemRequestClose;
use App\Models\Payment;
use App\Models\PotentialVendor;
use App\Helpers\InboxHelper;
use App\Services\Weblas\Message;

class ItemPurchaseMobileController extends BaseController
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

            $itemPurchase = ItemPurchase::create($validatedData);

            $itemRequest->status = 'WAITING_PAYMENT';
            $itemRequest->save();

            if ($request->boolean('is_finished')) {
                $itemRequest->is_open = 0;
                $itemRequest->save();
                dispatch(new ItemRequestClose($itemRequest, Auth::user()->id));
            }

            DB::commit();
            return response()->json([
                'message' => 'Item purchase created successfully',
                'data' => $itemPurchase
            ], 200);

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
                $message = "Request Penerbitan Air Way Bill (Resi) pada request #{$itemPurchase->itemRequest->item_name} #id {$itemPurchase->itemRequest->id}";
                $directUrl = route('item-request.show', $itemPurchase->itemRequest->id);
                $this->sentInbox($itemPurchase->itemRequest->user_id,$message, $directUrl, $itemPurchase->itemRequest->id);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function getPayment($itemRequestId)
    {
        try {
            $itemPurchases = ItemPurchase::where('item_request_id', $itemRequestId)
                ->with([
                    'itemRequest:id,item_name,status,is_open',
                    'productSupplier:id,store_name,owner_name,phone_number,location',
                    'sprinter:id,name,email',
                    'payment' => function ($q) {
                        $q->select('id', 'item_purchase_id', 'finance_id', 'proof_image', 'paid_at')
                        ->with(['finance:id,name']);
                    },
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($itemPurchases->isEmpty()) {
                return $this->sendError('Tidak ada data pembayaran untuk item_request_id ini.', [
                    'error' => 'Payment data not found'
                ]);
            }

            $mapped = $itemPurchases->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_request_id' => $item->item_request_id,
                    'actual_price' => $item->actual_price,
                    'bon_photo' => $item->bon_photo ? asset(str_replace('public/', 'storage/', $item->bon_photo)) : null,
                    'status' => $item->status,
                    'payment_term_date' => $item->payment_term_date,
                    'payment_method' => $item->payment_method,
                    'rekening_number' => $item->rekening_number,
                    'note' => $item->note,
                    'product_supplier' => $item->productSupplier,
                    'sprinter' => $item->sprinter,
                    'payment' => $item->payment ? [
                        'payment_id' => $item->payment->id,
                        'proof_image' => $item->payment->proof_image
                            ? asset('storage/' . $item->payment->proof_image)
                            : null,
                        'paid_at' => $item->payment->paid_at,
                        'finance_name' => optional($item->payment->finance)->name,
                    ] : null,
                ];
            })->values();

            $data = $mapped->count() === 1 ? $mapped->first() : ['list' => $mapped];

            return $this->sendResponse($data, 'Data pembayaran berhasil didapatkan.');

        } catch (\Exception $e) {
            \Log::error($e);
            return $this->sendError('Gagal mengambil data pembayaran.', ['error' => $e->getMessage()]);
        }
    }


    public function closed(Request $request, $id)
    {
        try {
            $itemRequest = ItemRequest::findOrFail($id);

            if (!$itemRequest->is_open) {
                return response()->json(['message' => 'Permintaan sudah ditutup'], 400);
            }

            $itemRequest->is_open = false;
            $itemRequest->status = 'CLOSED';
            $itemRequest->close_reason = $request->close_reason;
            $itemRequest->save();

            // Send inbox
            $message = "Permintaan #{$itemRequest->item_name} #id {$itemRequest->id} telah ditutup. Silakan cek detailnya";
            $directUrl = route('item-request.show', $itemRequest->id);

            // method dari BaseController
            $this->sentInbox(
                $itemRequest->user_id,
                $message,
                $directUrl,
                $itemRequest->id
            );

            return response()->json(['message' => 'Permintaan ditutup']);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Terjadi kesalahan'], 500);
        }
    }

    public function complete($id)
    {
        try {
            $itemRequest = ItemRequest::findOrFail($id);

            // update status
            $itemRequest->is_open = 0;
            // $itemRequest->status = 'COMPLETED';
            $itemRequest->save();

            // dispatch job
            dispatch(new ItemRequestClose($itemRequest, Auth::user()->id));

            return response()->json(['message' => 'Permintaan diselesaikan']);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Terjadi kesalahan'], 500);
        }
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
        return;
    }

}