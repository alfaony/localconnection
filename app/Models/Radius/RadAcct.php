<?php

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Model;

class RadAcct extends Model
{
    protected $connection = 'radius';
    protected $table = 'radacct';
    protected $primaryKey = 'radacctid';
    public $timestamps = false;

    protected $fillable = [
        'acctsessionid', 'acctuniqueid', 'username', 'groupname',
        'realm', 'nasipaddress', 'nasportid', 'nasporttype',
        'acctstarttime', 'acctupdatetime', 'acctstoptime',
        'acctinterval', 'acctsessiontime', 'acctauthentic',
        'connectinfo_start', 'connectinfo_stop',
        'acctinputoctets', 'acctoutputoctets',
        'calledstationid', 'callingstationid',
        'acctterminatecause', 'servicetype',
        'framedprotocol', 'framedipaddress',
    ];

    protected $casts = [
        'acctstarttime'  => 'datetime',
        'acctupdatetime' => 'datetime',
        'acctstoptime'   => 'datetime',
    ];

    /**
     * Scope: session aktif (belum stop)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('acctstoptime');
    }

    /**
     * Scope: session berdasarkan username
     */
    public function scopeForUser($query, string $username)
    {
        return $query->where('username', $username);
    }
}
