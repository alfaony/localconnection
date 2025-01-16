<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Ramsey\Uuid\Uuid;

class WarehouseType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['id', 'name'];

    public $incrementing = false; // UUID sebagai primary key
    protected $keyType = 'string';

    // Override UUID dengan Ramsey UUID
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }

    // Relasi One-to-Many ke Warehouses
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class, 'warehouse_type_id');
    }
}