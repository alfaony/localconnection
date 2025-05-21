<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'item_request_id',
        'user_id',
        'message',
        'attachment',
    ];

    public function itemRequest()
    {
        return $this->belongsTo(ItemRequest::class)->withTrashed();
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }
}
