<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Types;

use App\Domain\Role;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use RuntimeException;

final class RoleType extends Type
{
    public const string NAME = 'role';

    public function convertToPHPValue($value, AbstractPlatform $platform): Role
    {
        if (! is_string($value)) {
            throw new RuntimeException('Invalid role value: ' . $value);
        }

        return Role::from($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof Role) {
            return $value->value;
        }

        throw new RuntimeException('Invalid role value: ' . $value);
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
