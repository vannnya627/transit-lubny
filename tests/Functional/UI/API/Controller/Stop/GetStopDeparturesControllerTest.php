<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\API\Controller\Stop;

use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Route\PatternStop;
use App\Data\Entity\Route\Route;
use App\Data\Entity\Stop;
use App\Data\Entity\Trip\StopTime;
use App\Data\Entity\Trip\Trip;
use App\Data\ValueObjects\Coordinates;
use App\Data\ValueObjects\ServiceDays;
use App\Enum\Direction;
use App\Tests\AbstractWebTestCase;

class GetStopDeparturesControllerTest extends AbstractWebTestCase
{
    public function testInvoke(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $route = Route::create(shortName: '1', longName: 'test');
        $em->persist($route);

        $pattern = Pattern::create(
            route: $route,
            direction: Direction::FORWARD,
            name: 'test',
        );
        $route->addPattern($pattern);
        $em->persist($pattern);

        $stop = Stop::create(
            name: 'test',
            coordinates: Coordinates::create(50.0, 32.0),
            cityCode: 1,
        );
        $em->persist($stop);
        $em->flush();

        $patternStop = PatternStop::create(
            pattern: $pattern,
            stopSequence: 1,
            stopId: $stop->id,
        );
        $pattern->addPatternStop($patternStop);
        $em->persist($patternStop);

        $trip = Trip::create(
            patternId: $pattern->id,
            serviceDays: ServiceDays::everyday(),
        );
        $em->persist($trip);

        $stopTime = StopTime::create(
            trip: $trip,
            stopSequence: 1,
            stopId: $stop->id,
            arrivalTime: 86399,
            isEstimate: false,
        );
        $trip->addStopTime($stopTime);
        $em->persist($stopTime);
        $em->flush();

        $client->request(
            'GET',
            "/api/v1/stops/$stop->id/departures",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('stopId', $responseContent);
        $this->assertArrayHasKey('stopName', $responseContent);
        $this->assertArrayHasKey('cityCode', $responseContent);
        $this->assertArrayHasKey('departures', $responseContent);

        $this->assertEquals($stop->id, $responseContent['stopId']);
        $this->assertEquals('test', $responseContent['stopName']);
        $this->assertEquals(1, $responseContent['cityCode']);
        $this->assertIsArray($responseContent['departures']);
        $this->assertCount(1, $responseContent['departures']);

        $departure = $responseContent['departures'][0];
        $this->assertEquals($trip->id, $departure['tripId']);
        $this->assertEquals('1', $departure['route']['shortName']);
        $this->assertEquals('test', $departure['headsign']);
        $this->assertEquals('23:59', $departure['arrivalTime']);
        $this->assertIsInt($departure['expectedInMinutes']);

        $db = $em->getRepository(Stop::class)->find($stop->id);
        $this->assertNotNull($db);
    }

    public function testInvokeWithValidLimitAndQueryParameters(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $stop = Stop::create('test', Coordinates::create(50.0, 32.0), 1);
        $em->persist($stop);
        $em->flush();

        $client->request(
            'GET',
            "/api/v1/stops/$stop->id/departures",
            [
                'limit' => 5,
                'time' => '2026-10-05T15:00:00+03:00',
            ],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseContent);
        $this->assertEquals($stop->id, $responseContent['stopId']);
        $this->assertIsArray($responseContent['departures']);
    }

    public function testInvokeWithNegativeLimitFailsValidation(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $stop = Stop::create('test', Coordinates::create(50.0, 32.0), 1);
        $em->persist($stop);
        $em->flush();

        // #[Assert\Positive]
        $client->request(
            'GET',
            "/api/v1/stops/$stop->id/departures",
            ['limit' => -1],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseStatusCodeSame(422);

        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('context', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent['context']);
    }

    public function testInvokeWithLimitExceedingMaxFailsValidation(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $stop = Stop::create('test', Coordinates::create(50.0, 32.0), 1);
        $em->persist($stop);
        $em->flush();

        // #[Assert\LessThanOrEqual(100)]
        $client->request(
            'GET',
            "/api/v1/stops/$stop->id/departures",
            ['limit' => 150],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseStatusCodeSame(422);

        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('context', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent['context']);
    }

    public function testInvokeWhenStopNotFound(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/v1/stops/999/departures',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
