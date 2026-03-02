<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class WebhookSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'app_name',
        'selected_apps',
        'url',
        'token'
    ];

    protected $casts = [
        'selected_apps' => 'array'
    ];

    // Relasi ke company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeByCompany($query, $companyId)
    {
        // $companyIds = Auth::user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->where('company_id', $companyId);
    }

    public function scopeHasApp($q, string $appName)
    {
        return $q->whereJsonContains('selected_apps', $appName);
    }
    
    public static function dispatchIfHasUsedLaptops(array $payload, string $event = 'created'): void
    {
        $companyId = Auth::user()->company_id;

        // Ambil list setting sesuai filter
        $settings = self::byCompany($companyId)
            ->hasUsedLaptopsAndMultipleApps()
            ->get();

        foreach ($settings as $s) {
            SendWebhookJob::dispatch(
                url: $s->url,
                token: $s->token,
                payload: [
                    'event'     => $event,
                    'app'       => 'used_laptops',
                    'timestamp' => now()->toISOString(),
                    'data'      => $payload,
                    'signature' => hash_hmac('sha256', json_encode($payload), (string) $s->token),
                ]
            )->onQueue('webhooks');
        }
    }
}