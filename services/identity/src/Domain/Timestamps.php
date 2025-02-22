<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Timestamps
{
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true, options: ['default' => null])]
    private ?DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true, options: ['default' => null])]
    private ?DateTimeImmutable $deletedAt;
}
