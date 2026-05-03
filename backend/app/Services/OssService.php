<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

/**
 * Object Storage Service — abstraction layer for all file storage.
 *
 * Automatically uses the 'oss' disk (S3-compatible Aliyun OSS) when OSS_BUCKET
 * is configured in the environment. Falls back to the 'public' (local) disk for
 * development without OSS credentials.
 *
 * Storage key prefixes (relative paths):
 *   resumes/{student_id}/          — private, access via presigned URL
 *   avatars/students/{student_id}/ — public-read
 *   logos/enterprises/{enterprise_id}/ — public-read
 *   assets/logos/                  — public-read (site logo)
 *   assets/favicons/               — public-read (site favicon)
 *   thumbnails/seminars/           — public-read
 *   thumbnails/posts/              — public-read
 *   universities/logos/            — public-read
 */
class OssService
{
    /**
     * Determine the disk name at runtime.
     * OSS is used when OSS_BUCKET is non-empty; otherwise falls back to local public disk.
     */
    private function diskName(): string
    {
        return config('filesystems.disks.oss.bucket') ? 'oss' : 'public';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Generic upload
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Upload a file to the storage disk without any processing.
     * A UUID filename is generated automatically to prevent path traversal.
     *
     * @param  UploadedFile  $file
     * @param  string        $directory  Storage key prefix (e.g. 'resumes/42')
     * @return string  Stored key (relative path on disk)
     */
    public function upload(UploadedFile $file, string $directory): string
    {
        $ext      = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid() . '.' . $ext;
        $key      = ltrim($directory, '/') . '/' . $filename;

        Storage::disk($this->diskName())->put($key, file_get_contents($file->getRealPath()));

        return $key;
    }

    /**
     * Upload a file from a local absolute path using a caller-specified key.
     *
     * @param  string  $localPath  Absolute filesystem path to read from
     * @param  string  $ossKey     Destination key on the disk
     * @return string  The $ossKey as stored
     */
    public function uploadFromPath(string $localPath, string $ossKey): string
    {
        Storage::disk($this->diskName())->put($ossKey, file_get_contents($localPath));

        return $ossKey;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Image-specific upload methods (Intervention\Image processing)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Upload a student avatar.
     * Processed: fit 500×500, JPEG quality 85.
     *
     * @param  UploadedFile  $file
     * @param  string        $directory  e.g. 'avatars/students/42'
     * @return string  Stored key
     */
    public function uploadAvatar(UploadedFile $file, string $directory): string
    {
        $img      = Image::make($file)->fit(500, 500);
        $encoded  = (string) $img->encode('jpg', 85);
        $key      = ltrim($directory, '/') . '/' . Str::uuid() . '.jpg';

        Storage::disk($this->diskName())->put($key, $encoded);

        return $key;
    }

    /**
     * Upload an enterprise or site logo.
     * Processed: resize to max 800×400, preserve aspect ratio, PNG.
     *
     * @param  UploadedFile  $file
     * @param  string        $directory  e.g. 'logos/enterprises/7'
     * @return string  Stored key
     */
    public function uploadLogo(UploadedFile $file, string $directory): string
    {
        $img = Image::make($file)->resize(800, 400, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $encoded = (string) $img->encode('png');
        $key     = ltrim($directory, '/') . '/' . Str::uuid() . '.png';

        Storage::disk($this->diskName())->put($key, $encoded);

        return $key;
    }

    /**
     * Upload a seminar or post thumbnail.
     * Processed: fit 800×450 (16:9), JPEG quality 80.
     *
     * @param  UploadedFile  $file
     * @param  string        $directory  e.g. 'thumbnails/seminars'
     * @return string  Stored key
     */
    public function uploadThumbnail(UploadedFile $file, string $directory): string
    {
        $img     = Image::make($file)->fit(800, 450);
        $encoded = (string) $img->encode('jpg', 80);
        $key     = ltrim($directory, '/') . '/' . Str::uuid() . '.jpg';

        Storage::disk($this->diskName())->put($key, $encoded);

        return $key;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Delete
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Delete an object from the disk by key.
     * Silently ignores missing keys.
     */
    public function delete(string $key): void
    {
        Storage::disk($this->diskName())->delete($key);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // URL helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate a time-limited presigned URL for private objects (e.g. resumes).
     *
     * On OSS: returns a real presigned URL valid for $expirySeconds.
     * On local public disk: returns a regular public URL (development fallback;
     *   there is no true presigning for local storage).
     *
     * @param  string  $key             Storage key (relative path)
     * @param  int     $expirySeconds   Default 3600 (1 hour)
     * @return string
     */
    public function presignedUrl(string $key, int $expirySeconds = 3600): string
    {
        $disk = $this->diskName();

        if ($disk === 'oss') {
            return Storage::disk('oss')->temporaryUrl($key, now()->addSeconds($expirySeconds));
        }

        // Local fallback — return plain public URL
        return Storage::disk('public')->url($key);
    }

    /**
     * Return the permanent public URL for a public-read object (avatars, logos, etc.).
     *
     * @param  string|null  $key
     * @return string|null
     */
    public function publicUrl(?string $key): ?string
    {
        if (!$key) {
            return null;
        }

        return Storage::disk($this->diskName())->url($key);
    }

    /**
     * Check whether an object exists on the disk.
     */
    public function exists(string $key): bool
    {
        return Storage::disk($this->diskName())->exists($key);
    }
}
