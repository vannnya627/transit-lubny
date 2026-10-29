<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\API\Controller\Route;

use App\Data\Entity\Route\Route;
use App\Enum\Direction;
use App\Tests\AbstractWebTestCase;

class GetRouteControllerTest extends AbstractWebTestCase
{
    public function testInvoke(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $route = Route::create('1', 'test');
        $pattern = $route->createPattern(Direction::FORWARD, 'test pattern');

        $em->persist($route);
        $em->persist($pattern);
        $em->flush();

        $client->request(
            'GET',
            "/api/v1/route/$route->id",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('id', $responseContent);
        $this->assertEquals($route->id, $responseContent['id']);
        $this->assertEquals('1', $responseContent['shortName']);
        $this->assertEquals('test', $responseContent['longName']);
        $this->assertArrayHasKey('patterns', $responseContent);
        $this->assertCount(1, $responseContent['patterns']);
        $this->assertEquals($pattern->id, $responseContent['patterns'][0]['id']);
        $this->assertEquals('Прямо', $responseContent['patterns'][0]['direction']);
        $this->assertEquals('test pattern', $responseContent['patterns'][0]['name']);

        $routeDb = $em->getRepository(Route::class)->find($responseContent['id']);
        $this->assertNotNull($routeDb);
        $this->assertEquals('1', $routeDb->shortName);
    }

    public function testInvokeWhenRouteNotFound(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/v1/route/999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
