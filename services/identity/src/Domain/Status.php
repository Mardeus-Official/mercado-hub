<?php

declare(strict_types=1);

namespace App\Domain;

enum Status : string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DELETED = 'deleted';
}
