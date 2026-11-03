<?php

namespace App\Enums;

enum ItemCondition: string
{
    case BAIK = 'Baik';
    case RUSAK = 'Rusak';
    case PERLU_PERBAIKAN = 'Perlu Perbaikan';

    public function label(): string
    {
        return match ($this) {
            self::BAIK => 'Baik',
            self::RUSAK => 'Rusak',
            self::PERLU_PERBAIKAN => 'Perlu Perbaikan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::BAIK => 'badge-success bg-green-100 text-green-800 border-green-200',
            self::RUSAK => 'badge-danger bg-red-100 text-red-800 border-red-200',
            self::PERLU_PERBAIKAN => 'badge-warning bg-amber-100 text-amber-800 border-amber-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
