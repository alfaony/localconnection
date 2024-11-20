<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use App\Schemas\RoleSchema;
class PassChecking extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'uuid'; // Karena menggunakan UUID sebagai primary key
    public $incrementing = false; // Non-incrementing ID, karena UUID
    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) 
        {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'user_id',
        'name',
        'date',
        'start_time',
        'end_time',
        'pictures',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'pictures' => 'array', // Cast pictures as an array
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];


    /**
     * Get the user associated with the pass checking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByCompany($query,$companyId)
    {
        if(Auth::user()->role != RoleSchema::ROOT)
        {
            if(Auth::user()->role == RoleSchema::ADMIN || Auth::user()->role == RoleSchema::DIRECTOR || Auth::user()->role == RoleSchema::MANAGER)
            {
                if($companyId)
                {
                    return $query->whereHas('user', function ($query) use ($companyId) 
                    {
                        $query->where('company_id', $companyId);
                    });
                }
            }else
            {
                return $query->where('user_id', Auth::user()->id);
            }
        }
    }

    public function employeeChecking()
    {
        return $this->hasMany(EmployeeChecking::class);
    }
}
