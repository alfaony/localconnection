<?php

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Model;

class RadUserGroup extends Model
{
    protected $connection = 'radius';
    protected $table = 'radusergroup';
    protected $primaryKey = 'username';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['username', 'groupname', 'priority'];
}
