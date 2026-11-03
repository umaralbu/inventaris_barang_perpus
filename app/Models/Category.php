<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
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
     * Get the item catalogs associated with this category.
     */
    public function catalogs(): HasMany
    {
        return $this->hasMany(ItemCatalog::class, 'category_id');
    }

    /**
     * Get the physical item units under this category.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'category_id');
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

