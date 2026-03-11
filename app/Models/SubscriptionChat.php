<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionChat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subscription_id',
        'user_id',
        'message',
        'attachment',
    ];

    protected $appends = ['attachment_url'];

    public function subscription()
    {
        return $this->belongsTo(CustomerSubscription::class, 'subscription_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function getAttachmentUrlAttribute()
    {
        if (!$this->attachment) return null;
        return s3_asset(true, 10, $this->attachment);
    }
}
