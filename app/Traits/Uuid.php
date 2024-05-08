<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;

/**
 * Uuid
 */
trait Uuid
{
    protected static function boot()
    {
        // Boot other traits on the Model
        parent::boot();

        /**
         * Listen for the 'creating' event on the Track Model.
         * Sets the 'id' to a UUID using Str::uuid() on the instance being created
         */
        static::creating(function ($model) {
            // Check if the primary key doesn't have a value
            if (!$model->getKey()) {
                // Dynamically set the primary key
                $model->setAttribute($model->getKeyName(), str()->uuid()->toString());
            }
        });

        static::saving(function ($model) {
            if (Schema::hasColumn($model->getTable(), 'slug')) {
                $originalSlug = $model->name;
                $slug = $originalSlug;
                $count = 1;

                // Check if the slug already exists in the database
                while (static::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }

                $model->slug = $slug;
            }
        });
    }

    // Tells Eloquent Model not to auto-increment this field
    public function getIncrementing()
    {
        return false;
    }

    // Tells that the IDs on the table should be stored as strings
    public function getKeyType()
    {
        return 'string';
    }
}
