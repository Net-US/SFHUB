<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::with(['user', 'categories', 'tags']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('category')) {
            $query->whereHas('categories', fn($q) => $q->where('slug', $request->get('category')));
        }

        $posts = $query->latest()->paginate($request->get('per_page', 20));
        $categories = BlogCategory::withCount('posts')->get();
        $tags = BlogTag::all();

        return view('admin.blog', compact('posts', 'categories', 'tags'));
    }

    public function create()
    {
        $categories = BlogCategory::all();
        return view('admin.add_post', compact('categories'));
    }

    public function edit(BlogPost $post)
    {
        $categories = BlogCategory::all();
        $post->load(['categories', 'tags']);
        return view('admin.edit_post', compact('categories', 'post'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Debug: Log all incoming data
            if (config('app.debug')) {
                Log::info('Blog store request data:', [
                    'all_data' => $request->all(),
                    'featured_image_length' => $request->featured_image ? strlen($request->featured_image) : 'null',
                    'featured_image_prefix' => $request->featured_image ? substr($request->featured_image, 0, 50) . '...' : 'null'
                ]);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:blog_posts',
                'content' => 'required|string',
                'excerpt' => 'nullable|string|max:500',
                'featured_image' => 'nullable|string', // Remove max limit, handle in upload logic
                'status' => 'required|in:draft,published,featured',
                'published_at' => 'nullable|date',
                'category_id' => 'nullable|exists:blog_categories,id',
                'tags' => 'nullable|string',
                'meta_title' => 'nullable|string|max:60',
                'meta_description' => 'nullable|string|max:160',
            ], [
                'featured_image.string' => 'Format gambar tidak valid.',
                'title.required' => 'Judul postingan wajib diisi.',
                'slug.required' => 'URL slug wajib diisi.',
                'slug.unique' => 'URL slug sudah digunakan.',
                'content.required' => 'Konten postingan wajib diisi.',
                'status.required' => 'Status postingan wajib dipilih.',
                'category_id.exists' => 'Kategori tidak ditemukan.',
            ]);

            // Handle empty content from Quill
            if (trim(strip_tags($validated['content'])) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal. Periksa kembali input Anda.',
                    'errors' => ['content' => 'Konten postingan wajib diisi.']
                ], 422);
            }

            $validated['user_id'] = auth()->id();

            if (empty($validated['published_at']) && $validated['status'] === 'published') {
                $validated['published_at'] = now();
            }

            // Handle featured image upload
            if (!empty($validated['featured_image'])) {
                $imageData = $validated['featured_image'];

                // Check if it's base64 data
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                    $imageType = $matches[1];
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                    $imageData = base64_decode($imageData);

                    if ($imageData === false) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal mendekode gambar. Pastikan format gambar valid.',
                            'errors' => ['featured_image' => 'Format gambar tidak valid.']
                        ], 422);
                    }

                    // Check image size (max 10MB)
                    if (strlen($imageData) > 10 * 1024 * 1024) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Ukuran gambar terlalu besar.',
                            'errors' => ['featured_image' => 'Ukuran gambar terlalu besar.']
                        ], 422);
                    }

                    // Validate image type
                    $allowedTypes = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
                    if (!in_array(strtolower($imageType), $allowedTypes)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Format gambar tidak didukung. Gunakan JPEG, PNG, GIF, atau WebP.',
                            'errors' => ['featured_image' => 'Format gambar tidak didukung.']
                        ], 422);
                    }

                    // Create directory if not exists
                    $uploadPath = public_path('images/blog');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    // Create filename and save
                    $filename = 'blog_' . time() . '_' . Str::random(10) . '.' . $imageType;
                    $fullPath = $uploadPath . '/' . $filename;

                    // Save file to public/images/blog
                    if (file_put_contents($fullPath, $imageData) === false) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal menyimpan gambar. Periksa permission folder.',
                            'errors' => ['featured_image' => 'Gagal menyimpan gambar.']
                        ], 500);
                    }

                    $validated['featured_image'] = 'images/blog/' . $filename;
                }
            }

            $post = BlogPost::create($validated);

            // Handle category
            if (!empty($validated['category_id'])) {
                $post->categories()->attach($validated['category_id']);
            }

            // Handle tags
            if (!empty($validated['tags'])) {
                $tagNames = array_map('trim', explode(',', $validated['tags']));
                foreach ($tagNames as $tagName) {
                    $tag = BlogTag::firstOrCreate(['name' => $tagName, 'slug' => Str::slug($tagName)]);
                    $post->tags()->attach($tag->id);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Postingan berhasil dibuat!',
                'post' => $post
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. Periksa kembali input Anda.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan postingan: ' . $e->getMessage(),
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show(BlogPost $post): JsonResponse
    {
        return response()->json([
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'content' => $post->content,
            'excerpt' => $post->excerpt,
            'featured_image' => $post->featured_image,
            'status' => $post->status,
            'views' => $post->views,
            'author' => $post->user->name,
            'published_at' => $post->published_at?->format('Y-m-d H:i:s'),
            'created_at' => $post->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $post->updated_at->format('Y-m-d H:i:s'),
            'categories' => $post->categories,
            'tags' => $post->tags,
            'category_ids' => $post->categories->pluck('id'),
            'tag_ids' => $post->tags->pluck('id'),
        ]);
    }

    public function update(Request $request, BlogPost $post): JsonResponse
    {
        try {
            // Debug: Log all incoming data
            if (config('app.debug')) {
                Log::info('Blog update request data:', [
                    'post_id' => $post->id,
                    'all_data' => $request->all(),
                    'featured_image_length' => $request->featured_image ? strlen($request->featured_image) : 'null',
                    'featured_image_prefix' => $request->featured_image ? substr($request->featured_image, 0, 50) . '...' : 'null'
                ]);
            }

            // Remove unique slug validation for updates (except if slug changed)
            $slugRule = 'required|string|max:255';
            if ($request->slug !== $post->slug) {
                $slugRule .= '|unique:blog_posts,slug,' . $post->id;
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => $slugRule,
                'content' => 'required|string',
                'excerpt' => 'nullable|string|max:500',
                'featured_image' => 'nullable|string', // Remove max limit, handle in upload logic
                'status' => 'required|in:draft,published,featured',
                'published_at' => 'nullable|date',
                'category_id' => 'nullable|exists:blog_categories,id',
                'tags' => 'nullable|string',
                'meta_title' => 'nullable|string|max:60',
                'meta_description' => 'nullable|string|max:160',
            ], [
                'featured_image.string' => 'Format gambar tidak valid.',
                'title.required' => 'Judul postingan wajib diisi.',
                'slug.required' => 'URL slug wajib diisi.',
                'slug.unique' => 'URL slug sudah digunakan.',
                'content.required' => 'Konten postingan wajib diisi.',
                'status.required' => 'Status postingan wajib dipilih.',
                'category_id.exists' => 'Kategori tidak ditemukan.',
            ]);

            // Handle empty content from Quill
            if (trim(strip_tags($validated['content'])) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal. Periksa kembali input Anda.',
                    'errors' => ['content' => 'Konten postingan wajib diisi.']
                ], 422);
            }

            if (empty($validated['published_at']) && $validated['status'] === 'published') {
                $validated['published_at'] = now();
            }

            // Handle featured image upload
            if (!empty($validated['featured_image'])) {
                $imageData = $validated['featured_image'];

                // Check if it's base64 data
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                    $imageType = $matches[1];
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                    $imageData = base64_decode($imageData);

                    if ($imageData === false) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal mendekode gambar. Pastikan format gambar valid.',
                            'errors' => ['featured_image' => 'Format gambar tidak valid.']
                        ], 422);
                    }

                    // Check image size (max 10MB)
                    if (strlen($imageData) > 10 * 1024 * 1024) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Ukuran gambar terlalu besar.',
                            'errors' => ['featured_image' => 'Ukuran gambar terlalu besar.']
                        ], 422);
                    }

                    // Validate image type
                    $allowedTypes = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
                    if (!in_array(strtolower($imageType), $allowedTypes)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Format gambar tidak didukung. Gunakan JPEG, PNG, GIF, atau WebP.',
                            'errors' => ['featured_image' => 'Format gambar tidak didukung.']
                        ], 422);
                    }

                    // Create directory if not exists
                    $uploadPath = public_path('images/blog');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    // Create filename and save
                    $filename = 'blog_' . time() . '_' . Str::random(10) . '.' . $imageType;
                    $fullPath = $uploadPath . '/' . $filename;

                    // Save file to public/images/blog
                    if (file_put_contents($fullPath, $imageData) === false) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal menyimpan gambar. Periksa permission folder.',
                            'errors' => ['featured_image' => 'Gagal menyimpan gambar.']
                        ], 500);
                    }

                    $validated['featured_image'] = 'images/blog/' . $filename;

                    // Delete old image if exists
                    if ($post->featured_image && file_exists(public_path($post->featured_image))) {
                        unlink(public_path($post->featured_image));
                    }
                }
            } else {
                // Keep old image if no new image uploaded
                $validated['featured_image'] = $post->featured_image;
            }

            $post->update($validated);

            // Handle category (sync instead of attach)
            if (!empty($validated['category_id'])) {
                $post->categories()->sync([$validated['category_id']]);
            } else {
                $post->categories()->detach();
            }

            // Handle tags (sync instead of attach)
            if (!empty($validated['tags'])) {
                $tagNames = array_map('trim', explode(',', $validated['tags']));
                $tagIds = [];
                foreach ($tagNames as $tagName) {
                    $tag = BlogTag::firstOrCreate(['name' => $tagName, 'slug' => Str::slug($tagName)]);
                    $tagIds[] = $tag->id;
                }
                $post->tags()->sync($tagIds);
            } else {
                $post->tags()->detach();
            }

            return response()->json([
                'success' => true,
                'message' => 'Postingan berhasil diperbarui!',
                'post' => $post
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. Periksa kembali input Anda.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui postingan: ' . $e->getMessage(),
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy(BlogPost $post): JsonResponse
    {
        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }

    public function getCategories(): JsonResponse
    {
        $categories = BlogCategory::withCount('posts')->get();

        return response()->json($categories);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = BlogCategory::create($validated);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    public function updateCategory(Request $request, BlogCategory $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }

    public function destroyCategory(BlogCategory $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }

    public function getTags(): JsonResponse
    {
        $tags = BlogTag::all();

        return response()->json($tags);
    }

    public function storeTag(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag = BlogTag::create($validated);

        return response()->json([
            'message' => 'Tag created successfully',
            'tag' => $tag,
        ], 201);
    }
}
