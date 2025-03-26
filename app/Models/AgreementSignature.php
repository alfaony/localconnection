<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgreementSignature extends Model
{
    use HasFactory;

    public function getTransalateSignature()
    {
        switch ($this->order) 
        {
            case 1:
                return "Pihak Pertama";
                break;

            case 2:
                return "Pihak Kedua";
                break;

            case 3:
                return "Pihak Ketiga";
                break;
            
            default:
                return "Pihak Bewenang";
                break;
        }
    }
}
