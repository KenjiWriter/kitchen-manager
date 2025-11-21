<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'color',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get all products in this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Get unique subcategories for this category.
     */
    public function getSubcategories(): array
    {
        return $this->products()
            ->whereNotNull('subcategory')
            ->distinct()
            ->pluck('subcategory')
            ->toArray();
    }
}
