<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FineTuneTable extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the fine_tuned_files for the FineTune.
     */
    public function fineTunedFiles()
    {
        return $this->hasMany(FineTuneFile::class);
    }

    /**
     * Get the file_tunes for the FineTune.
     */
    public function fileTunes()
    {
        return $this->hasMany(FileTune::class);
    }
}