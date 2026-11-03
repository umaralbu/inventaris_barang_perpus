<?php

namespace App\Enums;

enum TransactionType: string
{
    case BORROW = 'borrow';
    case RETURN = 'return';
    case TRANSFER = 'transfer';
    case MAINTENANCE = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::BORROW => 'Peminjaman',
            self::RETURN => 'Pengembalian',
            self::TRANSFER => 'Mutasi Lokasi',
            self::MAINTENANCE => 'Pemeliharaan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::BORROW => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            self::RETURN => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::TRANSFER => 'bg-purple-100 text-purple-800 border-purple-200',
            self::MAINTENANCE => 'bg-amber-100 text-amber-800 border-amber-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

