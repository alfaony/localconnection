<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OfficeMedia;
use Illuminate\Support\Facades\Storage;

class DeleteOldTemporaryMedia extends Command
{
    protected $signature = 'media:cleanup-temporary';
    protected $description = 'Delete temporary media older than 7 days, including physical file removal';

    public function handle()
    {
        $expiredMedia = OfficeMedia::where('is_temporary', true)
            ->where('created_at', '<', now()
            ->subDays(7)
            )
            ->get();

        $deletedCount = 0;

        foreach ($expiredMedia as $media) {
            // Hapus file jika type-nya image dan path-nya ada
            if ($media->type === 'image' && $media->file_path && Storage::exists($media->file_path)) {
                Storage::delete($media->file_path);
            }

            $media->delete(); // atau forceDelete() jika tidak soft deletes
            $deletedCount++;
        }

        $this->info("Deleted $deletedCount expired temporary media and associated files.");
    }
}
