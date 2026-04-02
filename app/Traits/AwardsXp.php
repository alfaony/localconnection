<?php

namespace App\Traits;

use App\Helpers\XpHelper;
use Illuminate\Support\Facades\Auth;

trait AwardsXp
{
    /**
     * Boot the trait for a model.
     * Menggunakan Eloquent event `created` untuk menjalankan aksi default 'store'.
     * Nantinya bisa diperluas ke event `updated` atau lainnya jika didukung oleh database.
     */
    public static function bootAwardsXp()
    {
        static::created(function ($model) {
            // Kita hanya berikan XP jika aksi dipicu oleh user yang login
            if (Auth::check()) {
                // Biarkan model menimpa deskripsi jika punya custom method
                $description = method_exists($model, 'getXpDescriptionForEvent')
                    ? $model->getXpDescriptionForEvent('store')
                    : 'Membuat ' . class_basename($model);

                // Kirim event 'store' secara konseptual melalui nama model sementara
                XpHelper::award(Auth::user(), $model, $description);
            }
        });
    }
}
