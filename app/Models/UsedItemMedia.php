<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsedItemMedia extends Model
{
    protected $fillable = ['used_item_id', 'file_path', 'caption'];

    public function item()
    {
        return $this->belongsTo(UsedItem::class);
    }

}

