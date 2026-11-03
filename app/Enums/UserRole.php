<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case PEGAWAI = 'pegawai';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::PEGAWAI => 'Pegawai',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ADMIN => 'bg-purple-100 text-purple-800 border-purple-200',
            self::PEGAWAI => 'bg-sky-100 text-sky-800 border-sky-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
