<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\Exceptions\Interfaces\DomainExceptionInterface;
use RuntimeException;

final class StopNotFoundException extends RuntimeException implements DomainExceptionInterface
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
