<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Models\ItemPurchase;
use App\Models\Payment;
use App\Models\ItemRequest;
use App\Models\User;
use App\Models\ProductSupplier;
use App\Models\PotentialVendor;

use App\Helpers\InboxHelper;

use App\Models\Role;
use App\Schemas\RoleSchema;

use App\Events\ChatMessageSent;

class ItemRequestClose implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    
    private $itemRequest;
    private $user_id;
    public function __construct($itemRequest, $user_id)
    {
        $this->itemRequest = $itemRequest;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->itemRequestClose($this->itemRequest);
    }
    private function itemRequestClose($itemRequest)
    {
        try {
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
                    $this->user_id
                ))->toOthers();
    
            $itemRequest->is_open = 0;
            return $itemRequest->save();
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
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
                $this->user_id
            ))->toOthers();
        }

        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $to, 
            $this->user_id, 
            $message, 
            $directUrl
        );
        return true;
    }
}
