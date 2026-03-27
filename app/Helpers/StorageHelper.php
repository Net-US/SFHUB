<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    /**
     * Directory paths configuration
     */
    public static function getDirectories(): array
    {
        return [
            'landing' => env('IMAGE_LANDING_DIR', 'images/landing'),
            'site' => env('IMAGE_SITE_DIR', 'images/site'),
            'blog' => env('IMAGE_BLOG_DIR', 'images/blog'),
            'favicon' => env('IMAGE_FAVICON_DIR', 'images/site'),
        ];
    }

    /**
     * Get directory path by type
     */
    public static function getDirectory(string $type): string
    {
        $directories = self::getDirectories();
        return $directories[$type] ?? 'images/uploads';
    }

    /**
     * Get the base path for storing files (write path)
     * Selalu gunakan public_path() untuk write - standar Laravel
     */
    public static function getWritePath(string $subPath = ''): string
    {
        $basePath = public_path();  // Selalu pakai public_path()

        if ($subPath) {
            return rtrim($basePath, '/') . '/' . ltrim($subPath, '/');
        }

        return $basePath;
    }

    /**
     * Get the base URL/path for reading files (read path)
     * Default: '' - reads from relative path
     */
    public static function getReadUrl(string $path = ''): string
    {
        // If full URL is stored, return as-is
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $baseUrl = env('STORAGE_READ_URL', '');

        if ($path) {
            // If path starts with /, remove it for concatenation
            $path = ltrim($path, '/');

            if ($baseUrl) {
                return rtrim($baseUrl, '/') . '/' . $path;
            }

            // Use Laravel's asset() helper if no custom base URL
            return asset($path);
        }

        return $baseUrl ?: asset('');
    }

    /**
     * Get full path for image type (directory + filename)
     *
     * @param string $filename - just the filename (e.g., 'logo_123.png')
     * @param string $type - 'landing', 'site', 'blog', 'favicon'
     * @return string full relative path (e.g., 'images/site/logo_123.png')
     */
    public static function getImagePath(?string $filename, string $type = 'site'): string
    {
        if (!$filename) {
            return '';
        }

        // If already a full URL, return as-is
        if (filter_var($filename, FILTER_VALIDATE_URL)) {
            return $filename;
        }

        // If already has directory path, extract just the filename
        $filename = basename($filename);

        $directory = self::getDirectory($type);
        return $directory . '/' . $filename;
    }

    /**
     * Get full URL for image type
     *
     * @param string $filename - just the filename (e.g., 'logo_123.png')
     * @param string $type - 'landing', 'site', 'blog', 'favicon'
     * @return string full URL
     */
    public static function getImageUrl(?string $filename, string $type = 'site'): string
    {
        if (!$filename) {
            return '';
        }

        // If already a full URL, return as-is
        if (filter_var($filename, FILTER_VALIDATE_URL)) {
            return $filename;
        }

        // Get full path and convert to URL
        $fullPath = self::getImagePath($filename, $type);
        return self::getReadUrl($fullPath);
    }

    /**
     * Store file to configured write path
     * Returns only the filename (not full path)
     */
    public static function storeFile($file, string $directory, string $filename): array
    {
        $writePath = self::getWritePath($directory);

        // Create directory if not exists
        if (!file_exists($writePath)) {
            @mkdir($writePath, 0755, true);
        }

        // Move file
        $file->move($writePath, $filename);

        // Return only filename and full URL
        return [
            'filename' => $filename,  // Only filename, no path
            'full_url' => self::getReadUrl($directory . '/' . $filename),
            'write_path' => $writePath . '/' . $filename,
            'directory' => $directory,
        ];
    }

    /**
     * Delete file from write path
     * Accepts just filename or full path
     */
    public static function deleteFile(string $filename, string $type = 'site'): bool
    {
        if (!$filename) {
            return false;
        }

        // Extract just the filename if full path provided
        $filename = basename($filename);
        $directory = self::getDirectory($type);
        $relativePath = $directory . '/' . $filename;

        $writePath = self::getWritePath($relativePath);

        if (file_exists($writePath)) {
            return @unlink($writePath);
        }

        return false;
    }

    /**
     * Check if file exists in write path
     * Accepts just filename or full path
     */
    public static function fileExists(string $filename, string $type = 'site'): bool
    {
        if (!$filename) {
            return false;
        }

        // Extract just the filename if full path provided
        $filename = basename($filename);
        $directory = self::getDirectory($type);
        $relativePath = $directory . '/' . $filename;

        $writePath = self::getWritePath($relativePath);
        return file_exists($writePath);
    }
}
