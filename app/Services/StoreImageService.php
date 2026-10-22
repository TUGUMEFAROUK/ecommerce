<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StoreImageService
{
    /**
     * The disk that storefront images are served from.
     */
    private const DISK = 'public';

    /**
     * Resolve the stored path for an image slot after an upload or removal.
     *
     * An upload always wins: the new file is written first and only then is
     * the previous file deleted, so a failed write can never leave the
     * settings pointing at a file that no longer exists.
     *
     * @param  string  $directory  The directory on the public disk to store the file in.
     */
    public function resolve(?string $currentPath, ?UploadedFile $upload, string $directory, bool $remove = false): ?string
    {
        if ($upload instanceof UploadedFile && $upload->isValid()) {
            $path = $upload->store($directory, self::DISK);

            if (! is_string($path) || $path === '') {
                return $currentPath;
            }

            $this->delete($currentPath);

            return $path;
        }

        if ($remove) {
            $this->delete($currentPath);

            return null;
        }

        return $currentPath;
    }

    /**
     * Remove a stored image file.
     */
    public function delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        Storage::disk(self::DISK)->delete($path);
    }

    /**
     * The public URL of a stored image, when the file is still present.
     */
    public function url(?string $path): ?string
    {
        if (blank($path) || ! Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        return Storage::disk(self::DISK)->url($path);
    }
}
