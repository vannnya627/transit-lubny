<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\API\Controller\Route;

use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Route\Route;
use App\Data\Entity\Stop;
use App\Data\Entity\Trip\Trip;
use App\Data\ValueObjects\Coordinates;
use App\Data\ValueObjects\ServiceDays;
use App\Enum\Direction;
use App\Tests\AbstractWebTestCase;

class GetPatternScheduleControllerTest extends AbstractWebTestCase
{
    public function testInvoke(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $route = Route::create(shortName: '1', longName: 'test');
        $em->persist($route);

        $pattern = $route->createPattern(Direction::FORWARD, 'test');
        $em->persist($pattern);

        $stop = Stop::create(
            name: 'test',
            coordinates: Coordinates::create(50.0, 32.0),
            cityCode: 1,
        );
        $em->persist($stop);
        $em->flush();

        $patternStop = $pattern->createPatternStop(1, $stop->id);
        $em->persist($patternStop);

        $trip = Trip::create(
            patternId: $pattern->id,
            serviceDays: ServiceDays::weekdaysOnly(),
        );
        $em->persist($trip);
        $em->flush();

        $client->request(
            'GET',
            "/api/v1/patterns/$pattern->id/schedule",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('id', $responseContent);
        $this->assertEquals($pattern->id, $responseContent['id']);
        $this->assertEquals('Прямо', $responseContent['direction']);
        $this->assertEquals('test', $responseContent['name']);

        $this->assertIsArray($responseContent['stops']);
        $this->assertCount(1, $responseContent['stops']);

        $this->assertIsArray($responseContent['trips']);
        $this->assertCount(1, $responseContent['trips']);

        $db = $em->getRepository(Pattern::class)->find($pattern->id);
        $this->assertNotNull($db);
    }

    public function testInvokeWhenPatternNotFound(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/v1/patterns/999/schedule',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
