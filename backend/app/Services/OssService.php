<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Object Storage Service — abstraction layer for file storage.
 *
 * TASK-016: Replace Storage::disk('public') calls with Aliyun OSS / S3 using
 * the configured `league/flysystem-aws-s3-v3` driver.
 *
 * Current implementation uses the `public` disk (local storage).
 * To switch to OSS in TASK-016:
 *   1. Set FILESYSTEM_DISK=s3 (or oss) in .env
 *   2. Configure config/filesystems.php with OSS endpoint/key/secret
 *   3. Update `disk()` method below to return `Storage::disk('s3')` or `Storage::disk('oss')`
 *
 * All file upload/delete/url operations in the app should route through this service.
 */
class OssService
{
    /**
     * Resolved disk name.
     * TASK-016: change to 's3' or 'oss' when OSS is configured.
     */
    private string $disk = 'public';

    /**
     * Upload a file to the configured disk.
     *
     * @param  UploadedFile  $file
     * @param  string        $directory  Relative directory path (e.g. 'avatars', 'resumes')
     * @param  string|null   $filename   Optional custom filename (without extension)
     * @return string  Stored path (relative to disk root)
     */
    public function upload(UploadedFile $file, string $directory, ?string $filename = null): string
    {
        if ($filename) {
            $name = $filename . '.' . $file->getClientOriginalExtension();
            return $file->storeAs($directory, $name, $this->disk);
        }

        return $file->store($directory, $this->disk);
    }

    /**
     * Delete a file from the configured disk.
     *
     * @param  string  $path  Relative path returned by upload()
     */
    public function delete(string $path): void
    {
        Storage::disk($this->disk)->delete($path);
    }

    /**
     * Get the public URL for a stored file.
     *
     * TASK-016: For OSS/S3 this returns a CDN or presigned URL.
     * For local public disk this returns an app-served URL.
     *
     * @param  string|null  $path
     * @return string|null
     */
    public function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // TASK-016: For S3/OSS — return Storage::disk($this->disk)->url($path);
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Check if a file exists on the disk.
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }
}
