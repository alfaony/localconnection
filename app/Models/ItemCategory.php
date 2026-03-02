<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'type',
    ];

    /**
     * Relasi ke Company (jika kamu pakai multi-company)
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relasi ke MasterCheckItem
     */
    public function checkItems()
    {
        return $this->hasMany(MasterCheckItem::class, 'item_category_id');
    }

    /**
     * Scope opsional untuk filter berdasarkan type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}