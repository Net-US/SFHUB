<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $categories = FaqCategory::with(['faqs'])->get();
        $faqs = Faq::with('category')->paginate(20);
        $totalFaqs = Faq::count();
        $publishedFaqs = Faq::where('is_active', true)->count();
        $totalViews = 0; // Default to 0 since view_count column doesn't exist

        return view('admin.faq', compact('categories', 'faqs', 'totalFaqs', 'publishedFaqs', 'totalViews'));
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = FaqCategory::create($validated);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    public function updateCategory(Request $request, FaqCategory $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }

    public function destroyCategory(FaqCategory $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'faq_category_id' => 'nullable|exists:faq_categories,id',
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
        ]);

        $faq = Faq::create($validated);

        return response()->json([
            'message' => 'FAQ created successfully',
            'faq' => $faq->load('category'),
        ], 201);
    }

    public function show(Faq $faq): JsonResponse
    {
        return response()->json([
            'id' => $faq->id,
            'question' => $faq->question,
            'answer' => $faq->answer,
            'is_active' => $faq->is_active,
            'category' => $faq->category,
            'faq_category_id' => $faq->faq_category_id,
        ]);
    }

    public function update(Request $request, Faq $faq): JsonResponse
    {
        $validated = $request->validate([
            'faq_category_id' => 'nullable|exists:faq_categories,id',
            'question' => 'sometimes|string|max:500',
            'answer' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $faq->update($validated);

        return response()->json([
            'message' => 'FAQ updated successfully',
            'faq' => $faq->fresh('category'),
        ]);
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $faq->delete();

        return response()->json([
            'message' => 'FAQ deleted successfully',
        ]);
    }
}
