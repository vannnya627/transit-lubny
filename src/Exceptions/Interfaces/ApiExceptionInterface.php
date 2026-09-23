<?php

declare(strict_types=1);

namespace App\Exceptions\Interfaces;

interface ApiExceptionInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getContext(): array;
}
