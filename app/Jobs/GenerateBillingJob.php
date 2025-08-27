<?php

namespace App\Jobs;

use App\Models\UserCustomer;
use App\Models\InternetCustomerPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Schemas\ParamSchema;

class GenerateBillingJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $customer;

    public function __construct(UserCustomer $customer)
    {
        $this->customer = $customer;
    }

    public function handle()
    {
        $internetCustomer = $this->customer->internetCustomer;


        DB::beginTransaction();
        try {

            $this->customer->internetCustomer->update([
                'is_paid' => false,
                'status' => ParamSchema::WAITING_PAYMENT_SUBSCRIPTION
            ]);

            $check = InternetCustomerPurchase::where('internet_customer_id', $internetCustomer->id)
                ->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->first();
            if (!$check) 
            {
                $check = InternetCustomerPurchase::create([
                    'amount_paid' => $internetCustomer->internetPackage->price_nett ?? 0,
                    'internet_customer_id' => $internetCustomer->id,
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            // dd($th);
            DB::rollBack();
            \Log::error("Gagal buat tagihan otomatis: " . $th->getMessage());
        }
    }
}
