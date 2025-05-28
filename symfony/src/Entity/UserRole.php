<?php

declare(strict_types=1);

namespace App\Entity;

enum UserRole: string
{
    case ADMIN = 'ROLE_ADMIN';
    case AUTHOR = 'ROLE_AUTHOR';
    case READER = 'ROLE_READER';

    public static function getValues(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}