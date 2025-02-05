<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;

class GenerateIndonesiaCountry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:indonesia-country';

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
        $countryName = 'Indonesia';
        $country = Country::firstOrCreate(['name' => $countryName]);
        $this->info("successfully!");
    }
}
