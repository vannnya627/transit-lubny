<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Caching\Keys;

use App\Application\Caching\Keys\SearchStopsCacheKey;
use App\Application\DTOs\Inputs\SearchStopsInput;
use App\Domain\ValueObjects\Coordinates;
use App\Tests\AbstractTestCase;

class SearchStopsCacheKeyTest extends AbstractTestCase
{
    public function testQueryOnly(): void
    {
        $key = SearchStopsCacheKey::fromInput(new SearchStopsInput(q: 'Лікарня'));

        self::assertSame(
            'stops.v1.q.' . hash('xxh3', 'лікарня'),
            $key->toString(),
        );
    }

    public function testQueryIsNormalized(): void
    {
        $a = SearchStopsCacheKey::fromInput(new SearchStopsInput(q: ' ЛІКАРНЯ '));
        $b = SearchStopsCacheKey::fromInput(new SearchStopsInput(q: 'лікарня'));

        self::assertSame($a->toString(), $b->toString());
    }

    public function testEmptyQueryMeansAll(): void
    {
        self::assertSame(
            'stops.v1.q.all',
            SearchStopsCacheKey::fromInput(new SearchStopsInput())->toString(),
        );
        self::assertSame(
            'stops.v1.q.all',
            SearchStopsCacheKey::fromInput(new SearchStopsInput(q: '   '))->toString(),
        );
    }

    public function testGeoDiffersByCoordinatesAndRadius(): void
    {
        $base = SearchStopsCacheKey::fromInput(new SearchStopsInput(
            coordinates: Coordinates::create(50.0, 32.0),
            radius: 500,
        ));

        $otherLat = SearchStopsCacheKey::fromInput(new SearchStopsInput(
            coordinates: Coordinates::create(50.1, 32.0),
            radius: 500,
        ));

        $otherRadius = SearchStopsCacheKey::fromInput(new SearchStopsInput(
            coordinates: Coordinates::create(50.0, 32.0),
            radius: 1000,
        ));

        self::assertNotSame($base->toString(), $otherLat->toString());
        self::assertNotSame($base->toString(), $otherRadius->toString());
    }

    public function testGeoAndQueryHaveDifferentNamespaces(): void
    {
        $geo = SearchStopsCacheKey::fromInput(new SearchStopsInput(
            coordinates: Coordinates::create(50.0, 32.0),
        ));
        $q = SearchStopsCacheKey::fromInput(new SearchStopsInput(q: 'test'));

        self::assertStringStartsWith('stops.v1.geo.', $geo->toString());
        self::assertStringStartsWith('stops.v1.q.', $q->toString());
    }
}
