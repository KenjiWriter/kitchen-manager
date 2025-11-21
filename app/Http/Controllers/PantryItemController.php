<?php

namespace App\Http\Controllers;

use App\Models\PantryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PantryItemController extends Controller
{
    /**
     * Get all pantry items with optional filters.
     */
    public function index(Request $request)
    {
        $user = $request->attributes->get('auth_user');
        
        $query = PantryItem::with(['product.category', 'addedBy', 'userGroup'])
            ->active();

        // Filter by user group
        if ($request->has('group_id')) {
            $query->where('user_group_id', $request->group_id);
        }

        // Filter by location
        if ($request->has('location')) {
            $query->where('location', $request->location);
        }

        // Filter by expiry status
        if ($request->has('expiry_status')) {
            switch ($request->expiry_status) {
                case 'expiring_soon':
                    $query->expiringSoon();
                    break;
                case 'expired':
                    $query->expired();
                    break;
            }
        }

        // Filter by product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Search by product name
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        $items = $query->orderBy('expiry_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'location' => $item->location,
                    'notes' => $item->notes,
                    'expiry_date' => $item->expiry_date?->format('Y-m-d'),
                    'days_until_expiry' => $item->daysUntilExpiry(),
                    'is_expired' => $item->isExpired(),
                    'is_expiring_soon' => $item->isExpiringSoon(),
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'image_url' => $item->product->image_url,
                        'ean_code' => $item->product->ean_code,
                        'category' => [
                            'id' => $item->product->category->id,
                            'name' => $item->product->category->name,
                            'icon' => $item->product->category->icon,
                            'color' => $item->product->category->color,
                        ],
                    ],
                    'added_by' => [
                        'id' => $item->addedBy->id,
                        'name' => $item->addedBy->name,
                    ],
                    'group' => $item->userGroup ? [
                        'id' => $item->userGroup->id,
                        'name' => $item->userGroup->name,
                    ] : null,
                    'created_at' => $item->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    /**
     * Get single pantry item.
     */
    public function show($id)
    {
        $item = PantryItem::with(['product.category', 'addedBy', 'userGroup'])->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Element nie został znaleziony'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'location' => $item->location,
                'notes' => $item->notes,
                'expiry_date' => $item->expiry_date?->format('Y-m-d'),
                'days_until_expiry' => $item->daysUntilExpiry(),
                'is_expired' => $item->isExpired(),
                'is_expiring_soon' => $item->isExpiringSoon(),
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'image_url' => $item->product->image_url,
                ],
                'added_by' => [
                    'id' => $item->addedBy->id,
                    'name' => $item->addedBy->name,
                ],
                'created_at' => $item->created_at,
            ]
        ]);
    }

    /**
     * Add item to pantry.
     */
    public function store(Request $request)
    {
        $user = $request->attributes->get('auth_user');

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'user_group_id' => 'nullable|exists:user_groups,id',
            'expiry_date' => 'nullable|date|after_or_equal:today',
            'quantity' => 'required|integer|min:1',
            'location' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify user belongs to group if specified
        if ($request->user_group_id) {
            $userGroup = $user->groups()->find($request->user_group_id);
            if (!$userGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nie należysz do tej grupy'
                ], 403);
            }
        }

        $item = PantryItem::create([
            'product_id' => $request->product_id,
            'added_by' => $user->id,
            'user_group_id' => $request->user_group_id,
            'expiry_date' => $request->expiry_date,
            'quantity' => $request->quantity,
            'location' => $request->location,
            'notes' => $request->notes,
        ]);

        $item->load(['product.category', 'addedBy', 'userGroup']);

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'location' => $item->location,
                'expiry_date' => $item->expiry_date?->format('Y-m-d'),
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                ],
            ],
            'message' => 'Produkt dodany do magazynu'
        ], 201);
    }

    /**
     * Update pantry item.
     */
    public function update(Request $request, $id)
    {
        $item = PantryItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Element nie został znaleziony'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'expiry_date' => 'nullable|date',
            'quantity' => 'integer|min:0',
            'location' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'user_group_id' => 'nullable|exists:user_groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $item->update($request->only(['expiry_date', 'quantity', 'location', 'notes', 'user_group_id']));

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'location' => $item->location,
                'expiry_date' => $item->expiry_date?->format('Y-m-d'),
            ],
            'message' => 'Element zaktualizowany'
        ]);
    }

    /**
     * Mark item as consumed (soft delete).
     */
    public function consume(Request $request, $id)
    {
        $item = PantryItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Element nie został znaleziony'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $consumeQuantity = $request->input('quantity', $item->quantity);

        // Validate quantity doesn't exceed available
        if ($consumeQuantity > $item->quantity) {
            return response()->json([
                'success' => false,
                'message' => "Nie można zużyć więcej niż dostępna ilość ({$item->quantity})"
            ], 422);
        }

        if ($consumeQuantity >= $item->quantity) {
            // Consume all - mark as consumed
            $item->markAsConsumed();
            $message = 'Produkt oznaczony jako zużyty';
        } else {
            // Partial consume - reduce quantity
            $item->quantity -= $consumeQuantity;
            $item->save();
            $message = "Zużyto {$consumeQuantity} sztuk. Pozostało: {$item->quantity}";
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Delete pantry item.
     */
    public function destroy($id)
    {
        $item = PantryItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Element nie został znaleziony'
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Element usunięty z magazynu'
        ]);
    }

    /**
     * Get items expiring soon.
     */
    public function expiringSoon(Request $request)
    {
        $user = $request->attributes->get('auth_user');
        
        $query = PantryItem::with(['product.category', 'addedBy', 'userGroup'])
            ->expiringSoon();

        // Filter by user group if specified
        if ($request->has('group_id')) {
            $query->where('user_group_id', $request->group_id);
        }

        $items = $query->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'expiry_date' => $item->expiry_date?->format('Y-m-d'),
                    'days_until_expiry' => $item->daysUntilExpiry(),
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'image_url' => $item->product->image_url,
                        'category' => [
                            'icon' => $item->product->category->icon,
                            'name' => $item->product->category->name,
                        ],
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'items' => $items,
            'count' => $items->count(),
        ]);
    }

    /**
     * Get pantry statistics.
     */
    public function statistics(Request $request)
    {
        $user = $request->attributes->get('auth_user');
        
        $query = PantryItem::active();

        // Filter by group if specified
        if ($request->has('group_id')) {
            $query->where('user_group_id', $request->group_id);
        }

        $totalItems = $query->count();
        $expiringSoonCount = (clone $query)->expiringSoon()->count();
        $expiredCount = (clone $query)->expired()->count();

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_items' => $totalItems,
                'expiring_soon' => $expiringSoonCount,
                'expired' => $expiredCount,
            ]
        ]);
    }
}
