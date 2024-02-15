<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use App\Schemas\NoticeSchema;
use App\Schemas\ParamSchema;

use App\Models\Project;
use App\Models\User;
use App\Models\Quote;
use App\Models\Product;
use App\Models\QuoteProduct;

class ProjectReccuring extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:reccuring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Project Reccuing';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $projects = Project::where('recurring',NoticeSchema::ACTIVED)->whereDate('end_date', Carbon::today())->get();
        foreach ($projects as $project) 
        {
            $quote = $project->workOrder->quote;
            $user = User::find($quote->user_created_id);

            $this->store($quote,$user->company_id);
        }

        $this->info("Berhasil Eksekusi");
    }

    protected function store($quoteRequest,$companyId)
    {
        try 
        {
            DB::beginTransaction();
            $date = Carbon::now()->format('m/Y');
            $quoteNumber = Quote::byCompany($companyId)->withTrashed()->max('quote_number') + 1;
            $numberResult = $quoteNumber.'/'.$date;

            $quote = new Quote();
            $quote->quote_number = $quoteNumber;
            $quote->number_result = $numberResult;
            $quote->customer_id = $quoteRequest->customer_id;
            $quote->date = Carbon::now();
            $quote->tax = $quoteRequest->tax;
            $quote->service_fee = $quoteRequest->service_fee;
            $quote->discount = $quoteRequest->discount;
            $quote->charges = $quoteRequest->charges;
            
            $quote->user_created_id = $quoteRequest->user_created_id;
            $quote->user_updated_id = $quoteRequest->user_created_id;
            $quote->save();

            $quoteProductRequest = $quoteRequest->quoteProduct;
            foreach ($quoteProductRequest as $productQuote) 
            {
                $product = Product::find($productQuote->product_id);

                $quoteProduct = new QuoteProduct;
                $quoteProduct->sort = $productQuote->sort;
                $quoteProduct->product_id = $product->id;
                $quoteProduct->qty = $productQuote->qty;
                $quoteProduct->price_sell = $product->price_sell;
                $quoteProduct->sub_total = $product->price_sell * $productQuote->qty;
                $quoteProduct->description = $productQuote->description;
                
                
                $quote->quoteProduct()->save($quoteProduct);
            }



            $this->grandTotal($quote);
            DB::commit();
            // return redirect()->to(route('quote.index'))->with('store',true);
            // return redirect()->to(route('quote.download.pdf', ['slug' => $quote->slug]))->with('store',true);
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            Log::error($th);
            $this->info($th);
            
            // return redirect()->to(route('quote.index'))->with('false',true);
            return false;
        }
    }

    private function grandTotal($quote)
    {
        $service_fee = $quote->service_fee ?? 0;
        $tax = $quote->tax ?? 0;

        $total =  $quote->quoteProduct() ? $quote->quoteProduct()->sum('sub_total') : 0;
        $charges = $quote->charges ?? 0;
        $discount = $quote->discount ?? 0;

        // return $tax;
        $totalAll = ($total + $charges) - $discount;
        $serviceFee = $service_fee != 0 ? round(($totalAll * $service_fee) / ParamSchema::PERCENTAGE) : 0 ;
        
        $totalAfterServiceFee = $totalAll + $serviceFee;
        $ppn = $tax != 0 ? round(($totalAfterServiceFee * $tax) / ParamSchema::PERCENTAGE) : 0 ;
        
        $grandTotal = $totalAfterServiceFee + $ppn;

        $quote->total = $grandTotal;
        $quote->save();
    }
}
