<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeleteProductBos1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:proudct-bos-1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \DB::beginTransaction();
        try {
            $companies = \App\Models\Company::where('name',"BOS 1")->first();
            $product = \App\Models\Product::byCompany($companies->id)->get();
            foreach ($product as $value) 
            {
                $this->info('Product '.$value->name.' terdelete');
                $value->delete();
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->error('Delete product bos 2 gagal');
            $this->error($e->getMessage());
        }
    }
}

