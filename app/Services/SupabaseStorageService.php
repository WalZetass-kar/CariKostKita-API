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

    protected string $bucket;

    public function __construct()
    {
        $this->supabaseUrl = config('services.supabase.url', env('SUPABASE_URL'));
        $this->supabaseKey = config('services.supabase.key', env('SUPABASE_KEY'));
        $this->bucket = config('services.supabase.bucket', env('SUPABASE_STORAGE_BUCKET', 'kost-images'));
    }

    /**
     * Upload an image file for a kost.
     */
    public function uploadKostImage(UploadedFile $file, int|string $kostId): array
    {
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = Str::uuid()->toString().'.'.strtolower($extension);
        $path = "kosts/{$kostId}/{$filename}";

        // If Supabase credentials are configured, upload to Supabase Storage
        if (! empty($this->supabaseUrl) && ! empty($this->supabaseKey)) {
            $apiUrl = rtrim($this->supabaseUrl, '/')."/storage/v1/object/{$this->bucket}/{$path}";

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->supabaseKey}",
                'apiKey' => $this->supabaseKey,
            ])
                ->withBody(file_get_contents($file->getRealPath()), $file->getMimeType())
                ->post($apiUrl);

            if ($response->successful()) {
                $publicUrl = rtrim($this->supabaseUrl, '/')."/storage/v1/object/public/{$this->bucket}/{$path}";

                return [
                    'file_path' => $path,
                    'url' => $publicUrl,
                ];
            }
        }

        // Fallback for local development and testing
        $savedPath = Storage::disk('public')->putFileAs("kost-images/{$kostId}", $file, $filename);
        $publicUrl = Storage::disk('public')->url($savedPath);

        return [
            'file_path' => $savedPath,
            'url' => $publicUrl,
        ];
    }

    /**
     * Delete an image from storage.
     */
    public function deleteImage(string $filePath): bool
    {
        if (! empty($this->supabaseUrl) && ! empty($this->supabaseKey)) {
            $apiUrl = rtrim($this->supabaseUrl, '/')."/storage/v1/object/{$this->bucket}";

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->supabaseKey}",
                'apiKey' => $this->supabaseKey,
            ])->delete($apiUrl, [
                'prefixes' => [$filePath],
            ]);

            if ($response->successful()) {
                return true;
            }
        }

        return Storage::disk('public')->delete($filePath);
    }
}
