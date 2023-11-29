<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Spatie\Activitylog\Models\Activity;

class GenerateAllProductActivityLog extends Command
{
    protected $signature = 'generate:all-product-activity-log';
    protected $description = 'Generate activity log for all products';

    public function handle()
    {
        $this->info('Generating activity log for all products...');

        $products = Product::all();

        foreach ($products as $product) {
            $description = 'create';

            $attributes = 
            [
                'price_buy' => $product->price_buy,
                'price_sell' => $product->price_sell,
            ];

            activity()
            ->performedOn($product) // Gunakan performedOn untuk menentukan objek yang dilibatkan
            ->causedBy($product->user_updated_id)
            ->event('create')
            ->createdAt($product->updated_at)
            ->withProperties(
                [
                    'attributes' => $attributes,
                ]
            )
            ->log($description)
            ->save();
        }

        $this->info('Activity log generation completed.');
    }
}

