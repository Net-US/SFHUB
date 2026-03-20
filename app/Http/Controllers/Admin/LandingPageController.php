<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingFeature;
use App\Models\LandingHero;
use App\Models\LandingStat;
use App\Models\LandingTestimonial;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandingPageController extends Controller
{
    /**
     * Display landing page editor
     */
    public function index()
    {
        $heroes = LandingHero::active()->get();
        $features = LandingFeature::active()->get();
        $testimonials = LandingTestimonial::active()->get();
        $stats = LandingStat::active()->get();

        return view('admin.landing', compact('heroes', 'features', 'testimonials', 'stats'));
    }

    // Hero Section
    public function getHeroes(): JsonResponse
    {
        $heroes = LandingHero::active()->get();

        return response()->json([
            'heroes' => $heroes,
            'active_count' => $heroes->count(),
        ]);
    }

    public function storeHero(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'cta_text' => 'nullable|string|max:100',
            'cta_link' => 'nullable|string|max:255',
            'hero_image' => 'nullable|string',
        ]);

        $validated['is_active'] = true;

        $hero = LandingHero::create($validated);

        return response()->json([
            'message' => 'Hero section created successfully',
            'hero' => $hero,
        ], 201);
    }

    public function updateHero(Request $request, LandingHero $hero): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'subtitle' => 'nullable|string',
            'cta_text' => 'nullable|string|max:100',
            'cta_link' => 'nullable|string|max:255',
            'hero_image' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $hero->update($validated);

        return response()->json([
            'message' => 'Hero section updated successfully',
            'hero' => $hero,
        ]);
    }

    public function destroyHero(LandingHero $hero): JsonResponse
    {
        $hero->delete();

        return response()->json([
            'message' => 'Hero section deleted successfully',
        ]);
    }

    // Features Section
    public function getFeatures(): JsonResponse
    {
        $features = LandingFeature::active()->get();

        return response()->json([
            'features' => $features,
            'active_count' => $features->count(),
        ]);
    }

    public function storeFeature(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
        ]);

        $validated['is_active'] = true;

        $feature = LandingFeature::create($validated);

        return response()->json([
            'message' => 'Feature created successfully',
            'feature' => $feature,
        ], 201);
    }

    public function updateFeature(Request $request, LandingFeature $feature): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        $feature->update($validated);

        return response()->json([
            'message' => 'Feature updated successfully',
            'feature' => $feature,
        ]);
    }

    public function destroyFeature(LandingFeature $feature): JsonResponse
    {
        $feature->delete();

        return response()->json([
            'message' => 'Feature deleted successfully',
        ]);
    }

    // Testimonials Section
    public function getTestimonials(): JsonResponse
    {
        $testimonials = LandingTestimonial::active()->get();

        return response()->json([
            'testimonials' => $testimonials,
            'active_count' => $testimonials->count(),
        ]);
    }

    public function storeTestimonial(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'content' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'avatar' => 'nullable|string',
        ]);

        $validated['is_active'] = true;
        $validated['rating'] = $validated['rating'] ?? 5;

        $testimonial = LandingTestimonial::create($validated);

        return response()->json([
            'message' => 'Testimonial created successfully',
            'testimonial' => $testimonial,
        ], 201);
    }

    public function updateTestimonial(Request $request, LandingTestimonial $testimonial): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'role' => 'nullable|string|max:255',
            'content' => 'sometimes|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'avatar' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $testimonial->update($validated);

        return response()->json([
            'message' => 'Testimonial updated successfully',
            'testimonial' => $testimonial,
        ]);
    }

    public function destroyTestimonial(LandingTestimonial $testimonial): JsonResponse
    {
        $testimonial->delete();

        return response()->json([
            'message' => 'Testimonial deleted successfully',
        ]);
    }

    // Stats Section
    public function getStats(): JsonResponse
    {
        $stats = LandingStat::active()->get();

        return response()->json([
            'stats' => $stats,
            'active_count' => $stats->count(),
        ]);
    }

    public function storeStat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
        ]);

        $validated['is_active'] = true;

        $stat = LandingStat::create($validated);

        return response()->json([
            'message' => 'Stat created successfully',
            'stat' => $stat,
        ], 201);
    }

    public function updateStat(Request $request, LandingStat $stat): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'sometimes|string|max:255',
            'value' => 'sometimes|string|max:100',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        $stat->update($validated);

        return response()->json([
            'message' => 'Stat updated successfully',
            'stat' => $stat,
        ]);
    }

    public function destroyStat(LandingStat $stat): JsonResponse
    {
        $stat->delete();

        return response()->json([
            'message' => 'Stat deleted successfully',
        ]);
    }

    // Get all landing page content
    public function getAllContent(): JsonResponse
    {
        return response()->json([
            'heroes' => LandingHero::active()->get(),
            'features' => LandingFeature::active()->get(),
            'testimonials' => LandingTestimonial::active()->get(),
            'stats' => LandingStat::active()->get(),
        ]);
    }

    // Save all landing page changes
    public function saveAll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hero' => 'nullable|array',
            'features' => 'nullable|array',
            'testimonials' => 'nullable|array',
            'stats' => 'nullable|array',
        ]);

        // Update or create hero
        if (isset($validated['hero'])) {
            $heroData = $validated['hero'];

            // Only save if hero has required data
            if (!empty($heroData['title'])) {
                if (isset($heroData['id']) && $heroData['id']) {
                    // Update existing hero
                    $hero = LandingHero::find($heroData['id']);
                    if ($hero) {
                        $hero->update($heroData);
                    }
                } else {
                    // Create new hero
                    $heroData['is_active'] = true;
                    LandingHero::create($heroData);
                }
            }
        }

        // Update or create features
        if (isset($validated['features'])) {
            foreach ($validated['features'] as $feature) {
                // Only save if feature has required data
                if (!empty($feature['title']) && !empty($feature['description'])) {
                    if (isset($feature['id']) && $feature['id'] && !str_starts_with($feature['id'], 'new_')) {
                        // Update existing
                        $item = LandingFeature::find($feature['id']);
                        if ($item) {
                            $item->update($feature);
                        }
                    } else {
                        // Create new
                        $feature['is_active'] = true;
                        LandingFeature::create($feature);
                    }
                }
            }
        }

        // Update or create testimonials
        if (isset($validated['testimonials'])) {
            foreach ($validated['testimonials'] as $testimonial) {
                // Only save if testimonial has required data
                if (!empty($testimonial['name']) && !empty($testimonial['content'])) {
                    if (isset($testimonial['id']) && $testimonial['id'] && !str_starts_with($testimonial['id'], 'new_')) {
                        $item = LandingTestimonial::find($testimonial['id']);
                        if ($item) {
                            $item->update($testimonial);
                        }
                    } else {
                        $testimonial['is_active'] = true;
                        $testimonial['rating'] = $testimonial['rating'] ?? 5;
                        LandingTestimonial::create($testimonial);
                    }
                }
            }
        }

        // Update or create stats
        if (isset($validated['stats'])) {
            foreach ($validated['stats'] as $stat) {
                // Only save if stat has required data
                if (!empty($stat['label']) && !empty($stat['value'])) {
                    if (isset($stat['id']) && $stat['id'] && !str_starts_with($stat['id'], 'new_')) {
                        $item = LandingStat::find($stat['id']);
                        if ($item) {
                            $item->update($stat);
                        }
                    } else {
                        $stat['is_active'] = true;
                        LandingStat::create($stat);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Landing page content saved successfully',
        ]);
    }

    // Upload hero image
    public function uploadHeroImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            // Ensure storage directory exists
            $storagePath = storage_path('app/public/landing');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $file = $request->file('image');
            $filename = 'hero_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('landing', $filename, 'public');

            if ($path) {
                return response()->json([
                    'success' => true,
                    'url' => Storage::url($path),
                    'path' => $path,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store image',
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file provided',
        ], 400);
    }

    // Upload logo
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Ensure storage directory exists
            $storagePath = storage_path('app/public/site');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $file = $request->file('image');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('site', $filename, 'public');

            if ($path) {
                // Save to site settings
                SiteSetting::setValue('site_logo', Storage::url($path), 'image');

                return response()->json([
                    'success' => true,
                    'url' => Storage::url($path),
                    'path' => $path,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store logo',
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file provided',
        ], 400);
    }

    // Upload favicon
    public function uploadFavicon(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,ico|max:1024',
        ]);

        if ($request->hasFile('image')) {
            // Ensure storage directory exists
            $storagePath = storage_path('app/public/site');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $file = $request->file('image');
            $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('site', $filename, 'public');

            if ($path) {
                // Save to site settings
                SiteSetting::setValue('site_favicon', Storage::url($path), 'image');

                return response()->json([
                    'success' => true,
                    'url' => Storage::url($path),
                    'path' => $path,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store favicon',
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file provided',
        ], 400);
    }
}
