<?php

declare(strict_types=1);

namespace App\Exceptions\Exception;

use App\Exceptions\Interfaces\ApiExceptionInterface;
use RuntimeException;

class TripNotFoundException extends RuntimeException implements ApiExceptionInterface
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
