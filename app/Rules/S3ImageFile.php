<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Livewire\TemporaryUploadedFile;
use Illuminate\Support\Facades\Storage;

class S3UploadedFile implements Rule
{
    protected $maxSizeInKB;
    protected $allowedMimes;
    protected $errorMessage;

    public function __construct($maxSizeInKB = 2048, $allowedMimes = ['jpg', 'jpeg', 'png', 'pdf'])
    {
        $this->maxSizeInKB = $maxSizeInKB;
        $this->allowedMimes = $allowedMimes;
    }

    public function passes($attribute, $value)
    {
        if (!$value instanceof TemporaryUploadedFile) {
            $this->errorMessage = 'File harus diupload dengan benar.';
            return false;
        }

        // Validate extension
        $extension = strtolower($value->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedMimes)) {
            $this->errorMessage = 'File harus berupa: ' . implode(', ', $this->allowedMimes);
            return false;
        }

        // Get file size from S3 (bypass Laravel's getSize())
        try {
            $disk = $value->disk;
            $path = $value->path;
            
            // Get size from storage
            $sizeInBytes = Storage::disk($disk)->size($path);
            $sizeInKB = $sizeInBytes / 1024;
            
            if ($sizeInKB > $this->maxSizeInKB) {
                $this->errorMessage = 'Ukuran file maksimal ' . ($this->maxSizeInKB / 1024) . 'MB.';
                return false;
            }
        } catch (\Exception $e) {
            // If we can't get size, just validate extension
            \Log::warning('Unable to get file size from S3', [
                'error' => $e->getMessage(),
                'path' => $value->path ?? 'unknown'
            ]);
        }

        return true;
    }

    public function message()
    {
        return $this->errorMessage ?? 'File tidak valid.';
    }
}