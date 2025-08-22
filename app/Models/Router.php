<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\MikrotikService;

class Router extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
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

    public function getActiveAttribute($value)
    {
        try {
            //code...
            $status = new MikrotikService($this->id);
            return $status->ping()->original ? "UP" : "DOWN";
        } catch (\Throwable $th) {
            return "WRONG". $th->getMessage();
        }
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