<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalSeo;
use App\Models\MetaTag;
use App\Models\SeoSetting;
use App\Models\SitemapSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SeoController extends Controller
{
    /**
     * Display SEO settings page
     */
    public function index()
    {
        $globalSeo = GlobalSeo::first();
        $pages = SeoSetting::all();
        $metaTags = MetaTag::all();
        $sitemap = SitemapSetting::first();

        return view('admin.seo', compact('globalSeo', 'pages', 'metaTags', 'sitemap'));
    }

    public function getGlobalSettings(): JsonResponse
    {
        $globalSeo = GlobalSeo::first();

        return response()->json([
            'default_title' => $globalSeo?->default_title ?? config('app.name'),
            'default_description' => $globalSeo?->default_description ?? '',
            'default_keywords' => $globalSeo?->default_keywords ?? '',
            'author' => $globalSeo?->author ?? 'SFHUB Team',
            'robots' => $globalSeo?->robots ?? 'index, follow',
            'google_analytics_id' => $globalSeo?->google_analytics_id ?? '',
            'facebook_pixel_id' => $globalSeo?->facebook_pixel_id ?? '',
            'analytics_active' => $globalSeo?->analytics_active ?? false,
        ]);
    }

    public function updateGlobalSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'default_title' => 'required|string|max:255',
            'default_description' => 'required|string',
            'default_keywords' => 'nullable|string',
            'author' => 'nullable|string|max:255',
            'robots' => 'nullable|string|max:255',
            'google_analytics_id' => 'nullable|string|max:255',
            'facebook_pixel_id' => 'nullable|string|max:255',
            'analytics_active' => 'boolean',
        ]);

        GlobalSeo::updateOrCreate(['id' => 1], $validated);

        return response()->json([
            'message' => 'Global SEO settings updated successfully',
        ]);
    }

    public function getPageSettings(): JsonResponse
    {
        $pages = SeoSetting::all()->map(fn($page) => [
            'id' => $page->id,
            'page' => $page->page,
            'title' => $page->title,
            'description' => $page->description,
            'keywords' => $page->keywords,
            'canonical_url' => $page->canonical_url,
            'og_title' => $page->og_title,
            'og_description' => $page->og_description,
            'og_image' => $page->og_image,
            'priority' => $page->priority,
            'change_freq' => $page->change_freq,
        ]);

        return response()->json($pages);
    }

    public function updatePageSettings(Request $request, SeoSetting $seoSetting): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'keywords' => 'nullable|string',
            'canonical_url' => 'nullable|url',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string',
            'og_image' => 'nullable|string',
            'priority' => 'required|numeric|min:0|max:1',
            'change_freq' => 'required|in:always,hourly,daily,weekly,monthly,yearly,never',
        ]);

        $seoSetting->update($validated);

        return response()->json([
            'message' => 'Page SEO settings updated successfully',
            'page' => $seoSetting,
        ]);
    }

    public function getMetaTags(): JsonResponse
    {
        $tags = MetaTag::active()->get();

        return response()->json($tags);
    }

    public function storeMetaTag(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:name,property,http-equiv',
        ]);

        $tag = MetaTag::create($validated);

        return response()->json([
            'message' => 'Meta tag created successfully',
            'tag' => $tag,
        ], 201);
    }

    public function updateMetaTag(Request $request, MetaTag $metaTag): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'type' => 'sometimes|in:name,property,http-equiv',
            'is_active' => 'sometimes|boolean',
        ]);

        $metaTag->update($validated);

        return response()->json([
            'message' => 'Meta tag updated successfully',
            'tag' => $metaTag,
        ]);
    }

    public function destroyMetaTag(MetaTag $metaTag): JsonResponse
    {
        $metaTag->delete();

        return response()->json([
            'message' => 'Meta tag deleted successfully',
        ]);
    }

    public function getSitemapSettings(): JsonResponse
    {
        $sitemap = SitemapSetting::first();

        return response()->json([
            'last_generated' => $sitemap?->last_generated?->format('Y-m-d H:i:s'),
            'url_count' => $sitemap?->url_count ?? 0,
            'auto_generate' => $sitemap?->auto_generate ?? true,
            'sitemap_path' => $sitemap?->sitemap_path ?? 'sitemap.xml',
        ]);
    }

    public function updateSitemapSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'auto_generate' => 'required|boolean',
            'sitemap_path' => 'required|string|max:255',
        ]);

        SitemapSetting::updateOrCreate(['id' => 1], $validated);

        return response()->json([
            'message' => 'Sitemap settings updated successfully',
        ]);
    }

    public function generateSitemap(): JsonResponse
    {
        // Logic to generate sitemap
        $pages = SeoSetting::all();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($pages as $page) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . url($page->canonical_url ?? '/' . $page->page) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . now()->format('Y-m-d') . '</lastmod>' . PHP_EOL;
            $xml .= '    <changefreq>' . $page->change_freq . '</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . $page->priority . '</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        $sitemapPath = public_path('sitemap.xml');
        file_put_contents($sitemapPath, $xml);

        SitemapSetting::updateOrCreate(['id' => 1], [
            'last_generated' => now(),
            'url_count' => $pages->count(),
        ]);

        return response()->json([
            'message' => 'Sitemap generated successfully',
            'url_count' => $pages->count(),
        ]);
    }

    public function downloadSitemap()
    {
        $path = public_path('sitemap.xml');

        if (!file_exists($path)) {
            return response()->json(['message' => 'Sitemap not found. Please generate first.'], 404);
        }

        return response()->download($path, 'sitemap.xml');
    }
}
