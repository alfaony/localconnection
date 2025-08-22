<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class AddressPool extends Model
{
    use SoftDeletes;

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string

    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    protected $fillable = ['pop_id','name','cidr','gateway','meta'];
    protected $casts = ['meta' => 'array'];

    public function pop() { return $this->belongsTo(Pop::class, 'pop_id'); }
    public function routerInterfaces() { return $this->hasMany(RouterInterface::class, 'address_pool_id'); }
    public function pppoeServers() { return $this->hasMany(PppoeServer::class, 'address_pool_id'); }
}