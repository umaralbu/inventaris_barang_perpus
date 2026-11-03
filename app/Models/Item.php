<?php

namespace App\Models;

use App\Enums\ItemCondition;
use App\Enums\ItemUnitStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $table = 'items';

    protected $fillable = [
        'catalog_id',
        'category_id',
        'location_id',
        'code',
        'name',
        'brand',
        'specification',
        'quantity',
        'unit',
        'condition',
        'status',
        'purchase_date',
        'purchase_price',
        'description',
        'photo',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'condition'      => ItemCondition::class,
            'status'         => ItemUnitStatus::class,
            'purchase_date'  => 'date',
            'purchase_price' => 'decimal:2',
            'is_active'      => 'boolean',
        ];
    }

    /**
     * Parent catalog item.
     */
    public function catalog(): BelongsTo
    {
        return $this->belongsTo(ItemCatalog::class, 'catalog_id');
    }

    /**
     * Category of the item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Current storage location of this physical unit.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Transaction history details for this physical unit.
     */
    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'item_id');
    }

    /**
     * Check if this physical unit is ready and available for borrowing.
     */
    public function isAvailable(): bool
    {
        return $this->status === ItemUnitStatus::TERSEDIA
            && $this->condition === ItemCondition::BAIK
            && $this->is_active;
    }

    /**
     * Scope query to only available items.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', ItemUnitStatus::TERSEDIA->value)
            ->where('condition', ItemCondition::BAIK->value)
            ->where('is_active', true);
    }

    /**
     * Scope query to only active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

