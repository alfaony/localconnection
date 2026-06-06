<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportProgress extends Model
{
    use HasFactory;

    protected $fillable = ['batch_id', 'processed', 'total', 'total_import', 'errors'];

    protected $casts = [
        'errors' => 'array',  // Pastikan ini ada
        'processed' => 'integer',
        'total_import' => 'integer',
        'total' => 'integer',
    ];

    // Accessor untuk memastikan errors selalu array
    public function getErrorsAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return is_array($value) ? $value : [];
    }

    public function getPercentageAttribute()
    {
        if ($this->total <= 0) {
            return 0;
        }
        return round(($this->processed / $this->total) * 100, 2);
    }

    public function getSuccessAttribute()
    {
        return $this->total_import;
    }

    public function getFailedAttribute()
    {
        return $this->processed - $this->total_import;
    }
}