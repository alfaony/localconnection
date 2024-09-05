<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use App\Params\ParamSchema;

class LetterType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'template',
        'is_required',
        'is_duplicate',
        'auto_approve',
        'is_ending',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_duplicate' => 'boolean',
        'auto_approve' => 'boolean',
        'is_ending' => 'boolean',
    ];

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string
    
    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) 
        {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    public function letterSubmissions()
    {
        return $this->hasMany(LetterSubmission::class);
    }

    public function scopeFilterByUserStatus($query)
    {
        $user = auth()->user();

        // If user's status_position is NULL, show only required letter types
        if (is_null($user->status_position)) {
            $query->where('is_required', true);
            
            // Check if is_duplicate is false or true with appropriate logic
            return $query->where(function ($subQuery) use ($user) {
                // If is_duplicate is true, ensure no unapproved submissions
                $subQuery->where(function ($q) use ($user) {
                    $q->where('is_duplicate', true)
                    ->whereDoesntHave('letterSubmissions', function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                            ->whereNull('is_approved'); // Prevent showing if unapproved
                    });
                })
                // If is_duplicate is false, check if the user has never submitted this type before
                ->orWhere(function ($q) use ($user) {
                    $q->where('is_duplicate', false)
                    ->whereDoesntHave('letterSubmissions', function ($q) use ($user) {
                        $q->where('user_id', $user->id); // Prevent if already submitted
                    });
                });
            });
        }
        
        else {
            $perjanjianKerjaTypeId = LetterType::where('name', ParamSchema::PERJANJIANKERJA)->value('id');
            return $query->where('head_letter_types_id', $perjanjianKerjaTypeId);
        }
    }
}