<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BastFileMerge extends Model
{
    use SoftDeletes;

    protected $table = 'bast_file_merges';

    protected $fillable = [
        'version',
        'path',
        'bast_id',
    ];
}
