<?php

declare(strict_types=1);

namespace App\Exceptions\Exception;

use App\Exceptions\Interfaces\ApiExceptionInterface;
use RuntimeException;

final class StopNotFoundException extends RuntimeException implements ApiExceptionInterface
{
    public function __construct(private readonly mixed $stopId)
    {
        parent::__construct('Зупинки не знайдено');
    }

    public function getContext(): array
    {
        return ['stopId' => $this->stopId];
    }
}
