<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FineTune extends Model
{
    use HasFactory;

    protected $fillable = [
        'fine_tune_id',
        'fine_tune_table_id',
        'company_id',
        'fine_tune_model',
        'status',
        'active',
    ];

    /**
     * Get the fine_tune that owns the FileTune.
     */
    public function fineTune()
    {
        return $this->belongsTo(FineTune::class);
    }

    /**
     * Get the fine_tune that owns the FileTune.
     */

     public function fineTuneFile()
     {
         return $this->hasMany(FineTuneFile::class);
     }

    /**
     * Get the company associated with the FileTune.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}