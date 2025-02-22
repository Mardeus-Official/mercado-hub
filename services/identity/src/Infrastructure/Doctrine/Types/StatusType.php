<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Types;

use App\Domain\Status;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use RuntimeException;

final class StatusType extends Type
{
    public const string NAME = 'status';

    public function convertToPHPValue($value, AbstractPlatform $platform): Status
    {
        if (! is_string($value)) {
            throw new RuntimeException('Invalid status value: ' . $value);
        }

        return Status::from($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof Status) {
            return $value->value;
        }

        throw new RuntimeException('Invalid status value: ' . $value);
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
