<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_name',
        'template_agreement',
        'template_agreement_show',
        'is_active',
        'is_default',
    ];
}