<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case MENUNGGU_PERSETUJUAN = 'Menunggu Persetujuan';
    case DIPINJAM = 'Dipinjam';
    case SEBAGIAN_DIKEMBALIKAN = 'Sebagian Dikembalikan';
    case DIKEMBALIKAN = 'Dikembalikan';
    case DITOLAK = 'Ditolak';
    case DIBATALKAN = 'Dibatalkan';
    case SELESAI = 'Selesai';

    public function label(): string
    {
        return $this->value;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::MENUNGGU_PERSETUJUAN => 'bg-amber-100 text-amber-800 border-amber-200',
            self::DIPINJAM => 'bg-blue-100 text-blue-800 border-blue-200',
            self::SEBAGIAN_DIKEMBALIKAN => 'bg-cyan-100 text-cyan-800 border-cyan-200',
            self::DIKEMBALIKAN => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::DITOLAK => 'bg-rose-100 text-rose-800 border-rose-200',
            self::DIBATALKAN => 'bg-gray-100 text-gray-800 border-gray-200',
            self::SELESAI => 'bg-teal-100 text-teal-800 border-teal-200',
        };
    }

    public function isPending(): bool
    {
        return $this === self::MENUNGGU_PERSETUJUAN;
    }

    public function isCancellable(): bool
    {
        return $this === self::MENUNGGU_PERSETUJUAN;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

