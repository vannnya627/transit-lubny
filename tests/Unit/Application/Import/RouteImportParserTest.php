<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Import;

use App\Application\Import\Exceptions\InvalidRouteImportException;
use App\Application\Import\RouteImportParser;
use App\Domain\Enums\Direction;
use App\Tests\AbstractTestCase;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class RouteImportParserTest extends AbstractTestCase
{
    private const string SOURCE = 'route.json';

    /**
     * @throws JsonException
     */
    #[Test]
    public function itParsesValidPayload(): void
    {
        $routes = new RouteImportParser()->parse(
            json_encode([$this->validRoute()], JSON_THROW_ON_ERROR),
            self::SOURCE,
        );

        self::assertCount(1, $routes);
        self::assertSame('99', $routes[0]->shortName);
        self::assertSame('Тестовий маршрут', $routes[0]->longName);
        self::assertCount(1, $routes[0]->patterns);

        $pattern = $routes[0]->patterns[0];
        self::assertSame(Direction::FORWARD, $pattern->direction);
        self::assertSame('Прямо', $pattern->name);
        self::assertCount(2, $pattern->stops);
        self::assertSame(991, $pattern->stops[0]->cityCode);
        self::assertCount(1, $pattern->trips);
        self::assertCount(2, $pattern->trips[0]->times);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function itAllowsDuplicateShortName(): void
    {
        $routes = new RouteImportParser()->parse(
            json_encode([$this->validRoute(), $this->validRoute()], JSON_THROW_ON_ERROR),
            self::SOURCE,
        );

        self::assertCount(2, $routes);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function itAllowsNullAsMissingTime(): void
    {
        $payload = $this->validRoute();
        $payload['patterns'][0]['trips'][0]['times'] = ['08:00', null];

        $routes = new RouteImportParser()->parse(
            json_encode([$payload], JSON_THROW_ON_ERROR),
            self::SOURCE,
        );

        self::assertNull($routes[0]->patterns[0]->trips[0]->times[1]);
    }

    #[Test]
    public function itRejectsInvalidJson(): void
    {
        $this->expectException(InvalidRouteImportException::class);
        $this->expectExceptionMessageIsOrContains('Невалідний JSON');

        new RouteImportParser()->parse('{"shortName": ', self::SOURCE);
    }

    #[Test]
    public function itRejectsRootObject(): void
    {
        $this->expectException(InvalidRouteImportException::class);
        $this->expectExceptionMessageIsOrContains('корінь JSON має бути масивом');

        new RouteImportParser()->parse('{}', self::SOURCE);
    }

    #[Test]
    public function itRejectsRootScalar(): void
    {
        $this->expectException(InvalidRouteImportException::class);
        $this->expectExceptionMessageIsOrContains('корінь JSON має бути масивом');

        new RouteImportParser()->parse('"route"', self::SOURCE);
    }

    #[Test]
    public function itRejectsEmptyList(): void
    {
        $this->expectException(InvalidRouteImportException::class);
        $this->expectExceptionMessageIsOrContains('файл не містить маршрутів');

        new RouteImportParser()->parse('[]', self::SOURCE);
    }

    /**
     * @param callable(array<string, mixed>): array<string, mixed> $mutate
     *
     * @throws JsonException
     */
    #[Test]
    #[DataProvider('invalidPayloadProvider')]
    public function itRejectsInvalidStructure(callable $mutate, string $expectedMessage): void
    {
        $payload = $mutate($this->validRoute());

        $this->expectException(InvalidRouteImportException::class);
        $this->expectExceptionMessageIsOrContains($expectedMessage);

        new RouteImportParser()->parse(
            json_encode([$payload], JSON_THROW_ON_ERROR),
            self::SOURCE,
        );
    }

    /**
     * @return iterable<string, array{callable(array<string, mixed>): array<string, mixed>, string}>
     */
    public static function invalidPayloadProvider(): iterable
    {
        yield 'missing shortName' => [
            static function (array $route): array {
                unset($route['shortName']);

                return $route;
            },
            'shortName',
        ];

        yield 'blank shortName' => [
            static function (array $route): array {
                $route['shortName'] = '  ';

                return $route;
            },
            'shortName не може бути порожнім',
        ];

        yield 'empty patterns' => [
            static function (array $route): array {
                $route['patterns'] = [];

                return $route;
            },
            'patterns не може бути порожнім',
        ];

        yield 'invalid direction' => [
            static function (array $route): array {
                $route['patterns'][0]['direction'] = 7;

                return $route;
            },
            'невідоме значення direction',
        ];

        yield 'empty stops' => [
            static function (array $route): array {
                $route['patterns'][0]['stops'] = [];

                return $route;
            },
            'stops не може бути порожнім',
        ];

        yield 'non numeric cityCode' => [
            static function (array $route): array {
                $route['patterns'][0]['stops'][0]['cityCode'] = 'abc';

                return $route;
            },
            'cityCode має бути додатним цілим',
        ];

        yield 'non positive cityCode' => [
            static function (array $route): array {
                $route['patterns'][0]['stops'][0]['cityCode'] = 0;

                return $route;
            },
            'cityCode має бути більшим за 0',
        ];

        yield 'latitude out of range' => [
            static function (array $route): array {
                $route['patterns'][0]['stops'][0]['lat'] = 91.0;

                return $route;
            },
            'Широта (lat)',
        ];

        yield 'longitude out of range' => [
            static function (array $route): array {
                $route['patterns'][0]['stops'][0]['lon'] = 181.0;

                return $route;
            },
            'Довгота (lon)',
        ];

        yield 'invalid time' => [
            static function (array $route): array {
                $route['patterns'][0]['trips'][0]['times'][0] = '25:00';

                return $route;
            },
            'невалідний час',
        ];

        yield 'times count mismatch' => [
            static function (array $route): array {
                $route['patterns'][0]['trips'][0]['times'] = ['08:00'];

                return $route;
            },
            'кількість times',
        ];

        yield 'cityCode collision' => [
            static function (array $route): array {
                $route['patterns'][0]['stops'][1]['cityCode'] = 991;
                $route['patterns'][0]['stops'][1]['name'] = 'Інша зупинка';

                return $route;
            },
            'cityCode 991 вже прив’язаний',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validRoute(): array
    {
        return [
            'shortName' => '99',
            'longName' => 'Тестовий маршрут',
            'patterns' => [
                [
                    'direction' => 1,
                    'name' => 'Прямо',
                    'stops' => [
                        [
                            'name' => 'Зупинка 1',
                            'cityCode' => 991,
                            'lat' => 50.042642,
                            'lon' => 32.942818,
                        ],
                        [
                            'name' => 'Зупинка 2',
                            'cityCode' => 992,
                            'lat' => 50.038582,
                            'lon' => 32.940063,
                        ],
                    ],
                    'trips' => [
                        [
                            'times' => ['08:00', '08:15'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
