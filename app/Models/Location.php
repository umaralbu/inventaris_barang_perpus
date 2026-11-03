<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
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
     * Get the physical item units currently stored at this location.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'location_id');
    }

    /**
     * Transaction details where this location is the source location.
     */
    public function transactionDetailsFrom(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'from_location_id');
    }

    /**
     * Transaction details where this location is the destination location.
     */
    public function transactionDetailsTo(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'to_location_id');
    }

    /**
     * Scope a query to only include active locations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

