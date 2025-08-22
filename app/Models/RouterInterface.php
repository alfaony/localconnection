<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
class RouterInterface extends Model
{
    use SoftDeletes;

    protected $table = 'router_interfaces'; // PK = UUID
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

    protected $fillable = ['router_id','name','role','vlan_id','address_pool_id','meta'];
    protected $casts = ['meta' => 'array'];

    public function router() { return $this->belongsTo(Router::class); }
    public function addressPool() { return $this->belongsTo(AddressPool::class, 'address_pool_id'); }
    public function pppoeServers() { return $this->hasMany(PppoeServer::class, 'interface_id'); }
}