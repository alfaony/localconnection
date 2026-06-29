<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\InternetCustomerPurchase;
use Carbon\Carbon;

class CleanupPurchaseMedia extends Command
{
    protected $signature = 'purchase:cleanup-media
                            {--months=2 : Hapus media purchase yang updated_at-nya lebih lama dari N bulan (0 = hapus semua)}
                            {--dry-run : Simulasi tanpa benar-benar menghapus}';

    protected $description = 'Hapus file S3 payment_proof dari InternetCustomerPurchase yang sudah lama (default: > 2 bulan)';

    public function handle(): int
    {
        $months  = (int) $this->option('months');
        $dryRun  = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('[DRY-RUN] Tidak ada file yang akan dihapus.');
        }

        $query = InternetCustomerPurchase::whereNotNull('payment_proof');

        if ($months > 0) {
            $cutoff = Carbon::now()->subMonths($months);
            $query->where('updated_at', '<', $cutoff);
            $this->info("Mencari purchase dengan updated_at < {$cutoff->toDateTimeString()} ({$months} bulan lalu)...");
        } else {
            $this->warn('Opsi --months=0: akan menghapus SEMUA media payment_proof.');
            if (!$this->confirm('Yakin ingin menghapus semua media?', false)) {
                $this->info('Dibatalkan.');
                return 0;
            }
        }

        $purchases = $query->get(['id', 'payment_proof', 'updated_at']);

        if ($purchases->isEmpty()) {
            $this->info('Tidak ada media yang ditemukan.');
            return 0;
        }

        $this->info("Ditemukan {$purchases->count()} record dengan payment_proof.");
        $this->newLine();

        $deleted   = 0;
        $missing   = 0;
        $failed    = 0;

        // Buat instance disk dengan throw=true agar error tidak ditelan diam-diam
        $disk = Storage::build([
            'driver'                  => 's3',
            'key'                     => config('filesystems.disks.s3.key'),
            'secret'                  => config('filesystems.disks.s3.secret'),
            'region'                  => config('filesystems.disks.s3.region'),
            'bucket'                  => config('filesystems.disks.s3.bucket'),
            'url'                     => config('filesystems.disks.s3.url'),
            'endpoint'                => config('filesystems.disks.s3.endpoint'),
            'use_path_style_endpoint' => config('filesystems.disks.s3.use_path_style_endpoint', false),
            'throw'                   => true,
        ]);

        $bar = $this->output->createProgressBar($purchases->count());
        $bar->start();

        foreach ($purchases as $purchase) {
            $path = $purchase->payment_proof;

            try {
                if ($disk->exists($path)) {
                    if (!$dryRun) {
                        $disk->delete($path);
                        $purchase->payment_proof = null;
                        $purchase->saveQuietly();
                    }
                    $deleted++;
                } else {
                    // File tidak ada di S3, bersihkan kolom saja
                    if (!$dryRun) {
                        $purchase->payment_proof = null;
                        $purchase->saveQuietly();
                    }
                    $missing++;
                }
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("Gagal hapus purchase #{$purchase->id} (path: {$path}): {$e->getMessage()}");
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $label = $dryRun ? '[DRY-RUN] Akan dihapus' : 'Berhasil dihapus dari S3';
        $this->info("{$label}             : {$deleted} file");
        $this->info("File tidak ada di S3 (kolom dikosongkan) : {$missing}");

        if ($failed > 0) {
            $this->warn("Gagal                                    : {$failed} file");
        }

        return 0;
    }
}
