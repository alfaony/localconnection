<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class EventOccurrence extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Uuid::uuid4()->toString();
            }
        });
    }

    protected $fillable = [
        'event_id',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function eventViews()
    {
        return $this->hasMany(EventView::class);
    }
}
