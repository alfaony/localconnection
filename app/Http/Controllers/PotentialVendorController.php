<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PotentialVendor;
use Illuminate\Support\Facades\Validator;

use App\Events\ChatMessageSent;

use App\Helpers\InboxHelper;

use App\Models\Role;
use App\Models\User;
use App\Schemas\RoleSchema;

class PotentialVendorController extends Controller
{
    public function edit(Request $request, $id, $token)
    {
        $vendor = $request->vendor;
        $itemRequest = $vendor->itemRequest;
        $potentialVendor = PotentialVendor::where('id', $id)->first();

        return view('item_request.form_respond', compact('vendor', 'itemRequest', 'potentialVendor'));
    }

    public function update(Request $request, $id, $token)
    {
        $vendor = $request->vendor;
        $itemRequest = $vendor->itemRequest;

        // if (!$itemRequest->is_open || $vendor->responded) {
        //     return redirect()->back()->with('error', 'Permintaan ini sudah ditutup atau Anda sudah merespon.');
        // }

        $validator = Validator::make($request->all(), [
            'price_offered' => 'required|numeric|max:' . $itemRequest->estimated_price,
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vendor->update([
            'price_offered' => $request->price_offered,
            'note' => $request->note,
            'responded' => true,
            'responded_at' => now(),
        ]);

        $adminRole = Role::where('name', RoleSchema::ADMIN)->first();
        $rootRole = Role::where('name', RoleSchema::ROOT)->first();

        $userSent = User::where('company_id', $vendor->company_id)
        ->where(function ($query) use ($adminRole, $rootRole) {
            if ($adminRole) {
                $query->where('role_id', $adminRole->id);
            }
            if ($rootRole) 
            {
                $query->orWhere('role_id', $rootRole->id);
            }

            // $query->orWhere('id','a5d7507a-20a6-4c00-9eb2-e01051a122c6')
            ;
        })
        ->first();

        $message = "Vendor #{$vendor->productSupplier->store_name} Menerima Tawaran Untuk {$vendor->itemRequest->item_name}";
        $directUrl = route('item-request.show', $vendor->itemRequest->id);

        $this->sentInbox($userSent->id, $vendor->itemRequest->assigned_pic_id, $message, $directUrl, $vendor->itemRequest->id);
        $this->sentInbox($userSent->id, $vendor->itemRequest->user_id, $message, $directUrl, $vendor->itemRequest->id);

        return redirect()->back()->with('success', 'Respon berhasil dikirim.');
    }

    private function sentInbox($from, $to,$message,$directUrl, $itemRequest = null)
    {
        if($itemRequest)
        {
            broadcast(new ChatMessageSent(
                "",
                $message,
                now(),
                $itemRequest,
                $from,
            ))->toOthers();
        }

        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $to, 
            $from,
            $message, 
            $directUrl
        );
        return;
    }
}
