<?php

namespace App\Enums;

enum ItemUnitStatus: string
{
    case TERSEDIA = 'Tersedia';
    case DIPINJAM = 'Dipinjam';
    case DALAM_PERBAIKAN = 'Dalam Perbaikan';
    case HILANG = 'Hilang';
    case NONAKTIF = 'Nonaktif';

    public function label(): string
    {
        return match ($this) {
            self::TERSEDIA => 'Tersedia',
            self::DIPINJAM => 'Dipinjam',
            self::DALAM_PERBAIKAN => 'Dalam Perbaikan',
            self::HILANG => 'Hilang',
            self::NONAKTIF => 'Nonaktif',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::TERSEDIA => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::DIPINJAM => 'bg-blue-100 text-blue-800 border-blue-200',
            self::DALAM_PERBAIKAN => 'bg-amber-100 text-amber-800 border-amber-200',
            self::HILANG => 'bg-rose-100 text-rose-800 border-rose-200',
            self::NONAKTIF => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
