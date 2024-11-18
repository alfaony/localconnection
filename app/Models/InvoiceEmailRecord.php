<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceEmailRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id','user_id','to', 'cc', 'subject', 'content',
    ];

    // Cast 'to' and 'cc' fields as arrays
    protected $casts = [
        'to' => 'array',
        'cc' => 'array',
    ];

    // Relationship with Bast
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

