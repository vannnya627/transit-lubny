<?php

declare(strict_types=1);

namespace App\Application\Import\Interfaces;

interface RouteImportFileReaderInterface
{
    public function read(string $path): string;
}
