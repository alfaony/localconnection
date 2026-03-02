<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternetInstallationPhoto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'internet_installation_id',
        'photo',
        'caption',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to Installation
     */
    public function installation()
    {
        return $this->belongsTo(InternetCustomerInstallation::class, 'internet_installation_id');
    }

    /**
     * Get full S3 URL for photo
     */
    public function getPhotoUrlAttribute()
    {
        if (!$this->photo) {
            return null;
        }
        
        return \Storage::disk('s3')->url($this->photo);
    }

    /**
     * Get temporary signed URL for private S3 files
     */
    public function getSignedPhotoUrl($expiration = 60)
    {
        if (!$this->photo) {
            return null;
        }
        
        return \Storage::disk('s3')->temporaryUrl(
            $this->photo,
            now()->addMinutes($expiration)
        );
    }
}