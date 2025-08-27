<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\MikrotikService;
use App\Services\RouterOSService;

use Illuminate\Support\Facades\Cache;

class Router extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pop_id',
        'company_id',
        'user_id',
        'name',
        'host',
        'port',
        'username',
        'password',
        'ssl',
        'active',
    ];

    protected $casts = [
        'ssl' => 'boolean',
        'active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function pop()
    {
        return $this->belongsTo(Pop::class, 'pop_id')->withTrashed();
    }

    public function defaultPool()
    {
        return $this->belongsTo(AddressPool::class, 'default_pool_id');
    }
    public function pppoeServers()
    {
        return $this->hasMany(PppoeServer::class, 'router_id');
    }

    public function getActiveAttribute($value)
    {
        return Cache::remember("router_{$this->id}_active", now()->addMinutes(5), function () {
            try {
                //code...
                $status = new MikrotikService($this->id);
                return $status->ping()->original ? "UP" : "DOWN";
            } catch (\Throwable $th) {
                return "WRONG \n". $th->getMessage();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function scopeByCompany($query, $company_id)
    {
        return $query->where('company_id', $company_id);
    }
}