<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Caching\Keys;

use App\Application\Caching\Keys\GetStopDeparturesCacheKey;
use App\Application\DTOs\Inputs\GetStopDeparturesInput;
use App\Domain\ValueObjects\TimeOfDay;
use App\Tests\AbstractTestCase;

class GetStopDeparturesCacheKeyTest extends AbstractTestCase
{
    public function testSameMinuteProducesSameKey(): void
    {
        $a = GetStopDeparturesCacheKey::fromInput(
            new GetStopDeparturesInput(1, TimeOfDay::fromSeconds(43200)),
        );
        $b = GetStopDeparturesCacheKey::fromInput(
            new GetStopDeparturesInput(1, TimeOfDay::fromSeconds(43259)),
        );

        self::assertSame($a->toString(), $b->toString());
        self::assertSame(
            'departures.v1.stop.1.time.43200.limit.10',
            $a->toString(),
        );
    }

    public function testMinuteBoundaryProducesDifferentKey(): void
    {
        $a = GetStopDeparturesCacheKey::fromInput(
            new GetStopDeparturesInput(1, TimeOfDay::fromSeconds(43259)),
        );
        $b = GetStopDeparturesCacheKey::fromInput(
            new GetStopDeparturesInput(1, TimeOfDay::fromSeconds(43260)),
        );

        self::assertNotSame($a->toString(), $b->toString());
    }

    public function testStopIdAndLimitAffectKey(): void
    {
        $base = GetStopDeparturesCacheKey::fromInput(
            new GetStopDeparturesInput(1, TimeOfDay::fromSeconds(43200), 10),
        );

        $otherStop = GetStopDeparturesCacheKey::fromInput(
            new GetStopDeparturesInput(2, TimeOfDay::fromSeconds(43200), 10),
        );

        $otherLimit = GetStopDeparturesCacheKey::fromInput(
            new GetStopDeparturesInput(1, TimeOfDay::fromSeconds(43200), 5),
        );

        self::assertNotSame($base->toString(), $otherStop->toString());
        self::assertNotSame($base->toString(), $otherLimit->toString());
    }
}
