<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Inbox extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id_to',
        'user_id_from',
        'message',
        'direct_url',
        'is_read',
        'is_notif',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_notif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
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

    public function userTo()
    {
        return $this->belongsTo(User::class, 'user_id_to')->withTrashed();
    }

    public function userFrom()
    {
        return $this->belongsTo(User::class, 'user_id_from')->withTrashed();
    }
    // ============== QUERY SCOPES ==============
    
    /**
     * Scope untuk pesan yang diterima user tertentu
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id_to', $userId);
    }

    /**
     * Scope untuk pesan yang belum dibaca
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope untuk notifikasi aktif
     */
    public function scopeNotifications($query)
    {
        return $query->where('is_notif', true);
    }

    /**
     * Scope untuk pesan terbaru
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope dengan eager loading default
     */
    public function scopeWithSender($query)
    {
        return $query->with(['userFrom:id,name,email,avatar']);
    }

    // ============== HELPER METHODS ==============
    
    /**
     * Mark pesan sebagai sudah dibaca
     */
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true, 'is_notif' => false]);
    }

    /**
     * Cek apakah pesan milik user tertentu
     */
    public function belongsToUser($userId): bool
    {
        return $this->user_id_to === $userId;
    }

    /**
     * Get excerpt dari message
     */
    public function getExcerptAttribute($length = 100): string
    {
        return \Str::limit(strip_tags($this->message), $length);
    }

    /**
     * Format waktu relatif
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}

