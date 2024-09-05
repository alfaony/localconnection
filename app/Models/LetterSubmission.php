<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

use App\Schemas\RoleSchema;
use Ramsey\Uuid\Uuid;

class LetterSubmission extends Model
{
    use HasFactory, SoftDeletes;

    // Primary key type
    protected $keyType = 'uuid';

    // Automatically cast field column to array (JSON)
    protected $casts = [
        'field' => 'array',  // Field column will automatically be cast as array
        'is_approved' => 'boolean', // is_approved will automatically be cast to boolean
    ];

    // Fillable attributes for mass assignment
    protected $fillable = [
        'letter_type_id',
        'user_id',
        'is_approved',
        'field',
    ];

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    
    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) 
        {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    public function getConvertFieldAttribute()
    {
        return json_decode($this->field, true);
    }
    /**
     * Relationship with LetterType model.
     * A LetterSubmission belongs to a LetterType.
     */
    public function letterType()
    {
        return $this->belongsTo(LetterType::class);
    }

    /**
     * Relationship with User model.
     * A LetterSubmission belongs to a User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if submission is approved.
     * 
     * @return bool|null
     */
    public function isApproved()
    {
        return $this->is_approved;
    }

    /**
     * Set the approval status to approved (true).
     */
    public function approve()
    {
        $this->is_approved = true;
        $this->save();
    }

    /**
     * Set the approval status to rejected (false).
     */
    public function reject()
    {
        $this->is_approved = false;
        $this->save();
    }


    public function scopeByCompany($query,$companyId)
    {
        if($companyId)
        {
            return $query->whereHas('user', function ($query) use ($companyId) 
            {
                $query->where('company_id', $companyId);
            });
        }
    }

    public function scopeByRole($query)
    {
        if(Auth::user()->role->name == RoleSchema::ROOT || Auth::user()->role->name == RoleSchema::DIRECTOR || Auth::user()->role->name == RoleSchema::ADMIN)
        {
            return $query->byCompany(Auth::user()->company_id);
        }else
        {
            return $query->where('user_id', Auth::user()->id);
        }
    }
}
