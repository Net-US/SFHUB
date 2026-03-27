<?php

if (!function_exists('get_image_base_path')) {
    /**
     * Get the base path for image storage
     * Automatically detects if images folder is in public/ or root
     * Can be overridden via .env IMAGE_PATH
     *
     * @return string
     */
    function get_image_base_path(): string
    {
        // Check if custom path is set in .env
        $customPath = env('IMAGE_STORAGE_PATH');
        if ($customPath) {
            return base_path(trim($customPath, '/'));
        }

        // Auto-detect: check public/images first
        $publicPath = public_path('images');
        if (file_exists($publicPath) && is_writable(dirname($publicPath))) {
            return $publicPath;
        }

        // If public/images doesn't exist or not writable, check root/images
        $rootPath = base_path('images');
        if (file_exists($rootPath) || is_writable(base_path())) {
            return $rootPath;
        }

        // Default fallback to public/images
        return $publicPath;
    }
}

if (!function_exists('get_image_url_path')) {
    /**
     * Get the URL path for images
     * Returns relative path from public folder
     *
     * @param string $filepath
     * @return string
     */
    function get_image_url_path(string $filepath): string
    {
        // If images are stored in root (not public), return direct path
        $basePath = get_image_base_path();
        $publicPath = public_path('images');

        // If base path is not in public folder, images are in root
        if ($basePath !== $publicPath && !str_starts_with($basePath, $publicPath)) {
            // For root-level images, the URL is just /images/
            return 'images/' . ltrim($filepath, '/');
        }

        // For public/images, return the same
        return 'images/' . ltrim($filepath, '/');
    }
}

if (!function_exists('ensure_image_directory')) {
    /**
     * Ensure image directory exists with proper error handling
     *
     * @param string $subPath
     * @return array ['success' => bool, 'path' => string, 'error' => string|null]
     */
    function ensure_image_directory(string $subPath = ''): array
    {
        $basePath = get_image_base_path();
        $fullPath = $subPath ? $basePath . '/' . trim($subPath, '/') : $basePath;

        // Check if base directory exists
        if (!file_exists($basePath)) {
            // Try to create base directory
            $mkdirResult = @mkdir($basePath, 0755, true);
            if (!$mkdirResult) {
                $error = error_get_last();
                \Illuminate\Support\Facades\Log::error('Failed to create base image directory', [
                    'path' => $basePath,
                    'error' => $error
                ]);
                return [
                    'success' => false,
                    'path' => $fullPath,
                    'error' => 'Gagal membuat folder dasar: ' . ($error['message'] ?? 'Unknown error')
                ];
            }
        }

        // Check if subdirectory needs to be created
        if ($subPath && !file_exists($fullPath)) {
            $mkdirResult = @mkdir($fullPath, 0755, true);
            if (!$mkdirResult) {
                $error = error_get_last();
                \Illuminate\Support\Facades\Log::error('Failed to create image subdirectory', [
                    'path' => $fullPath,
                    'error' => $error
                ]);
                return [
                    'success' => false,
                    'path' => $fullPath,
                    'error' => 'Gagal membuat folder: ' . ($error['message'] ?? 'Unknown error')
                ];
            }
        }

        // Check writable
        if (!is_writable($fullPath)) {
            return [
                'success' => false,
                'path' => $fullPath,
                'error' => 'Folder tidak dapat ditulis (permission denied)'
            ];
        }

        return [
            'success' => true,
            'path' => $fullPath,
            'error' => null
        ];
    }
}
