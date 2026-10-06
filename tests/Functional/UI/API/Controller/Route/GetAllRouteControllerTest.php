<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\API\Controller\Route;

use App\Data\Entity\Route\Route;
use App\Tests\AbstractWebTestCase;

class GetAllRouteControllerTest extends AbstractWebTestCase
{
    public function testInvoke(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $route = Route::create('1', 'test');
        $em->persist($route);
        $em->flush();

        $client->request(
            'GET',
            '/api/v1/routes',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertArrayHasKey('id', $responseContent[0]);
        $this->assertEquals('1', $responseContent[0]['shortName']);
        $this->assertEquals('test', $responseContent[0]['longName']);

        $routeDb = $em->getRepository(Route::class)->find($responseContent[0]['id']);
        $this->assertNotNull($routeDb);
        $this->assertEquals('1', $routeDb->shortName);
    }

    public function testInvokeWhenArrayIsEmpty(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/v1/routes',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertEquals([], $responseContent);
    }

    public function testInvokeWhenEndpointIsNotFound(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/v1/bad-route',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
