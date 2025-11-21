<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name',
        'ean_code',
        'category_id',
        'subcategory',
        'image_path',
        'description',
        'created_by',
    ];

    protected $appends = ['image_url'];

    /**
     * Get the category of this product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Get the user who created this product.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the full image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return Storage::url($this->image_path);
    }

    /**
     * Delete product image if exists.
     */
    public function deleteImage(): void
    {
        if ($this->image_path && Storage::exists($this->image_path)) {
            Storage::delete($this->image_path);
        }
    }

    /**
     * Search products by name or EAN.
     */
    public static function search(string $query, ?int $categoryId = null)
    {
        $searchQuery = self::with(['category', 'creator'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('ean_code', 'LIKE', "%{$query}%");
            });

        if ($categoryId) {
            $searchQuery->where('category_id', $categoryId);
        }

        return $searchQuery->get();
    }
}
