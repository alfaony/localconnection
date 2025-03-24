<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

use App\Schemas\RoleSchema;

class PartnershipAgreement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'date_agreement',
        'company_id',
        'user_created_id',
        'user_updated_id',
        'partnership_agreement_type_id',
        'letter_number',
        'number_result',
        'slug',
        'status',
        'is_approve',
        'reason',
        'fields',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_approve' => 'boolean',
    ];

    public function updateCreate()
    {
        return $this->belongsTo(User::class, 'user_created_id')->withTrashed();
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'user_updated_id')->withTrashed();
    }

    public function type()
    {
        return $this->belongsTo(PartnershipAgreementType::class,'partnership_agreement_type_id')->withTrashed();
    }

    public function getFields($key = null)
    {
        // Cek apakah key ada di dalam fields
        $key = trim($key);
        
        if (is_null($key) || $key == "") 
        {
            return json_decode($this->fields, true);
        }
        
        $fields = json_decode($this->fields, true);
        if (isset($fields[$key])) 
        {
            return $fields[$key] ?? null;  // Jika ada, return value atau empty string
        }
        
        // Jika tidak ada, kembalikan empty string
        return null;
    }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId && Auth::user()->role->name != RoleSchema::ROOT)
        {
            return $query->where("company_id",$companyId);
        }
    }
}