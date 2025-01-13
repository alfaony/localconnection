<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WarehouseType;
use Ramsey\Uuid\Uuid;

class WarehouseTypeSeeder extends Seeder
{
    public function run()
    {
        $warehouseTypes = [
            ['id' => Uuid::uuid4()->toString(), 'name' => 'Gudang'],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'Gudang Toko'],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'Gudang Supplier'],
        ];

        WarehouseType::insert($warehouseTypes);
    }
}