<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;

class OfficeMedia extends Model
{
    use SoftDeletes;

    protected $table = 'office_media';

    protected $fillable = [
        'type',
        'title',
        'file_path',
        'youtube_url',
        'is_temporary',
        'user_id',
        'company_id',
    ];

    protected $casts = [
        'is_temporary' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function scopeByCompany($query,$companyId)
    {
        if($companyId && Auth::user()->role->name != RoleSchema::ROOT)
        {
            return $query->where("company_id",$companyId);
        }
    }
}
