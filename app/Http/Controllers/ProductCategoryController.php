<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductCategoryController extends Controller
{
    /**
     * Get all categories.
     */
    public function index()
    {
        $categories = ProductCategory::withCount('products')
            ->orderBy('order')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'icon' => $category->icon,
                    'color' => $category->color,
                    'order' => $category->order,
                    'products_count' => $category->products_count,
                    'subcategories' => $category->getSubcategories(),
                ];
            });

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    /**
     * Create a new category.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:product_categories,name',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $category = ProductCategory::create([
            'name' => $request->name,
            'icon' => $request->icon ?? '📦',
            'color' => $request->color ?? '#3B82F6',
            'order' => $request->order ?? ProductCategory::max('order') + 1,
        ]);

        return response()->json([
            'success' => true,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->icon,
                'color' => $category->color,
                'order' => $category->order,
            ],
            'message' => 'Kategoria została utworzona'
        ], 201);
    }

    /**
     * Update a category.
     */
    public function update(Request $request, $id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategoria nie została znaleziona'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255|unique:product_categories,name,' . $id,
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($request->only(['name', 'icon', 'color', 'order']));

        return response()->json([
            'success' => true,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->icon,
                'color' => $category->color,
                'order' => $category->order,
            ],
            'message' => 'Kategoria została zaktualizowana'
        ]);
    }

    /**
     * Delete a category.
     */
    public function destroy($id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategoria nie została znaleziona'
            ], 404);
        }

        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Nie można usunąć kategorii zawierającej produkty'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategoria została usunięta'
        ]);
    }
}
