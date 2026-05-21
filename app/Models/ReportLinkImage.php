<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportLinkImage extends Model
{
    protected $fillable = ['report_link_id', 'path', 'description', 'order'];

    public function reportLink()
    {
        return $this->belongsTo(ReportLink::class);
    }
}
