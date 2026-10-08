<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SupabaseStorageService
{
    protected ?string $supabaseUrl;

    protected ?string $supabaseKey;

    protected string $defaultBucket;

    public function __construct()
    {
        $this->supabaseUrl = config('services.supabase.url', env('SUPABASE_URL'));
        $this->supabaseKey = config('services.supabase.key', env('SUPABASE_KEY'));
        $this->defaultBucket = config('services.supabase.bucket', env('SUPABASE_STORAGE_BUCKET', 'kost-images'));
    }

    /**
     * Upload an uploaded file to a specified bucket.
     */
    public function uploadFile(UploadedFile $file, string $bucket, string $folder = '', ?string $customFilename = null): array
    {
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $filename = $customFilename ?: Str::uuid()->toString().'.'.strtolower($extension);
        $path = ! empty($folder) ? trim($folder, '/')."/{$filename}" : $filename;

        // If Supabase credentials are configured, upload to Supabase Storage
        if (! empty($this->supabaseUrl) && ! empty($this->supabaseKey) && ! app()->environment('testing')) {
            try {
                $apiUrl = rtrim($this->supabaseUrl, '/')."/storage/v1/object/{$bucket}/{$path}";

                $response = Http::withoutVerifying()
                    ->withHeaders([
                        'Authorization' => "Bearer {$this->supabaseKey}",
                        'apiKey' => $this->supabaseKey,
                    ])
                    ->withBody(file_get_contents($file->getRealPath()), $file->getMimeType() ?: 'application/octet-stream')
                    ->post($apiUrl);

                if ($response->successful()) {
                    $publicUrl = rtrim($this->supabaseUrl, '/')."/storage/v1/object/public/{$bucket}/{$path}";

                    return [
                        'file_path' => $path,
                        'bucket' => $bucket,
                        'url' => $publicUrl,
                    ];
                }
            } catch (\Throwable $e) {
                // Fallback to local storage on network/SSL error
            }
        }

        // Fallback for local development and automated testing
        $savedPath = Storage::disk('public')->putFileAs("{$bucket}/{$folder}", $file, $filename);
        $publicUrl = Storage::disk('public')->url($savedPath);

        return [
            'file_path' => $savedPath,
            'bucket' => $bucket,
            'url' => $publicUrl,
        ];
    }

    /**
     * Upload an image file for a kost.
     */
    public function uploadKostImage(UploadedFile $file, int|string $kostId): array
    {
        return $this->uploadFile($file, 'kost-images', "kosts/{$kostId}");
    }

    /**
     * Upload an advertisement banner.
     */
    public function uploadAdBanner(UploadedFile $file): array
    {
        return $this->uploadFile($file, 'ad-banners', 'banners');
    }

    /**
     * Upload a user avatar.
     */
    public function uploadAvatar(UploadedFile $file, int|string $userId): array
    {
        return $this->uploadFile($file, 'user-avatars', "avatars/{$userId}");
    }

    /**
     * Upload verification document (KTP, selfie, ownership letter).
     */
    public function uploadVerificationDoc(UploadedFile $file, int|string $userId, string $type): array
    {
        return $this->uploadFile($file, 'verification-docs', "users/{$userId}/{$type}");
    }

    /**
     * Upload an APK release file.
     */
    public function uploadApk(UploadedFile $file, string $version): array
    {
        $safeVersion = Str::slug($version);
        $filename = "CariKostKita-{$safeVersion}.apk";

        return $this->uploadFile($file, 'apk-releases', 'releases', $filename);
    }

    /**
     * Delete a file from storage.
     */
    public function deleteFile(string $filePath, string $bucket = 'kost-images'): bool
    {
        if (! empty($this->supabaseUrl) && ! empty($this->supabaseKey) && ! app()->environment('testing')) {
            try {
                $apiUrl = rtrim($this->supabaseUrl, '/')."/storage/v1/object/{$bucket}";

                $response = Http::withoutVerifying()
                    ->withHeaders([
                        'Authorization' => "Bearer {$this->supabaseKey}",
                        'apiKey' => $this->supabaseKey,
                    ])->delete($apiUrl, [
                        'prefixes' => [$filePath],
                    ]);

                if ($response->successful()) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Fallback to local disk deletion
            }
        }

        return Storage::disk('public')->delete($filePath);
    }

    /**
     * Backwards compatible deleteImage method.
     */
    public function deleteImage(string $filePath): bool
    {
        return $this->deleteFile($filePath, $this->defaultBucket);
    }
}
