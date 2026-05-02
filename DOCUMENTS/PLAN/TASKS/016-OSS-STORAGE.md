# TASK-016: OSS File Storage Integration

**Phase:** 5 — External Service Integrations (Backend)  
**Status:** Pending  
**Depends On:** TASK-003  
**Priority:** HIGH  

---

## Objective

Integrate Aliyun OSS (Object Storage Service) as the file storage backend for all file uploads: resumes, avatars, logos, favicons, seminar thumbnails, and speaker photos. Implement a reusable `OssService` that other modules call. Also handle presigned URLs for secure file access.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Section 2.3 (module architecture, storage references)
2. `DOCUMENTS/REQUIREMENTS-EN.md` — Section VI (Storage: OSS for resumes, images, seminar recordings)
3. `DOCUMENTS/SOLUTION.md` — Section 7.2 (File Storage: Local + OSS ready)

---

## Packages to Install

```bash
composer require aliyuncs/oss-sdk-php
```

Or use the S3-compatible interface with AWS SDK:
```bash
composer require league/flysystem-aws-s3-v3
```

Configure in `config/filesystems.php` as disk `oss`.

---

## Deliverables

### Service
- `app/Services/OssService.php`
  ```php
  class OssService {
      // Upload file to OSS, return object key (relative path)
      public function upload(UploadedFile $file, string $directory): string
      
      // Upload from local path
      public function uploadFromPath(string $localPath, string $ossKey): string
      
      // Delete object by key
      public function delete(string $key): void
      
      // Generate presigned URL (time-limited access)
      public function presignedUrl(string $key, int $expirySeconds = 3600): string
      
      // Generate public URL (for public-read buckets like logos)
      public function publicUrl(string $key): string
  }
  ```

### Storage Directories (OSS key prefixes)

| Directory | Content | Access |
|-----------|---------|--------|
| `resumes/{student_id}/` | Resume files | Private (presigned URL) |
| `avatars/students/` | Student avatars | Public-read |
| `avatars/enterprises/` | Enterprise logos | Public-read |
| `assets/` | Site logo, favicon | Public-read |
| `thumbnails/seminars/` | Seminar thumbnails | Public-read |
| `thumbnails/posts/` | Post thumbnails | Public-read |
| `universities/logos/` | University logos | Public-read |

### Config
- `config/oss.php` or add to `config/filesystems.php`:
  ```php
  'oss' => [
      'driver' => 's3',
      'key' => env('OSS_ACCESS_KEY_ID'),
      'secret' => env('OSS_ACCESS_KEY_SECRET'),
      'region' => env('OSS_REGION', 'oss-cn-hangzhou'),
      'bucket' => env('OSS_BUCKET'),
      'endpoint' => env('OSS_ENDPOINT'),
      'url' => env('OSS_URL'),
  ]
  ```

### Fallback (Local Development)
When `OSS_BUCKET` is empty, fall back to `local` disk (under `storage/app/public`):
- `OssService::upload()` stores to local storage
- `OssService::presignedUrl()` returns a signed temporary route
- `OssService::publicUrl()` returns Storage::url($key)

This allows development without actual OSS credentials.

---

## File Processing Before Upload

### Resume Files
- Accept: pdf, doc, docx, jpg, jpeg, png
- Max size: 20MB
- No processing — store as-is
- Generate unique filename: `{uuid}.{ext}`

### Images (Avatar, Logo, Thumbnail)
- Use `intervention/image` package
- Avatar: resize to max 500x500, convert to JPEG, quality 85
- Logo: resize to max 800x400, preserve aspect ratio, convert to PNG
- Thumbnail: resize to 800x450 (16:9), convert to JPEG, quality 80
- Favicon: accept only .ico or .png (no resizing)

---

## API Endpoint (if needed)

No direct file upload endpoint in this task — all uploads are handled within their respective module controllers (TASK-010 for resumes, TASK-011 for logos, etc.). `OssService` is called from those controllers.

---

## Acceptance Criteria

- [ ] `OssService::upload()` uploads file to OSS (or local fallback)
- [ ] `OssService::delete()` removes file from OSS
- [ ] `OssService::presignedUrl()` generates time-limited URL (1 hour default)
- [ ] `OssService::publicUrl()` returns direct CDN/public URL
- [ ] Avatar uploads resized to max 500x500 before upload
- [ ] Logo uploads resized to max 800x400 before upload
- [ ] Resume files uploaded without modification
- [ ] Local fallback works when OSS credentials not configured (development)
- [ ] All uploaded filenames are UUIDs (no user-controlled filenames in storage paths)
- [ ] `OSS_*` env variables documented in `.env.example`

---

## Security Notes

- Resume files: NEVER stored in public-read bucket folder — always use presigned URLs
- User-uploaded filenames are NOT used as storage keys — always generate UUID filename
- File type validation: check both MIME type and file extension (not just extension)
- File size validated at the controller level before calling OssService
- Presigned URL expiry: 1 hour for resumes, can be configured per use case
