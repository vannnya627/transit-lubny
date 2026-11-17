<?php

declare(strict_types=1);

namespace App\Tests\Functional\Presentation\UI\API\Controller\Stop;

use App\Domain\Entity\Route\Route;
use App\Domain\Entity\Stop;
use App\Domain\Enums\Direction;
use App\Domain\ValueObjects\Coordinates;
use App\Tests\AbstractWebTestCase;

class GetStopWithRoutesControllerTest extends AbstractWebTestCase
{
    public function testInvoke(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $stop = Stop::create('Школа №1', Coordinates::create(50.0, 32.0), 42);
        $em->persist($stop);

        $route = Route::create('7', 'Вокзал - Автопарк');
        $em->persist($route);

        $pattern = $route->createPattern(Direction::FORWARD, 'Прямий рейс');
        $em->persist($pattern);
        $em->flush();

        $patternStop = $pattern->createPatternStop(1, $stop->id);
        $em->persist($patternStop);
        $em->flush();

        $client->request(
            'GET',
            "/api/v1/stops/$stop->id/routes",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('stop', $responseContent);
        $this->assertArrayHasKey('routes', $responseContent);

        $this->assertEquals($stop->id, $responseContent['stop']['id']);
        $this->assertEquals('Школа №1', $responseContent['stop']['name']);
        $this->assertEquals(50.0, $responseContent['stop']['lat']);
        $this->assertEquals(32.0, $responseContent['stop']['lon']);
        $this->assertEquals(42, $responseContent['stop']['cityCode']);

        $this->assertCount(1, $responseContent['routes']);
        $this->assertEquals($route->id, $responseContent['routes'][0]['id']);
        $this->assertEquals('7', $responseContent['routes'][0]['shortName']);
        $this->assertEquals('Вокзал - Автопарк', $responseContent['routes'][0]['longName']);
    }

    public function testInvokeWhenStopHasNoRoutes(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $stop = Stop::create('Зупинка без маршрутів', Coordinates::create(50.1, 32.1), 43);
        $em->persist($stop);
        $em->flush();

        $client->request(
            'GET',
            "/api/v1/stops/$stop->id/routes",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame([], $responseContent['routes']);
    }

    public function testInvokeWhenStopNotFound(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/v1/stops/999/routes',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
