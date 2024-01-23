<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuoteProduct;
use App\Models\WorkOrderProduct;
class GenerateProductPriceForWorkOrderAndQuote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:product-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate product prices for work orders and quotes';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Update price_sell for QuoteProduct
        $quoteProducts = QuoteProduct::all();
        foreach ($quoteProducts as $quoteProduct) 
        {
            $product = $quoteProduct->product; // Assuming there is a 'product' relationship in QuoteProduct model
            $quoteProduct->price_sell = $product->price_sell;
            $quoteProduct->save();
        }

        // Update price_buy for WorkOrderProduct
        $workOrderProducts = WorkOrderProduct::all();
        foreach ($workOrderProducts as $workOrderProduct) 
        {
            $product = $workOrderProduct->product; // Assuming there is a 'product' relationship in WorkOrderProduct model
            $workOrderProduct->price_buy = $product->price_buy;
            $workOrderProduct->save();
        }

        $this->info('Product prices updated successfully.');

        return Command::SUCCESS;
    }
}
