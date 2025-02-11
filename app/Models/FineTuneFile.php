<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FineTuneFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fine_tune_table_id',
        'fine_tune_file_id',
        'fine_tune_id',
        'company_id',
        'filename',
        'file_path',
    ];

    /**
     * Get the fine_tune that owns the FineTunedFile.
     */
    public function fineTune()
    {
        return $this->belongsTo(FineTune::class);
    }

    /**
     * Get the company associated with the FineTunedFile.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
