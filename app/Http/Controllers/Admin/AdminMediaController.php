<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class AdminMediaController extends Controller
{
    /**
     * Display media manager page
     */
    public function index(Request $request): Response
    {
        $type = $request->get('type', 'all');
        $folder = $request->get('folder', '/');
        $search = $request->get('search');

        $query = Media::with('user')
            ->when($type !== 'all', fn($q) => $q->byType($type))
            ->when($folder !== '/', fn($q) => $q->byFolder($folder))
            ->when($search, fn($q) => $q->where('filename', 'like', "%{$search}%"))
            ->orderByDesc('created_at');

        $media = $query->paginate(24);
        $folders = Media::select('folder')->distinct()->pluck('folder');
        $stats = [
            'total' => Media::count(),
            'images' => Media::byType('image')->count(),
            'videos' => Media::byType('video')->count(),
            'documents' => Media::byType('document')->count(),
            'total_size' => Media::sum('size'),
        ];

        return response()->view('admin.media', compact('media', 'folders', 'stats', 'type', 'folder'));
    }

    /**
     * Get media files as JSON (for AJAX requests)
     */
    public function getMedia(Request $request): JsonResponse
    {
        $type = $request->get('type', 'all');
        $folder = $request->get('folder', '/');
        $perPage = $request->get('per_page', 24);

        $query = Media::with('user')
            ->when($type !== 'all', fn($q) => $q->byType($type))
            ->when($folder !== '/', fn($q) => $q->byFolder($folder))
            ->orderByDesc('created_at');

        $media = $query->paginate($perPage);

        return response()->json([
            'data' => $media->map(fn($item) => [
                'id' => $item->id,
                'filename' => $item->filename,
                'original_name' => $item->original_name,
                'url' => $item->getUrl(),
                'thumbnail' => $item->type === 'image' ? $item->getUrl() : null,
                'type' => $item->type,
                'mime_type' => $item->mime_type,
                'size' => $item->getSizeForHumans(),
                'size_bytes' => $item->size,
                'extension' => $item->extension,
                'folder' => $item->folder,
                'alt_text' => $item->alt_text,
                'description' => $item->description,
                'is_public' => $item->is_public,
                'uploaded_by' => $item->user?->name,
                'created_at' => $item->created_at->toISOString(),
            ]),
            'pagination' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
            ],
        ]);
    }

    /**
     * Upload new media file
     */
    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
            'folder' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $folder = $validated['folder'] ?? 'uploads';
        $folder = trim($folder, '/');

        // Generate unique filename
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filepath = $folder . '/' . $filename;

        // Store file
        $path = $file->storeAs($folder, $filename, 'public');

        if (!$path) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file',
            ], 500);
        }

        // Determine file type
        $mimeType = $file->getMimeType();
        $type = $this->getFileType($mimeType);

        // Create media record
        $media = Media::create([
            'user_id' => auth()->id(),
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'filepath' => $filepath,
            'mime_type' => $mimeType,
            'size' => $file->getSize(),
            'extension' => strtolower($file->getClientOriginalExtension()),
            'type' => $type,
            'alt_text' => $validated['alt_text'] ?? null,
            'description' => $validated['description'] ?? null,
            'folder' => '/' . $folder,
            'is_public' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'media' => [
                'id' => $media->id,
                'filename' => $media->filename,
                'url' => $media->getUrl(),
                'type' => $media->type,
                'size' => $media->getSizeForHumans(),
            ],
        ]);
    }

    /**
     * Update media metadata
     */
    public function update(Request $request, Media $media): JsonResponse
    {
        $validated = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $media->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Media updated successfully',
            'media' => $media,
        ]);
    }

    /**
     * Delete media file
     */
    public function destroy(Media $media): JsonResponse
    {
        // Delete file from storage
        if (Storage::disk('public')->exists($media->filepath)) {
            Storage::disk('public')->delete($media->filepath);
        }

        // Delete record
        $media->delete();

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully',
        ]);
    }

    /**
     * Bulk delete media files
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:media,id',
        ]);

        $mediaItems = Media::whereIn('id', $validated['ids'])->get();

        foreach ($mediaItems as $media) {
            if (Storage::disk('public')->exists($media->filepath)) {
                Storage::disk('public')->delete($media->filepath);
            }
            $media->delete();
        }

        return response()->json([
            'success' => true,
            'message' => $mediaItems->count() . ' files deleted successfully',
        ]);
    }

    /**
     * Create new folder
     */
    public function createFolder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'folder_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9_-]+$/',
            'parent_folder' => 'nullable|string|max:255',
        ]);

        $parent = trim($validated['parent_folder'] ?? '', '/');
        $newFolder = $validated['folder_name'];
        $fullPath = $parent ? $parent . '/' . $newFolder : $newFolder;

        // Create folder in storage
        Storage::disk('public')->makeDirectory($fullPath);

        return response()->json([
            'success' => true,
            'message' => 'Folder created successfully',
            'folder' => '/' . $fullPath,
        ]);
    }

    /**
     * Get folder list
     */
    public function getFolders(): JsonResponse
    {
        $folders = Media::select('folder')
            ->distinct()
            ->pluck('folder')
            ->map(fn($folder) => [
                'path' => $folder,
                'name' => basename($folder) ?: 'Root',
                'count' => Media::where('folder', $folder)->count(),
            ]);

        return response()->json($folders);
    }

    /**
     * Get media statistics
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'total_files' => Media::count(),
            'total_size' => Media::sum('size'),
            'by_type' => [
                'images' => Media::byType('image')->count(),
                'videos' => Media::byType('video')->count(),
                'audio' => Media::byType('audio')->count(),
                'documents' => Media::byType('document')->count(),
                'archives' => Media::byType('archive')->count(),
                'other' => Media::byType('other')->count(),
            ],
            'recent_uploads' => Media::orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn($m) => [
                    'id' => $m->id,
                    'filename' => $m->filename,
                    'url' => $m->getUrl(),
                    'type' => $m->type,
                    'size' => $m->getSizeForHumans(),
                    'uploaded_at' => $m->created_at->diffForHumans(),
                ]),
        ];

        return response()->json($stats);
    }

    /**
     * Determine file type from mime type
     */
    private function getFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }
        if (str_starts_with($mimeType, 'application/pdf') ||
            str_starts_with($mimeType, 'application/msword') ||
            str_starts_with($mimeType, 'application/vnd.openxmlformats-officedocument') ||
            str_starts_with($mimeType, 'text/')) {
            return 'document';
        }
        if (str_starts_with($mimeType, 'application/zip') ||
            str_starts_with($mimeType, 'application/x-rar') ||
            str_starts_with($mimeType, 'application/x-7z-compressed')) {
            return 'archive';
        }
        return 'other';
    }
}
