<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\DayoffExport;
use App\Exports\DayoffRecapExport;
class DayoffMultiSheetExport implements WithMultipleSheets
{
    protected $dayoffData;
    protected $recapData;

    public function __construct($dayoffData, $recapData)
    {
        $this->dayoffData = $dayoffData;
        $this->recapData = $recapData;
    }

    public function sheets(): array
    {
        return [
            new DayoffExport($this->dayoffData),
            new DayoffRecapExport($this->recapData),
        ];
    }
}
