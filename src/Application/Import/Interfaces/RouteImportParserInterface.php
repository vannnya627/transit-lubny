<?php

declare(strict_types=1);

namespace App\Application\Import\Interfaces;

use App\Application\Import\DTOs\RouteImportData;

interface RouteImportParserInterface
{
    /**
     * @return list<RouteImportData>
     */
    public function parse(string $json, string $source): array;
}
