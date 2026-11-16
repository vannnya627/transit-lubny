<?php

declare(strict_types=1);

namespace App\Infrastructure\Filesystem;

use App\Application\Import\Exceptions\InvalidRouteImportException;
use App\Application\Import\Interfaces\RouteImportFileReaderInterface;

final readonly class RouteImportFileReader implements RouteImportFileReaderInterface
{
    public function read(string $path): string
    {
        if (!is_file($path)) {
            throw InvalidRouteImportException::fileNotFound($path);
        }

        if (!is_readable($path)) {
            throw InvalidRouteImportException::fileNotReadable($path);
        }

        $contents = file_get_contents($path);

        if (false === $contents) {
            throw InvalidRouteImportException::readFailed($path);
        }

        return $contents;
    }
}
