<?php

declare(strict_types=1);

namespace App\Application\Import\Exceptions;

use JsonException;
use RuntimeException;

final class InvalidRouteImportException extends RuntimeException
{
    public static function fileNotFound(string $path): self
    {
        return new self(sprintf('Файл імпорту не знайдено: %s', $path));
    }

    public static function fileNotReadable(string $path): self
    {
        return new self(sprintf('Файл імпорту недоступний для читання: %s', $path));
    }

    public static function readFailed(string $path): self
    {
        return new self(sprintf('Не вдалося прочитати файл імпорту: %s', $path));
    }

    public static function invalidJson(string $path, JsonException $exception): self
    {
        return new self(
            sprintf('Невалідний JSON у файлі "%s": %s', $path, $exception->getMessage()),
            previous: $exception,
        );
    }

    public static function at(string $source, string $jsonPath, string $message): self
    {
        return new self(sprintf('%s: %s: %s', $source, $jsonPath, $message));
    }
}
