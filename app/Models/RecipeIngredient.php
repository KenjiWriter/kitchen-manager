<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    protected $fillable = [
        'recipe_id',
        'original_name',
        'normalized_name',
        'measure',
        'product_id',
        'product_category_id',
        'estimated_quantity',
    ];

    protected $casts = [
        'estimated_quantity' => 'decimal:2',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Sprawdza dostępność składnika w spiżarni
     */
    public function checkAvailability($userGroupId)
    {
        $result = [
            'available' => false,
            'partial' => false,
            'found_items' => [],
            'total_quantity' => 0,
            'required_quantity' => $this->estimated_quantity ?? 0,
        ];

        // Jeśli zmapowano konkretny produkt
        if ($this->product_id) {
            $pantryItems = PantryItem::where('user_group_id', $userGroupId)
                ->where('product_id', $this->product_id)
                ->where('quantity', '>', 0)
                ->get();

            $totalQuantity = $pantryItems->sum('quantity');
            $result['total_quantity'] = $totalQuantity;
            $result['found_items'] = $pantryItems;

            if ($totalQuantity >= $this->estimated_quantity) {
                $result['available'] = true;
            } elseif ($totalQuantity > 0) {
                $result['partial'] = true;
            }
        }
        // Jeśli zmapowano kategorię
        elseif ($this->product_category_id) {
            $pantryItems = PantryItem::where('user_group_id', $userGroupId)
                ->whereHas('product', function($query) {
                    $query->where('category_id', $this->product_category_id);
                })
                ->where('quantity', '>', 0)
                ->with('product')
                ->get();

            $totalQuantity = $pantryItems->sum('quantity');
            $result['total_quantity'] = $totalQuantity;
            $result['found_items'] = $pantryItems;

            if ($totalQuantity >= $this->estimated_quantity) {
                $result['available'] = true;
            } elseif ($totalQuantity > 0) {
                $result['partial'] = true;
            }
        }

        return $result;
    }
}
