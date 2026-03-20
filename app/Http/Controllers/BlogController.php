<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::with(['user', 'categories', 'tags'])
            ->where('status', 'published')
            ->orderBy('published_at', 'desc');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $categorySlug = $request->get('category');
            $query->whereHas('categories', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Filter by tag
        if ($request->has('tag')) {
            $tagSlug = $request->get('tag');
            $query->whereHas('tags', function($q) use ($tagSlug) {
                $q->where('slug', $tagSlug);
            });
        }

        $posts = $query->paginate(6);
        $categories = BlogCategory::withCount('posts')->get();
        $featuredPosts = BlogPost::with(['user', 'categories'])
            ->where('status', 'published')
            ->where('featured_image', true)
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        return view('blog.index', compact('posts', 'categories', 'featuredPosts'));
    }

    public function show($slug)
    {
        $post = BlogPost::with(['user', 'categories', 'tags'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Increment view count
        $post->increment('views');

        // Get related posts
        $relatedPosts = BlogPost::with(['user', 'categories'])
            ->where('status', 'published')
            ->where('id', '!=', $post->id)
            ->whereHas('categories', function($q) use ($post) {
                $q->whereIn('blog_categories.id', $post->categories->pluck('id'));
            })
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        // Get previous and next posts
        $previousPost = BlogPost::where('status', 'published')
            ->where('published_at', '<', $post->published_at)
            ->orderBy('published_at', 'desc')
            ->first();

        $nextPost = BlogPost::where('status', 'published')
            ->where('published_at', '>', $post->published_at)
            ->orderBy('published_at', 'asc')
            ->first();

        return view('blog.show', compact('post', 'relatedPosts', 'previousPost', 'nextPost'));
    }
}
