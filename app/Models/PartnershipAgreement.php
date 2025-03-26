<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

use App\Schemas\RoleSchema;
use App\Schemas\ParamSchema;

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

    public function signature()
    {
        return $this->hasMany(AgreementSignature::class);
    }

    public function getNextSignatureNumber()
    {
        $maxSignatureNumber = $this->signature()->max('order');
        return $maxSignatureNumber ? $maxSignatureNumber + 1 : 1;
    }


    public function getTransalateSignature()
    {
        switch ($this->getNextSignatureNumber()) 
        {
            case 1:
                return "Pihak Pertama";
                break;

            case 2:
                return "Pihak Kedua";
                break;

            case 3:
                return "Pihak Ketiga";
                break;
            
            default:
                return "Pihak Bewenang";
                break;
        }
    }
    public function getApprove()
    {
        $maxSignatureNumber = $this->signature()->max('order') ?? 0;
        $signature = $this->type->count_signature ?? 0 ;
        if($maxSignatureNumber == $signature)
        {
            return true;
        }else
        {
            return false;
        }
    }



    public function isPermission($param)
    {
        $edit = false;
        $delete = false;
        $submit = false;
        $signature = false;
        $approvement = false;
        $download = false;
        $messageReject = false;

        if($this->status == ParamSchema::DRAFT)
        {
            $edit = true;
            $delete = true;
            $submit = true;
            $download = true;
        }

        if($this->status == ParamSchema::SUBMIT)
        {
            $signature = true;
        }

        if($this->status == ParamSchema::SIGNATURE)
        {
            $signature = true;
        }

        if($this->status == ParamSchema::ONREVIEW)
        {
            $approvement = true;
            $download = true;
        }
        
        if($this->status == ParamSchema::APPROVED)
        {
            $edit = true;
            $download = true;
        }

        if($this->status == ParamSchema::REJECTED)
        {
            $edit = true;
            $download = true;
            $messageReject = true;
        }

        return array_key_exists($param, [
            'edit' => $edit,
            'delete' => $delete,
            'submit' => $submit,
            'signature' => $signature,
            'approvement' => $approvement,
            'download' => $download,
            'messageReject' => $messageReject
        ]) ? ${$param} : false;
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
    public function getStatusBadgeAttribute()
    {
        // Return the appropriate badge class based on the status
        switch ($this->status) {
            case ParamSchema::DRAFT:
                return 'badge-secondary';  // for 'draf' status
            case ParamSchema::SUBMIT:
                return 'badge-warning';    // for 'submit' status
            case ParamSchema::SIGNATURE:
                return 'badge-primary';    // for 'signature' status
            case ParamSchema::ONREVIEW:
                return 'badge-info';       // for 'onreview' status
            case ParamSchema::APPROVED:
                return 'badge-success';    // for 'done' status
            case ParamSchema::REJECTED:
                return 'badge-danger';     // for 'rejected' status
            default:
                return 'badge-secondary';  // fallback to secondary for unknown status
        }
    }

    public function getSignature($order)
    {
        $approvement = $this->signature()->where('order', $order)->first();
        return $approvement ? $approvement : null;
    }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId && Auth::user()->role->name != RoleSchema::ROOT)
        {
            return $query->where("company_id",$companyId);
        }
    }
}