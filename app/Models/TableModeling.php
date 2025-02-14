<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TableModeling extends Model
{
    use HasFactory, SoftDeletes;

    // Define the table name if it's not the plural form of the model name
    protected $table = 'table_modelings';

    // Define the fillable fields
    protected $fillable = [
        'fine_tune_table_id',
        'company_id',
        'data_model',
    ];

    // Define the relationships
    /**
     * Get the fine tune table associated with the modeling.
     */
    public function fineTuneTable()
    {
        return $this->belongsTo(FineTuneTable::class);
    }

    /**
     * Get the company associated with the modeling.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Cast the 'data_model' field to an array or object, as it's stored as JSON in the database
    protected $casts = [
        'data_model' => 'array',
    ];
}