<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InternetCustomerInstallation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'internet_customer_id',
        'technical_user_id',
        'device_serial_number',
        'installed_at',
        'notes',
    ];

    // ✅ RELATIONS
    public function internetCustomer()
    {
        return $this->belongsTo(InternetCustomer::class)->withTrashed();
    }

    public function technicalUser()
    {
        return $this->belongsTo(User::class, 'technical_user_id')->withTrashed();
    }

    public function medias()
    {
        return $this->hasMany(InternetInstallationPhoto::class, 'internet_installation_id', 'id');
    }
}