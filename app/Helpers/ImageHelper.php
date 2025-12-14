<?php

namespace App\Helpers;

class ImageHelper
{
    /**
     * Get full CDN URL for an image path
     *
     * @param string|null $path
     * @return string|null
     */
    public static function cdnUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $cdnUrl = config('app.cdn_url');

        if (empty($cdnUrl)) {
            return $path;
        }

        // Remove leading slash if present
        $path = ltrim($path, '/');

        return rtrim($cdnUrl, '/') . '/' . $path;
    }

    /**
     * Get full CDN URL for a page image
     * Format: https://cdn.truyenvie.com/qq-comics/images/{comic_id}/{chapter_number}/{image_url}
     *
     * @param int $comicId
     * @param float $chapterNumber
     * @param string|null $imageUrl
     * @return string|null
     */
    public static function pageUrl(int $comicId, float $chapterNumber, ?string $imageUrl): ?string
    {
        if (empty($imageUrl)) {
            return null;
        }

        $cdnPageUrl = config('app.cdn_page_url');

        if (empty($cdnPageUrl)) {
            return $imageUrl;
        }

        // Remove leading slash if present
        $imageUrl = ltrim($imageUrl, '/');

        return rtrim($cdnPageUrl, '/') . "/{$comicId}/{$chapterNumber}/{$imageUrl}";
    }
}

