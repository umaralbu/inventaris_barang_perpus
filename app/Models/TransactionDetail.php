<?php

namespace App\Models;

use App\Enums\ItemCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $table = 'transaction_details';

    protected $fillable = [
        'transaction_id',
        'item_id',
        'from_location_id',
        'to_location_id',
        'condition_before',
        'condition_after',
        'is_returned',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'condition_before' => ItemCondition::class,
            'condition_after'  => ItemCondition::class,
            'is_returned'      => 'boolean',
            'returned_at'      => 'datetime',
        ];
    }

    /**
     * Parent transaction header.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    /**
     * Physical item unit involved.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Source location before this transaction.
     */
    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    /**
     * Destination location after this transaction.
     */
    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }
}
