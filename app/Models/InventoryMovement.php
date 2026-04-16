<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Uuid;

class InventoryMovement extends Model
{
    use HasFactory, Uuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'inventory_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'source',
        'external_ref',
        'notes',
        'company_id',
        'user_create_id',
    ];

    protected $casts = [
        'id'              => 'string',
        'inventory_id'    => 'string',
        'company_id'      => 'string',
        'user_create_id'  => 'string',
        'quantity'        => 'integer',
        'quantity_before' => 'integer',
        'quantity_after'  => 'integer',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id')->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_create_id')->withTrashed();
    }
}
