<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the transactions associated with this staff member.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'staff_id');
    }

    /**
     * Scope a query to only include active staff members.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

