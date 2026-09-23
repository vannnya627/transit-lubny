<?php

declare(strict_types=1);

namespace App\Exceptions\Exception;

use App\Exceptions\Interfaces\ApiExceptionInterface;
use RuntimeException;

final class RouteNotFoundException extends RuntimeException implements ApiExceptionInterface
{
    public function __construct(private readonly mixed $routeId)
    {
        parent::__construct('Маршруту не знайдено');
    }

    public function getContext(): array
    {
        return ['productId' => $this->routeId];
    }
}
