<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductScanHistory extends Model
{
    protected $table = 'product_scan_history';

    protected $fillable = [
        'product_id',
        'scanned_by',
        'ean_code',
        'scanned_at',
        'location',
        'notes',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    /**
     * Get the product that was scanned.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who scanned the product.
     */
    public function scanner()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
