<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\Role;
use App\Helpers\Access;

class ClearRolePermissionCache extends Command
{
    /**
     * Nama perintah artisan
     *
     * @var string
     */
    protected $signature = 'cache:clear-role-permissions {--roleId=* : (Opsional) ID Role spesifik yang ingin dihapus}';

    /**
     * Deskripsi perintah
     *
     * @var string
     */
    protected $description = 'Menghapus semua cache role_permissions (atau role tertentu jika disertakan --roleId)';

    /**
     * Eksekusi perintah
     */
    public function handle()
    {
        $roleIds = $this->option('roleId');

        if (empty($roleIds)) {
            // Hapus semua role_permissions
            $roles = Role::pluck('id');
            $count = 0;

            foreach ($roles as $roleId) {
                Cache::forget("role_permissions:{$roleId}");
                Access::clearCacheForRole($roleId);
                $count++;
            }

            $this->info("✅ Cache role_permissions untuk {$count} role berhasil dihapus.");
        } else {
            // Hapus berdasarkan role ID tertentu
            foreach ($roleIds as $roleId) {
                Cache::forget("role_permissions:{$roleId}");
                Access::clearCacheForRole($roleId);
                $this->info("✅ Cache role_permissions:{$roleId} berhasil dihapus.");
            }
        }

        return Command::SUCCESS;
    }
}