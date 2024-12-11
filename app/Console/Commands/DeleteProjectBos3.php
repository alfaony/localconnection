<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeleteProjectBos3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:proudct-bos-3';

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
            $companies = \App\Models\Company::where('name',"BOS 3")->first();
            $product = \App\Models\Product::byCompany($companies->id)->get();
            foreach ($product as $value) 
            {
                $this->info('Product '.$value->name.' terdelete');
                $value->delete();
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->error('Delete product bos 3 gagal');
            $this->error($e->getMessage());
        }
    }
}
