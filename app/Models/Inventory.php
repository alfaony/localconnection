<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'product_store_id',
        'quantity',
        'unit',
        'external_id',
        'last_sync_at',
        'company_id',
        'user_create_id',
        'user_modified_id',
    ];

    protected $casts = [
        'id'               => 'string',
        'product_store_id' => 'string',
        'company_id'       => 'string',
        'user_create_id'   => 'string',
        'user_modified_id' => 'string',
        'quantity'         => 'integer',
        'last_sync_at'     => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });

        static::updating(function ($model) {
            $model->user_modified_id = auth()->id();
        });
    }

    public function productStore(): BelongsTo
    {
        return $this->belongsTo(ProductStore::class, 'product_store_id')->withTrashed();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id')->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_create_id')->withTrashed();
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_modified_id')->withTrashed();
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'inventory_id')->latest();
    }

    /**
     * Tambah stok dan catat movement-nya.
     */
    public function addStock(int $qty, string $notes = null, string $source = 'manual', string $externalRef = null): InventoryMovement
    {
        $before = $this->quantity;
        $this->quantity += $qty;
        $this->save();

        return $this->movements()->create([
            'type'           => 'in',
            'quantity'       => $qty,
            'quantity_before' => $before,
            'quantity_after'  => $this->quantity,
            'source'         => $source,
            'external_ref'   => $externalRef,
            'notes'          => $notes,
            'company_id'     => $this->company_id,
            'user_create_id' => auth()->id(),
        ]);
    }

    /**
     * Kurangi stok dan catat movement-nya.
     */
    public function deductStock(int $qty, string $notes = null, string $source = 'manual', string $externalRef = null): InventoryMovement
    {
        $before = $this->quantity;
        $this->quantity -= $qty;
        $this->save();

        return $this->movements()->create([
            'type'           => 'out',
            'quantity'       => $qty,
            'quantity_before' => $before,
            'quantity_after'  => $this->quantity,
            'source'         => $source,
            'external_ref'   => $externalRef,
            'notes'          => $notes,
            'company_id'     => $this->company_id,
            'user_create_id' => auth()->id(),
        ]);
    }

    /**
     * Set stok ke angka tertentu (adjustment) dan catat movement-nya.
     */
    public function adjustStock(int $newQty, string $notes = null, string $source = 'manual', string $externalRef = null): InventoryMovement
    {
        $before = $this->quantity;
        $diff = $newQty - $before;
        $this->quantity = $newQty;
        $this->save();

        return $this->movements()->create([
            'type'           => 'adjustment',
            'quantity'       => abs($diff),
            'quantity_before' => $before,
            'quantity_after'  => $newQty,
            'source'         => $source,
            'external_ref'   => $externalRef,
            'notes'          => $notes,
            'company_id'     => $this->company_id,
            'user_create_id' => auth()->id(),
        ]);
    }
}
