<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UsedLaptop;

class AddBrandUsedLaptop extends Command
{
    protected $signature = 'used-laptop:add-brand';
    protected $description = 'Mengisi kolom brand berdasarkan name di UsedLaptop';

    public function handle()
    {
        $laptops = UsedLaptop::all();
        $updatedCount = 0;

        foreach ($laptops as $laptop) {
            $parts = explode(' ', trim($laptop->name)); // string → array
            
            if(!$laptop->brand)
            {
                $brand = null;
    
                if (!empty($parts)) {
                    if (strcasecmp($parts[0], 'Laptop') === 0) {
                        $brand = $parts[1] ?? null;
                    } else {
                        $brand = $parts[0] ?? null;
                    }
                }
                $laptop->brand = $brand;
                $laptop->save();
            }

            $updatedCount++;
        }

        $this->info("Berhasil update brand untuk {$updatedCount} laptop.");
        return Command::SUCCESS;
    }
}