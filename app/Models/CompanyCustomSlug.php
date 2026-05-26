<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyCustomSlug extends Model
{
    protected $fillable = ['company_id', 'slug'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
