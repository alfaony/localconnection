<?php

namespace App\Helpers;

use App\Jobs\AwardXpJob;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class XpHelper
{
    /**
     * Berikan XP kepada user berdasarkan aksi yang dilakukan.
     *
     * @param User   $user         User yang mendapat XP
     * @param Model  $model        Model yang jadi sumber XP (DailyTask, Meeting, dll.)
     * @param string|null $description  Deskripsi custom (opsional)
     * @param int|null    $overrideXp   Override nilai XP (opsional, abaikan config)
     */
    public static function award(
        User $user,
        Model $model,
        ?string $description = null,
        ?int $overrideXp = null
    ): void {
        $config = self::getConfig($user->company_id);

        // Jika company tidak punya config atau XP dimatikan → skip
        if (!$config || !$config->is_enabled) {
            return;
        }

        $sourceType = class_basename($model);
        $xp = $overrideXp ?? $config->resolveXp($sourceType);

        AwardXpJob::dispatch(
            $user->id,
            $user->company_id,
            $xp,
            $sourceType,
            (string) $model->id,
            $description ?? "Aksi: {$sourceType}"
        );
    }

    /**
     * Berikan XP minus (penalti) kepada user.
     *
     * @param User   $user
     * @param int    $xp          Nilai penalti (positif, akan dikonversi ke negatif)
     * @param string $sourceType  Nama model sumber penalti
     * @param string $description Alasan penalti
     */
    public static function penalty(
        User $user,
        int $xp,
        string $sourceType,
        string $description
    ): void {
        $config = self::getConfig($user->company_id);

        if (!$config || !$config->is_enabled) {
            return;
        }

        AwardXpJob::dispatch(
            $user->id,
            $user->company_id,
            -abs($xp),
            $sourceType,
            null,
            $description
        );
    }

    /**
     * Ambil XpConfig milik company.
     * Di-cache 10 menit agar tidak query DB setiap aksi.
     */
    private static function getConfig(string $companyId)
    {
        return Cache::remember("xp_config_{$companyId}", 600, function () use ($companyId) {
            $company = Company::with('xpConfig.models')->find($companyId);
            return $company?->xpConfig;
        });
    }

    /**
     * Clear cache XP config untuk company tertentu.
     * Dipanggil setelah admin mengubah config.
     */
    public static function clearConfigCache(string $companyId): void
    {
        Cache::forget("xp_config_{$companyId}");
    }
}
