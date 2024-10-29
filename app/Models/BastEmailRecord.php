<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BastEmailRecord extends Model
{
    use HasFactory;

    protected $table = 'bast_email_records';

    protected $fillable = [
        'bast_id', 'to', 'cc', 'subject', 'content'
    ];

    // Cast 'to' and 'cc' fields as arrays
    protected $casts = [
        'to' => 'array',
        'cc' => 'array',
    ];

    // Relationship with Bast
    public function bast()
    {
        return $this->belongsTo(Bast::class, 'bast_id');
    }
}
