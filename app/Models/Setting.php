<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Setting extends Model
{
    use HasFactory;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'key',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the company that owns the setting.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to filter by company.
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to get global settings.
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('company_id');
    }

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param string|null $companyId
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $companyId = null, $default = null)
    {
        $setting = static::where('key', $key)
            ->where('company_id', $companyId)
            ->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $companyId
     * @return Setting
     */
    public static function set($key, $value, $companyId = null)
    {
        return static::updateOrCreate(
            [
                'key' => $key,
                'company_id' => $companyId,
            ],
            [
                'value' => $value,
            ]
        );
    }

    /**
     * Check if a setting exists.
     *
     * @param string $key
     * @param string|null $companyId
     * @return bool
     */
    public static function has($key, $companyId = null)
    {
        return static::where('key', $key)
            ->where('company_id', $companyId)
            ->exists();
    }

    /**
     * Delete a setting.
     *
     * @param string $key
     * @param string|null $companyId
     * @return bool
     */
    public static function remove($key, $companyId = null)
    {
        return static::where('key', $key)
            ->where('company_id', $companyId)
            ->delete();
    }

    /**
     * Get all settings for a company as key-value array.
     *
     * @param string|null $companyId
     * @return array
     */
    public static function getAllForCompany($companyId = null)
    {
        return static::where('company_id', $companyId)
            ->pluck('value', 'key')
            ->toArray();
    }
}