<?php

namespace App\Http\Controllers;

use App\Models\ProductScanHistory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductScanHistoryController extends Controller
{
    /**
     * Get scan history for a specific EAN code.
     */
    public function getByEan(Request $request)
    {
        $ean = $request->query('ean');
        
        if (!$ean) {
            return response()->json([
                'success' => false,
                'message' => 'Kod EAN jest wymagany'
            ], 400);
        }

        // Find product by EAN
        $product = Product::where('ean_code', $ean)->first();
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produkt z tym kodem EAN nie istnieje'
            ], 404);
        }

        // Get scan history
        $history = ProductScanHistory::where('product_id', $product->id)
            ->with(['scanner'])
            ->orderBy('scanned_at', 'desc')
            ->limit(50) // Last 50 scans
            ->get()
            ->map(function ($scan) {
                return [
                    'id' => $scan->id,
                    'scanned_at' => $scan->scanned_at->format('Y-m-d H:i:s'),
                    'scanned_at_human' => $scan->scanned_at->diffForHumans(),
                    'scanner' => [
                        'id' => $scan->scanner->id,
                        'name' => $scan->scanner->name,
                    ],
                    'location' => $scan->location,
                    'notes' => $scan->notes,
                ];
            });

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'ean_code' => $product->ean_code,
                'image_url' => $product->image_url,
                'category' => [
                    'name' => $product->category->name,
                    'icon' => $product->category->icon,
                ],
            ],
            'history' => $history,
            'total_scans' => $history->count(),
        ]);
    }

    /**
     * Record a new scan.
     */
    public function recordScan(Request $request)
    {
        $user = $request->attributes->get('auth_user');

        $validator = Validator::make($request->all(), [
            'ean_code' => 'required|string|min:8|max:13',
            'location' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find product
        $product = Product::where('ean_code', $request->ean_code)->first();
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produkt nie został znaleziony'
            ], 404);
        }

        // Record scan
        $scan = ProductScanHistory::create([
            'product_id' => $product->id,
            'scanned_by' => $user->id,
            'ean_code' => $request->ean_code,
            'scanned_at' => now(),
            'location' => $request->location ?? 'pantry',
            'notes' => $request->notes,
        ]);

        $scan->load('scanner');

        return response()->json([
            'success' => true,
            'scan' => [
                'id' => $scan->id,
                'scanned_at' => $scan->scanned_at->format('Y-m-d H:i:s'),
                'scanner' => [
                    'name' => $scan->scanner->name,
                ],
            ],
            'message' => 'Skanowanie zapisane'
        ], 201);
    }
}
