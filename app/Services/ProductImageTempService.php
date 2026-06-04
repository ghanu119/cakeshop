<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProductImageTempService
{
    public const MAX_BYTES = 5 * 1024 * 1024;

    public const CACHE_TTL_SECONDS = 86400;

    private const CACHE_PREFIX = 'product_image_temp:';

    public static function relativeStorageUrl(string $path): string
    {
        return '/storage/'.ltrim(str_replace('\\', '/', $path), '/');
    }

    public static function normalizePreviewUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        if (str_starts_with($url, '/')) {
            return $url;
        }

        $path = parse_url($url, PHP_URL_PATH);

        return $path ?: $url;
    }

    public function store(UploadedFile $file, User $user): array
    {
        $token = (string) Str::uuid();
        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg';
        $path = sprintf('temp/product-images/%d/%s.%s', $user->id, $token, $extension);

        Storage::disk('public')->putFileAs(
            dirname($path),
            $file,
            basename($path)
        );

        Cache::put(self::CACHE_PREFIX.$token, [
            'user_id' => $user->id,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ], self::CACHE_TTL_SECONDS);

        return [
            'token' => $token,
            'url' => self::relativeStorageUrl($path),
            'name' => $file->getClientOriginalName(),
        ];
    }

    public function previewUrl(string $token): ?string
    {
        $meta = Cache::get(self::CACHE_PREFIX.$token);
        if (! is_array($meta) || empty($meta['path'])) {
            return null;
        }

        return self::relativeStorageUrl($meta['path']);
    }

    /**
     * @return array{user_id: int, path: string, original_name: string, mime: ?string, size: int}
     */
    public function assertOwned(string $token, User $user): array
    {
        $meta = Cache::get(self::CACHE_PREFIX.$token);
        if (! is_array($meta) || ($meta['user_id'] ?? null) !== $user->id) {
            throw new RuntimeException(__('Invalid or expired image upload.'));
        }

        if (! Storage::disk('public')->exists($meta['path'])) {
            Cache::forget(self::CACHE_PREFIX.$token);
            throw new RuntimeException(__('Invalid or expired image upload.'));
        }

        return $meta;
    }

    public function delete(string $token, User $user): void
    {
        $meta = Cache::get(self::CACHE_PREFIX.$token);
        if (! is_array($meta) || ($meta['user_id'] ?? null) !== $user->id) {
            return;
        }

        if (! empty($meta['path']) && Storage::disk('public')->exists($meta['path'])) {
            Storage::disk('public')->delete($meta['path']);
        }

        Cache::forget(self::CACHE_PREFIX.$token);
    }

    /**
     * Returns absolute filesystem path for Spatie addMedia; removes cache entry.
     */
    public function consume(string $token, User $user): string
    {
        $meta = $this->assertOwned($token, $user);
        Cache::forget(self::CACHE_PREFIX.$token);

        return Storage::disk('public')->path($meta['path']);
    }

    public function pruneExpiredFiles(): int
    {
        $deleted = 0;
        $disk = Storage::disk('public');
        $directories = $disk->directories('temp/product-images');

        foreach ($directories as $directory) {
            foreach ($disk->files($directory) as $file) {
                if ($disk->lastModified($file) < now()->subDay()->timestamp) {
                    $disk->delete($file);
                    $deleted++;
                }
            }
        }

        return $deleted;
    }
}
