<?php
namespace App\Observers;

use App\Models\EquipmentReduction;
use App\Models\Equipment;

class EquipmentReductionObserver
{
    public function created(EquipmentReduction $equipmentReduction)
    {
        $equipment = Equipment::find($equipmentReduction->equipment_id);
        $equipment->total_stock -= $equipmentReduction->stock;
        $equipment->save();
    }

    public function updated(EquipmentReduction $equipmentReduction)
    {
        $originalStock = $equipmentReduction->getOriginal('stock');
        $newStock = $equipmentReduction->stock;
        $difference = $originalStock - $newStock;

        $equipment = Equipment::find($equipmentReduction->equipment_id);
        $equipment->total_stock += $difference;
        $equipment->save();
    }

    public function deleted(EquipmentReduction $equipmentReduction)
    {
        $equipment = Equipment::find($equipmentReduction->equipment_id);
        $equipment->total_stock += $equipmentReduction->stock;
        $equipment->save();
    }
}
