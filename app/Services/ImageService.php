<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

/**
 * Resizes and stores uploaded images on the "public" disk, used for
 * product galleries, category images, and the logo/favicon uploaded from
 * admin Settings. Keeps a full-size (capped) copy plus a thumbnail so
 * grids/listings never have to download a multi-megabyte original.
 */
class ImageService
{
    /**
     * Store $file under storage/app/public/{$directory}, returning the
     * disk-relative paths of the (resized) original and its thumbnail.
     *
     * @return array{path: string, thumb_path: string}
     */
    public function storeWithThumbnail(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1600,
        int $thumbWidth = 500
    ): array {
        $filename = Str::uuid().'.'.($file->getClientOriginalExtension() ?: 'jpg');
        $thumbFilename = Str::uuid().'.'.($file->getClientOriginalExtension() ?: 'jpg');

        $image = Image::make($file->getRealPath());
        $image->orientate(); // respect EXIF rotation from phone cameras

        // Full-size: capped so a 20MP phone photo doesn't bloat storage,
        // but never upscaled if the original is already smaller.
        $full = clone $image;
        $full->resize($maxWidth, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $thumb = clone $image;
        $thumb->resize($thumbWidth, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $path = trim($directory, '/')."/{$filename}";
        $thumbPath = trim($directory, '/')."/thumbs/{$thumbFilename}";

        Storage::disk('public')->put($path, (string) $full->encode());
        Storage::disk('public')->put($thumbPath, (string) $thumb->encode());

        return ['path' => $path, 'thumb_path' => $thumbPath];
    }

    /**
     * Store a single resized image with no separate thumbnail — used for
     * the shop logo/favicon, which are displayed at one small size anyway.
     *
     * $trimTransparentPadding crops away any transparent margin around the
     * artwork before resizing. Logo exports from design tools routinely
     * leave empty canvas space around the mark; without trimming, that
     * padding gets scaled along with the artwork by the <x-logo> component's
     * fixed-height/object-contain display, making the visible logo render
     * noticeably smaller than the height class it's given. Only meaningful
     * for formats with an alpha channel, so it's opt-in (used for the logo
     * and favicon, not category photos, which are plain photography).
     */
    public function storeSingle(UploadedFile $file, string $directory, int $maxWidth = 800, bool $trimTransparentPadding = false): string
    {
        $filename = Str::uuid().'.'.($file->getClientOriginalExtension() ?: 'png');

        // SVGs pass through untouched — Intervention/GD can't rasterize
        // vector markup, and a hand-authored SVG logo shouldn't be.
        if (strtolower($file->getClientOriginalExtension()) === 'svg') {
            $path = trim($directory, '/')."/{$filename}";
            Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

            return $path;
        }

        $image = Image::make($file->getRealPath());
        $image->orientate();

        if ($trimTransparentPadding && $image->mime() !== 'image/jpeg') {
            $image->trim('transparent');
        }

        $image->resize($maxWidth, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $path = trim($directory, '/')."/{$filename}";
        Storage::disk('public')->put($path, (string) $image->encode());

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
