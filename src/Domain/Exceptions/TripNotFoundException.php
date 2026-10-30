<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\Exceptions\Interfaces\DomainExceptionInterface;
use RuntimeException;

class TripNotFoundException extends RuntimeException implements DomainExceptionInterface
{
    public function __construct(private readonly mixed $tripId)
    {
        parent::__construct('Рейс не знайдено');
    }

    public function getContext(): array
    {
        return ['tripId' => $this->tripId];
    }
}
