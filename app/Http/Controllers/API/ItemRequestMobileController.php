<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\ItemRequest;
use App\Models\SupplierCategory;
use App\Models\PotentialVendor;
use App\Services\WorkflowService;
use App\Models\Delivery;
use Illuminate\Support\Facades\Log;

class ItemRequestMobileController extends BaseController
{
    /**
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $items = ItemRequest::where('company_id', auth()->user()->company_id)
                ->with([
                    'requester:id,name',
                    'assignedPic:id,name'
                ])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {

                    $supplierTypeName = null;
                    if ($item->supplier_type_id) {
                        $supplierTypeName = \DB::table('supplier_types')
                            ->where('id', $item->supplier_type_id)
                            ->value('name');
                    }

                    $supplierCategoryName = null;
                    if ($item->supplier_category_id) {
                        $supplierCategoryName = \DB::table('supplier_categories')
                            ->where('id', $item->supplier_category_id)
                            ->value('name');
                    }

                    return [
                        'id' => $item->id,
                        'company_id' => $item->company_id,
                        'date' => $item->created_at?->format('Y-m-d'),

                        'item_name' => $item->item_name,
                        'picture' => $item->picture,
                        'description' => $item->description,

                        'estimated_price' => $item->estimated_price,
                        'qty' => $item->qty,

                        'status' => $item->status,
                        'is_open' => $item->is_open,
                        'close_reason' => $item->close_reason,

                        'price_with_format' => $item->price_with_format,
                        'status_badge' => $item->status_badge,

                        'user_name' => optional($item->requester)->name,
                        'assigned_pic_name' => optional($item->assignedPic)->name,
                        'supplier_type_name' => $supplierTypeName,
                        'supplier_category_name' => $supplierCategoryName,
                    ];
                });

            return $this->sendResponse($items->toArray(), 'Daftar semua item request berhasil diambil.');
            
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil data item request.', ['error' => $e->getMessage()]);
        }
    }


    public function show($id)
    {
        try {
            $item = ItemRequest::where('company_id', auth()->user()->company_id)
                ->with([
                    'requester:id,name',
                    'assignedPic:id,name'
                ])
                ->where('id', $id)
                ->firstOrFail();

            $supplierTypeName = null;
            if ($item->supplier_type_id) {
                $supplierTypeName = \DB::table('supplier_types')
                    ->where('id', $item->supplier_type_id)
                    ->value('name');
            }

            $supplierCategoryName = null;
            if ($item->supplier_category_id) {
                $supplierCategoryName = \DB::table('supplier_categories')
                    ->where('id', $item->supplier_category_id)
                    ->value('name');
            }

            $data = $item->toArray();

            $data['user_name'] = optional($item->requester)->name;
            $data['assigned_pic_name'] = optional($item->assignedPic)->name;
            $data['supplier_type_name'] = $supplierTypeName;
            $data['supplier_category_name'] = $supplierCategoryName;

            return $this->sendResponse($data, 'Detail item request berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil detail item request.', ['error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_category_id' => 'required|exists:supplier_categories,id',
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_price' => 'required|numeric|min:1',
            'qty' => 'required|numeric|min:1',
            'type' => 'nullable|exists:supplier_types,id',
        ]);

        if ($request->hasFile('picture')) {
            $validated['picture'] = $request->file('picture')->store('item_pictures');
        }

        $validated['user_id'] = auth()->id();
        $validated['company_id'] = auth()->user()->company_id;
        $validated['status'] = 'REQUESTED';
        $validated['supplier_type_id'] = $request->type;

        $item = ItemRequest::create($validated);

        return response()->json([
            'message' => 'Item request created',
            'data' => $item
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = ItemRequest::where('company_id', auth()->user()->company_id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'supplier_category_id' => 'required|exists:supplier_categories,id',
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_price' => 'required|numeric|min:1',
            'qty' => 'required|numeric|min:1',
            'type' => 'nullable|exists:supplier_types,id',
        ]);

        // Handle vendor input
        if (!empty($request->product_supplier_id)) {
            $inputVendorIds = $request->product_supplier_id ?? [];

            $existingVendorIds = $item->potentialVendors->pluck('product_supplier_id')->toArray();

            foreach ($inputVendorIds as $vendorId) {
                PotentialVendor::firstOrCreate([
                    'company_id' => $item->company_id,
                    'item_request_id' => $item->id,
                    'product_supplier_id' => $vendorId,
                ], [
                    'responded' => false
                ]);
            }

            $vendorsToDelete = array_diff($existingVendorIds, $inputVendorIds);

            if (!empty($vendorsToDelete)) {
                PotentialVendor::where('item_request_id', $item->id)
                    ->whereIn('product_supplier_id', $vendorsToDelete)
                    ->delete();
            }
        }

        if ($request->hasFile('picture')) {
            if ($item->picture) {
                Storage::delete($item->picture);
            }

            $validated['picture'] = $request->file('picture')->store('item_pictures');
        }

        $validated['supplier_type_id'] = $request->type;

        $item->update($validated);

        return response()->json([
            'message' => 'Item request updated',
            'data' => $item
        ]);
    }

    public function destroy($id)
    {
        $item = ItemRequest::where('company_id', auth()->user()->company_id)
            ->where('id', $id)
            ->firstOrFail();

        if (!$item->is_open) {
            return response()->json(['message' => 'Request already closed'], 422);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item request deleted'
        ]);
    }

    public function workflow($id)
    {
        try {
            $itemRequest = ItemRequest::find($id);

            if (!$itemRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item Request tidak ditemukan.'
                ], 404);
            }

            // Generate step workflow
            $steps = WorkflowService::generateSteps($itemRequest);

            return response()->json([
                'success' => true,
                'steps' => $steps,  
                'status_badge' => $itemRequest->status_badge,
                'status_open' => $itemRequest->status_open,
                'status_closed' => $itemRequest->status == 'CLOSED',
                'reason_text' => $itemRequest->close_reason,
                'item_request' => [
                    'id' => $itemRequest->id,
                    'title' => $itemRequest->title,
                    'status' => $itemRequest->status,
                ],
                'message' => 'Data workflow berhasil didapatkan'
            ]);

        } catch (\Exception $e) {
            Log::error('Workflow API error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat workflow.'
            ], 500);
        }
    }

    public function delivery(Request $request, $id)
    {
        $request->validate([
            'shipping_method' => 'nullable|string',
            'resi_number' => 'nullable|string',
            'airwillbill_photo' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'delivery_photo' => 'nullable|file|mimes:jpg,jpeg,png',
        ]);

        $itemRequest = ItemRequest::findOrFail($id);

        DB::beginTransaction();
        try {
            $awbPath = $request->hasFile('airwillbill_photo')
                ? $request->file('airwillbill_photo')->store('airwillbill')
                : null;

            $deliveryPhotoPath = $request->hasFile('delivery_photo')
                ? $request->file('delivery_photo')->store('delivery_photo')
                : null;

            if (!$itemRequest->delivery) {
                $delivery = Delivery::create([
                    'item_request_id' => $itemRequest->id,
                    'company_id' => auth()->user()->company_id,
                    'sprinter_id' => auth()->id(),
                    'shipping_method' => $request->shipping_method,
                    'resi_number' => $request->resi_number,
                    'airwillbill_photo' => $awbPath,
                    'delivery_photo' => $deliveryPhotoPath,
                ]);

                ItemRequest::where('id', $id)->update([
                    'status' => 'WAITING_CUSTOMER_CONFIRMATION'
                ]);
            } else {
                // $itemRequest->delivery->update([
                //     'delivery_photo' => $deliveryPhotoPath,
                //     'delivered_at' => now(),
                // ]);

                // ItemRequest::where('id', $id)->update(['status' => 'DELIVERED']);
                // $delivery = $itemRequest->delivery;
                
                $updateData = [
                    'delivered_at' => now(),
                ];

                if ($deliveryPhotoPath) {
                    $updateData['delivery_photo'] = $deliveryPhotoPath;
                }

                $itemRequest->delivery->update($updateData);

                ItemRequest::where('id', $id)->update(['status' => 'DELIVERED']);

                $delivery = $itemRequest->delivery;
            }

            DB::commit();
            return response()->json(['success' => true, 'delivery' => $delivery]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return response()->json([
                'success' => false,
                'message' => 'Error'
            ]);
        }
    }

}
