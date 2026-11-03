<?php

declare(strict_types=1);

namespace App\Tests\Functional\Presentation\UI\API\Controller\Trip;

use App\Domain\Entity\Route\Route;
use App\Domain\Entity\Stop;
use App\Domain\Entity\Trip\Trip;
use App\Domain\Enums\Direction;
use App\Domain\ValueObjects\Coordinates;
use App\Domain\ValueObjects\ServiceDays;
use App\Domain\ValueObjects\TimeOfDay;
use App\Tests\AbstractWebTestCase;

class GetTripControllerTest extends AbstractWebTestCase
{
    public function testInvoke(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $route = Route::create('1', 'test');
        $em->persist($route);

        $pattern = $route->createPattern(Direction::FORWARD, 'test');
        $em->persist($pattern);

        $stop = Stop::create(
            name: 'Stop 1',
            coordinates: Coordinates::create(50.0, 32.0),
            cityCode: 1,
        );
        $em->persist($stop);
        $em->flush();

        $trip = Trip::create(
            patternId: $pattern->id,
            serviceDays: ServiceDays::weekendsOnly(),
        );
        $em->persist($trip);
        $em->flush();

        $stopTime = $trip->createStopTime(
            stopSequence: 1,
            stopId: $stop->id,
            arrivalTime: TimeOfDay::fromSeconds(36000),
            isEstimate: false,
        );

        $em->persist($stopTime);
        $em->flush();

        $client->request(
            'GET',
            "/api/v1/trips/$trip->id",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('tripId', $responseContent);
        $this->assertEquals($trip->id, $responseContent['tripId']);
        $this->assertArrayHasKey('route', $responseContent);
        $this->assertArrayHasKey('headsign', $responseContent);
        $this->assertArrayHasKey('stops', $responseContent);
        $this->assertCount(1, $responseContent['stops']);

        $stopData = $responseContent['stops'][0];
        $this->assertEquals(1, $stopData['sequence']);
        $this->assertEquals($stop->id, $stopData['stop']['id']);
        $this->assertEquals('Stop 1', $stopData['stop']['name']);
        $this->assertEquals('10:00', $stopData['arrivalTime']);
        $this->assertIsBool($stopData['isPassed']);

        $routeData = $responseContent['route'];
        $this->assertIsArray($routeData);
        $this->assertEquals('1', $routeData['shortName']);

        $dbTrip = $em->getRepository(Trip::class)->find($trip->id);
        $this->assertNotNull($dbTrip);
    }

    public function testInvokeWhenTripNotFound(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/v1/trips/999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
