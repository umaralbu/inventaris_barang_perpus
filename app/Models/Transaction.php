<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'transaction_code',
        'catalog_id',
        'item_id',
        'user_id',
        'approved_by_user_id',
        'staff_id',
        'to_location_id',
        'borrow_transaction_id',
        'type',
        'status',
        'from_location',
        'to_location',
        'from_person',
        'to_person',
        'quantity',
        'transaction_date',
        'expected_return_date',
        'condition',
        'notes',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'type'                 => TransactionType::class,
            'status'               => TransactionStatus::class,
            'transaction_date'     => 'datetime',
            'expected_return_date' => 'date',
        ];
    }

    /**
     * Details of physical items involved in this transaction.
     */
    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }

    /**
     * Physical staff member responsible for or borrowing the items.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * System user who created/submitted this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Administrator who approved this transaction.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /**
     * Parent transaction (for return transactions referencing an original borrow).
     */
    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'borrow_transaction_id');
    }

    /**
     * Return transactions associated with this borrow transaction.
     */
    public function returnTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'borrow_transaction_id');
    }

    /**
     * Item catalog requested in this transaction.
     */
    public function catalog(): BelongsTo
    {
        return $this->belongsTo(ItemCatalog::class, 'catalog_id');
    }

    /**
     * Destination location relation.
     */
    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    /**
     * Legacy item relation for backward compatibility.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Check if this borrow transaction is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->expected_return_date
            && $this->status === TransactionStatus::DIPINJAM
            && $this->expected_return_date->isPast();
    }

    /**
     * Check if this transaction is still awaiting approval.
     */
    public function isPending(): bool
    {
        return $this->status === TransactionStatus::MENUNGGU_PERSETUJUAN;
    }

    /**
     * Check if a given user has authority to cancel this pending transaction.
     */
    public function isCancellableBy(User $user): bool
    {
        return $this->isPending() && ($user->isAdmin() || $this->user_id === $user->id);
    }
}

