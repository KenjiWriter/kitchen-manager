<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Get all products with optional filters.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'creator']);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by subcategory
        if ($request->has('subcategory')) {
            $query->where('subcategory', $request->subcategory);
        }

        // Search by name or EAN
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('ean_code', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'ean_code' => $product->ean_code,
                    'subcategory' => $product->subcategory,
                    'image_url' => $product->image_url,
                    'description' => $product->description,
                    'category' => [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                        'icon' => $product->category->icon,
                        'color' => $product->category->color,
                    ],
                    'created_by' => [
                        'id' => $product->creator->id,
                        'name' => $product->creator->name,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'products' => $products,
        ]);
    }

    /**
     * Get single product.
     */
    public function show($id)
    {
        $product = Product::with(['category', 'creator'])->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produkt nie został znaleziony'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'ean_code' => $product->ean_code,
                'subcategory' => $product->subcategory,
                'image_url' => $product->image_url,
                'description' => $product->description,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'icon' => $product->category->icon,
                    'color' => $product->category->color,
                ],
                'created_by' => [
                    'id' => $product->creator->id,
                    'name' => $product->creator->name,
                ],
                'created_at' => $product->created_at,
            ]
        ]);
    }

    /**
     * Create a new product.
     */
    public function store(Request $request)
    {
        $user = $request->attributes->get('auth_user');

        \Log::info('Product creation attempt', [
            'user_id' => $user?->id,
            'request_data' => $request->except(['image']),
            'has_image' => $request->hasFile('image')
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'ean_code' => 'nullable|string|min:8|max:13',
            'category_id' => 'required|exists:product_categories,id',
            'subcategory' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            \Log::error('Product validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate EAN uniqueness only if provided
        if ($request->ean_code) {
            $existingProduct = Product::where('ean_code', $request->ean_code)->first();
            if ($existingProduct) {
                \Log::warning('Duplicate EAN code attempted', [
                    'ean_code' => $request->ean_code,
                    'existing_product_id' => $existingProduct->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Produkt z tym kodem EAN już istnieje',
                    'errors' => ['ean_code' => ['Ten kod EAN jest już używany']]
                ], 422);
            }
        }

        try {
            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products', $fileName, 'public');
                \Log::info('Product image uploaded', ['path' => $imagePath]);
            }

            $product = Product::create([
                'name' => $request->name,
                'ean_code' => $request->ean_code,
                'category_id' => $request->category_id,
                'subcategory' => $request->subcategory,
                'description' => $request->description,
                'image_path' => $imagePath,
                'created_by' => $user->id,
            ]);

            $product->load(['category', 'creator']);

            \Log::info('Product created successfully', [
                'product_id' => $product->id,
                'name' => $product->name
            ]);

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'ean_code' => $product->ean_code,
                    'subcategory' => $product->subcategory,
                    'image_url' => $product->image_url,
                    'category' => [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ],
                ],
                'message' => 'Produkt został dodany'
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Product creation exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Wystąpił błąd podczas tworzenia produktu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a product.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produkt nie został znaleziony'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'ean_code' => 'nullable|string|min:8|max:13',
            'category_id' => 'exists:product_categories,id',
            'subcategory' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate EAN uniqueness only if provided and changed
        if ($request->has('ean_code') && $request->ean_code) {
            $existingProduct = Product::where('ean_code', $request->ean_code)
                ->where('id', '!=', $id)
                ->first();
            if ($existingProduct) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produkt z tym kodem EAN już istnieje',
                    'errors' => ['ean_code' => ['Ten kod EAN jest już używany']]
                ], 422);
            }
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            $product->deleteImage();

            // Upload new image
            $image = $request->file('image');
            $fileName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('products', $fileName, 'public');
            $product->image_path = $imagePath;
        }

        // Update other fields
        $product->update($request->only(['name', 'ean_code', 'category_id', 'subcategory', 'description']));

        $product->load(['category', 'creator']);

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'ean_code' => $product->ean_code,
                'image_url' => $product->image_url,
            ],
            'message' => 'Produkt został zaktualizowany'
        ]);
    }

    /**
     * Delete a product.
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produkt nie został znaleziony'
            ], 404);
        }

        // Delete image if exists
        $product->deleteImage();

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produkt został usunięty'
        ]);
    }

    /**
     * Search products by EAN code.
     */
    public function searchByEan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ean' => 'required|string|max:13',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::with(['category', 'creator'])
            ->where('ean_code', $request->ean)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produkt o podanym kodzie EAN nie został znaleziony',
                'found' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'found' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'ean_code' => $product->ean_code,
                'subcategory' => $product->subcategory,
                'image_url' => $product->image_url,
                'description' => $product->description,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'icon' => $product->category->icon,
                    'color' => $product->category->color,
                ],
            ]
        ]);
    }
}
