<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class MasterAccount extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'software_id',
        'nama_akun',
        'max_slots',
        'used_slots',
        'email_akun',
        'password_akun',
        'pin_code',
        'link_invite',
        'instruksi_akses',
        'attachment',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'max_slots' => 'integer',
        'used_slots' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the company that owns the master account.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the software that owns the master account.
     */
    public function software()
    {
        return $this->belongsTo(Software::class)->withTrashed();
    }

    /**
     * Get the subscriptions for the master account.
     */
    public function subscriptions()
    {
        return $this->hasMany(CustomerSubscription::class, 'master_account_id');
    }

    /**
     * Get active subscriptions.
     */
    public function activeSubscriptions()
    {
        return $this->hasMany(CustomerSubscription::class, 'master_account_id')
            ->where('status', 'active')
            ->where('payment_status', 'paid');
    }

    /**
     * Encrypt password before saving.
     */
    public function setPasswordAkunAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password_akun'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt password when retrieving.
     */
    public function getPasswordAkunAttribute($value)
    {
        if (!empty($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value; // Return as is if decryption fails
            }
        }
        return null;
    }

    /**
     * Get available slots.
     */
    public function getAvailableSlotsAttribute()
    {
        return $this->max_slots - $this->used_slots;
    }

    /**
     * Check if slots are available.
     */
    public function hasSlotsAvailable()
    {
        return $this->used_slots < $this->max_slots && $this->status === 'active';
    }

    /**
     * Reserve a slot.
     */
    public function reserveSlot()
    {
        if ($this->hasSlotsAvailable()) {
            $this->increment('used_slots');
            return true;
        }
        return false;
    }

    /**
     * Release a slot.
     */
    public function releaseSlot()
    {
        if ($this->used_slots > 0) {
            $this->decrement('used_slots');
            return true;
        }
        return false;
    }

    public function getAttachmentAttribute($value){
        if($value){
            return "public/".$value;
        }
    }

    /**
     * Scope a query to only include active master accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include accounts with available slots.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
            ->whereRaw('used_slots < max_slots');
    }

    /**
     * Scope a query to filter by company.
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Get slot usage percentage.
     */
    public function getSlotUsagePercentageAttribute()
    {
        if ($this->max_slots == 0) {
            return 0;
        }
        return round(($this->used_slots / $this->max_slots) * 100, 2);
    }
}