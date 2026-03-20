<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:blog_categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:blog_tags,id',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']);

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = BlogPost::create($validated);

        if (!empty($validated['category_ids'])) {
            $post->categories()->attach($validated['category_ids']);
        }

        if (!empty($validated['tag_ids'])) {
            $post->tags()->attach($validated['tag_ids']);
        }

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post->load(['categories', 'tags']),
        ], 201);
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
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string',
            'status' => 'sometimes|in:draft,published,archived',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:blog_categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:blog_tags,id',
        ]);

        if (isset($validated['title']) && $validated['title'] !== $post->title) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        if (isset($validated['status']) && $validated['status'] === 'published' && !$post->published_at) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        if (isset($validated['category_ids'])) {
            $post->categories()->sync($validated['category_ids']);
        }

        if (isset($validated['tag_ids'])) {
            $post->tags()->sync($validated['tag_ids']);
        }

        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post->fresh(['categories', 'tags']),
        ]);
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
