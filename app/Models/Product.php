<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'sku', 'name', 'description', 'unit',
        'cost_price', 'reorder_level', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'cost_price' => 'decimal:2'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getTotalStockAttribute()
    {
        return $this->warehouseStocks()->sum('quantity');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('warehouseStocks', function ($q) {
            $q->havingRaw('SUM(quantity) <= products.reorder_level');
        });
    }

    public function scopeInStock($query)
    {
        return $query->whereHas('warehouseStocks', function ($q) {
            $q->havingRaw('SUM(quantity) > 0');
        });
    }
}