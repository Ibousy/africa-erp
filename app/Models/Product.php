<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'tenant_id', 'code', 'name', 'category', 'description', 'unit',
        'quantity_in_stock', 'min_stock_alert', 'unit_price', 'status',
    ];

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    public function qualityControls(): HasMany
    {
        return $this->hasMany(QualityControl::class);
    }

    public function bomItems(): HasMany
    {
        return $this->hasMany(BomItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity_in_stock <= $this->min_stock_alert && $this->min_stock_alert > 0;
    }
}
