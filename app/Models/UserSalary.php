<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSalary extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'letter_submission_id',
        'user_id',
        'salary'
    ];
    /**
     * Get the user associated with the user salary.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the letter submission associated with the salary.
     */
    public function letterSubmission()
    {
        return $this->belongsTo(LetterSubmission::class);
    }
}
