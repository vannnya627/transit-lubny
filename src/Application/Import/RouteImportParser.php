<?php

declare(strict_types=1);

namespace App\Application\Import;

use App\Application\Import\DTOs\PatternImportData;
use App\Application\Import\DTOs\RouteImportData;
use App\Application\Import\DTOs\StopImportData;
use App\Application\Import\DTOs\TripImportData;
use App\Application\Import\Exceptions\InvalidRouteImportException;
use App\Application\Import\Interfaces\RouteImportParserInterface;
use App\Domain\Enums\Direction;
use App\Domain\ValueObjects\Coordinates;
use App\Domain\ValueObjects\TimeOfDay;
use InvalidArgumentException;
use JsonException;

final readonly class RouteImportParser implements RouteImportParserInterface
{
    /**
     * @return list<RouteImportData>
     */
    public function parse(string $json, string $source): array
    {
        try {
            $root = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw InvalidRouteImportException::invalidJson($source, $jsonException);
        }

        if (!is_array($root)) {
            throw InvalidRouteImportException::at($source, '$', 'корінь JSON має бути масивом маршрутів');
        }

        try {
            $routes = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw InvalidRouteImportException::invalidJson($source, $jsonException);
        }

        if (!is_array($routes) || !array_is_list($routes)) {
            throw InvalidRouteImportException::at($source, '$', 'корінь JSON має бути списком маршрутів');
        }

        if ([] === $routes) {
            throw InvalidRouteImportException::at($source, '$', 'файл не містить маршрутів');
        }

        $result = [];

        foreach ($routes as $index => $route) {
            $result[] = $this->parseRoute($route, $source, sprintf('$[%d]', $index));
        }

        $this->assertCityCodesAreConsistent($result, $source);

        return $result;
    }

    private function parseRoute(mixed $value, string $source, string $path): RouteImportData
    {
        $route = $this->arrayValue($value, $source, $path, 'маршрут');

        $shortName = $this->stringValue(
            value: $route['shortName'] ?? null,
            source: $source,
            path: $path . '.shortName',
            label: 'shortName',
        );

        if ('' === trim($shortName)) {
            throw InvalidRouteImportException::at($source, $path . '.shortName', 'shortName не може бути порожнім');
        }

        $longName = $this->optionalStringValue(
            value: $route['longName'] ?? null,
            source: $source,
            path: $path . '.longName',
            label: 'longName',
        ) ?? '';

        $patterns = $this->listValue(
            value: $route['patterns'] ?? null,
            source: $source,
            path: $path . '.patterns',
            label: 'patterns',
        );

        if ([] === $patterns) {
            throw InvalidRouteImportException::at($source, $path . '.patterns', 'patterns не може бути порожнім');
        }

        $patternData = [];

        foreach ($patterns as $index => $pattern) {
            $patternData[] = $this->parsePattern(
                value: $pattern,
                source: $source,
                path: sprintf('%s.patterns[%d]', $path, $index),
            );
        }

        return new RouteImportData($shortName, $longName, $patternData);
    }

    private function parsePattern(mixed $value, string $source, string $path): PatternImportData
    {
        $pattern = $this->arrayValue($value, $source, $path, 'pattern');

        $direction = $this->directionValue(
            value: $pattern['direction'] ?? null,
            source: $source,
            path: $path . '.direction',
        );

        $name = $this->stringValue(
            value: $pattern['name'] ?? null,
            source: $source,
            path: $path . '.name',
            label: 'name',
        );

        $stops = $this->listValue(
            value: $pattern['stops'] ?? null,
            source: $source,
            path: $path . '.stops',
            label: 'stops',
        );

        if ([] === $stops) {
            throw InvalidRouteImportException::at($source, $path . '.stops', 'stops не може бути порожнім');
        }

        $stopData = [];

        foreach ($stops as $index => $stop) {
            $stopData[] = $this->parseStop(
                value: $stop,
                source: $source,
                path: sprintf('%s.stops[%d]', $path, $index),
            );
        }

        $trips = $this->listValue(
            value: $pattern['trips'] ?? null,
            source: $source,
            path: $path . '.trips',
            label: 'trips',
        );

        $tripData = [];

        foreach ($trips as $index => $trip) {
            $tripData[] = $this->parseTrip(
                value: $trip,
                source: $source,
                path: sprintf('%s.trips[%d]', $path, $index),
                stopsCount: count($stopData),
            );
        }

        return new PatternImportData($direction, $name, $stopData, $tripData);
    }

    private function parseStop(mixed $value, string $source, string $path): StopImportData
    {
        $stop = $this->arrayValue($value, $source, $path, 'зупинка');

        $cityCode = $this->positiveIntValue(
            value: $stop['cityCode'] ?? null,
            source: $source,
            path: $path . '.cityCode',
            label: 'cityCode',
        );

        $name = $this->stringValue(
            value: $stop['name'] ?? null,
            source: $source,
            path: $path . '.name',
            label: 'name',
        );

        if ('' === trim($name)) {
            throw InvalidRouteImportException::at($source, $path . '.name', 'name зупинки не може бути порожнім');
        }

        $lat = $this->floatValue(
            value: $stop['lat'] ?? null,
            source: $source,
            path: $path . '.lat',
            label: 'lat',
        );

        $lon = $this->floatValue(
            value: $stop['lon'] ?? null,
            source: $source,
            path: $path . '.lon',
            label: 'lon',
        );

        try {
            $coordinates = Coordinates::create($lat, $lon);
        } catch (InvalidArgumentException $invalidArgumentException) {
            throw InvalidRouteImportException::at($source, $path, $invalidArgumentException->getMessage());
        }

        return new StopImportData($cityCode, $name, $coordinates);
    }

    private function parseTrip(
        mixed $value,
        string $source,
        string $path,
        int $stopsCount,
    ): TripImportData {
        $trip = $this->arrayValue($value, $source, $path, 'рейс');

        $times = $this->listValue(
            value: $trip['times'] ?? null,
            source: $source,
            path: $path . '.times',
            label: 'times',
        );

        if (count($times) !== $stopsCount) {
            throw $times |> count(...) |> (fn($x) => sprintf('кількість times (%d) не дорівнює кількості зупинок (%d)', $x, $stopsCount)) |> (fn($x) => InvalidRouteImportException::at($source, $path . '.times', $x));
        }

        $timeData = [];

        foreach ($times as $index => $time) {
            $timeData[] = $this->timeValue(
                value: $time,
                source: $source,
                path: sprintf('%s.times[%d]', $path, $index),
            );
        }

        return new TripImportData($timeData);
    }

    private function timeValue(mixed $value, string $source, string $path): ?TimeOfDay
    {
        if (null === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw InvalidRouteImportException::at($source, $path, 'час має бути рядком');
        }

        $value = trim($value);

        if (1 !== preg_match('/^(?:[01]?\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $value)) {
            throw InvalidRouteImportException::at($source, $path, sprintf('невалідний час "%s", очікується формат H:i', $value));
        }

        try {
            return TimeOfDay::fromString($value);
        } catch (InvalidArgumentException $invalidArgumentException) {
            throw InvalidRouteImportException::at($source, $path, $invalidArgumentException->getMessage());
        }
    }

    private function directionValue(mixed $value, string $source, string $path): Direction
    {
        if (is_int($value)) {
            $raw = $value;
        } elseif (is_string($value) && 1 === preg_match('/^-?\d+$/', trim($value))) {
            $raw = (int) trim($value);
        } else {
            throw InvalidRouteImportException::at($source, $path, 'direction має бути цілим числом 0 або 1');
        }

        $direction = Direction::tryFrom($raw);

        if (null === $direction) {
            throw InvalidRouteImportException::at($source, $path, sprintf('невідоме значення direction: %d', $raw));
        }

        return $direction;
    }

    private function positiveIntValue(
        mixed $value,
        string $source,
        string $path,
        string $label,
    ): int {
        if (is_int($value)) {
            $int = $value;
        } elseif (is_string($value) && 1 === preg_match('/^\d+$/', trim($value))) {
            $int = (int) trim($value);
        } else {
            throw InvalidRouteImportException::at($source, $path, sprintf('%s має бути додатним цілим', $label));
        }

        if ($int <= 0) {
            throw InvalidRouteImportException::at($source, $path, sprintf('%s має бути більшим за 0', $label));
        }

        return $int;
    }

    private function floatValue(
        mixed $value,
        string $source,
        string $path,
        string $label,
    ): float {
        if (
            is_int($value)
            || is_float($value)
            || (is_string($value) && is_numeric(trim($value)))
        ) {
            $float = (float) $value;

            if (is_finite($float)) {
                return $float;
            }
        }

        throw InvalidRouteImportException::at($source, $path, sprintf('%s має бути числом', $label));
    }

    /**
     * @return array<array-key, mixed>
     */
    private function arrayValue(
        mixed $value,
        string $source,
        string $path,
        string $label,
    ): array {
        if (!is_array($value)) {
            throw InvalidRouteImportException::at($source, $path, sprintf('%s має бути об’єктом', $label));
        }

        return $value;
    }

    /**
     * @return list<mixed>
     */
    private function listValue(
        mixed $value,
        string $source,
        string $path,
        string $label,
    ): array {
        if (!is_array($value) || !array_is_list($value)) {
            throw InvalidRouteImportException::at($source, $path, sprintf('%s має бути списком', $label));
        }

        return $value;
    }

    private function stringValue(
        mixed $value,
        string $source,
        string $path,
        string $label,
    ): string {
        if (!is_string($value)) {
            throw InvalidRouteImportException::at($source, $path, sprintf('%s має бути рядком', $label));
        }

        return $value;
    }

    private function optionalStringValue(
        mixed $value,
        string $source,
        string $path,
        string $label,
    ): ?string {
        if (null === $value) {
            return null;
        }

        return $this->stringValue($value, $source, $path, $label);
    }

    /**
     * @param list<RouteImportData> $routes
     */
    private function assertCityCodesAreConsistent(array $routes, string $source): void
    {
        $namesByCityCode = [];

        foreach ($routes as $routeIndex => $route) {
            foreach ($route->patterns as $patternIndex => $pattern) {
                foreach ($pattern->stops as $stopIndex => $stop) {
                    $existingName = $namesByCityCode[$stop->cityCode] ?? null;

                    if (null !== $existingName && $existingName !== $stop->name) {
                        throw InvalidRouteImportException::at($source, sprintf('$[%d].patterns[%d].stops[%d].cityCode', $routeIndex, $patternIndex, $stopIndex), sprintf('cityCode %d вже прив’язаний до "%s", але тут вказано "%s"', $stop->cityCode, $existingName, $stop->name));
                    }

                    $namesByCityCode[$stop->cityCode] = $stop->name;
                }
            }
        }
    }
}
