<?php

namespace App\Models;

use App\Enums\ItemCondition;
use App\Enums\ItemUnitStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemCatalog extends Model
{
    use HasFactory;

    protected $table = 'items_catalog';

    protected $fillable = [
        'category_id',
        'name',
        'brand',
        'specification',
        'unit_name',
        'photo',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Category of this item catalog.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Physical item units associated with this catalog.
     */
    public function units(): HasMany
    {
        return $this->hasMany(Item::class, 'catalog_id');
    }

    /**
     * Accessor for available units count (status 'Tersedia' and condition 'Baik' and is_active = true).
     */
    protected function availableUnitsCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->units()
                ->where('is_active', true)
                ->where('status', ItemUnitStatus::TERSEDIA->value)
                ->where('condition', ItemCondition::BAIK->value)
                ->count()
        );
    }

    /**
     * Scope query to only include active catalogs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
