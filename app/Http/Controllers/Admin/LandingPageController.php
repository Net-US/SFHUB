<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingFeature;
use App\Models\LandingHero;
use App\Models\LandingStat;
use App\Models\LandingTestimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            'heroes' => 'nullable|array',
            'features' => 'nullable|array',
            'testimonials' => 'nullable|array',
            'stats' => 'nullable|array',
        ]);

        // Update heroes
        if (isset($validated['heroes'])) {
            foreach ($validated['heroes'] as $hero) {
                if (isset($hero['id'])) {
                    LandingHero::where('id', $hero['id'])->update($hero);
                }
            }
        }

        // Update features
        if (isset($validated['features'])) {
            foreach ($validated['features'] as $feature) {
                if (isset($feature['id'])) {
                    LandingFeature::where('id', $feature['id'])->update($feature);
                }
            }
        }

        // Update testimonials
        if (isset($validated['testimonials'])) {
            foreach ($validated['testimonials'] as $testimonial) {
                if (isset($testimonial['id'])) {
                    LandingTestimonial::where('id', $testimonial['id'])->update($testimonial);
                }
            }
        }

        // Update stats
        if (isset($validated['stats'])) {
            foreach ($validated['stats'] as $stat) {
                if (isset($stat['id'])) {
                    LandingStat::where('id', $stat['id'])->update($stat);
                }
            }
        }

        return response()->json([
            'message' => 'Landing page content saved successfully',
        ]);
    }
}
