<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\API\Controller\Stop;

use App\Data\Entity\Stop;
use App\Data\ValueObjects\Coordinates;
use App\Tests\AbstractWebTestCase;

class GetStopsControllerTest extends AbstractWebTestCase
{
    public function testInvoke(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $stop = Stop::create(
            name: 'test',
            coordinates: Coordinates::create(50.0, 32.0),
            cityCode: 1,
        );
        $em->persist($stop);
        $em->flush();

        $client->request(
            'GET',
            '/api/v1/stops',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseContent);
        $this->assertNotEmpty($responseContent);

        $this->assertArrayHasKey('id', $responseContent[0]);
        $this->assertEquals($stop->id, $responseContent[0]['id']);
        $this->assertEquals('test', $responseContent[0]['name']);
        $this->assertEquals(50.0, $responseContent[0]['lat']);
        $this->assertEquals(32.0, $responseContent[0]['lon']);
        $this->assertEquals(1, $responseContent[0]['cityCode']);

        $db = $em->getRepository(Stop::class)->findAll();
        $this->assertNotEmpty($db);
    }

    public function testInvokeWhenArrayIsEmpty(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/v1/stops',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertEquals([], $responseContent);
    }

    public function testInvokeWithSearchQuery(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $stopTarget = Stop::create('Центральна', Coordinates::create(50.0, 32.0), 1);
        $stopOther = Stop::create('Вокзал', Coordinates::create(50.1, 32.1), 2);
        $em->persist($stopTarget);
        $em->persist($stopOther);
        $em->flush();

        $client->request(
            'GET',
            '/api/v1/stops',
            ['q' => 'Центр'],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseContent);
        $this->assertCount(1, $responseContent);
        $this->assertEquals('Центральна', $responseContent[0]['name']);
        $this->assertEquals($stopTarget->id, $responseContent[0]['id']);
    }

    public function testInvokeWithCoordinatesAndRadius(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $nearStop = Stop::create('Близька', Coordinates::create(50.0, 32.0), 1);
        $farStop = Stop::create('Далека', Coordinates::create(50.5, 32.0), 2);
        $em->persist($nearStop);
        $em->persist($farStop);
        $em->flush();

        $client->request(
            'GET',
            '/api/v1/stops',
            [
                'lat' => 50.0,
                'lon' => 32.0,
                'radius' => 500,
            ],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseContent);
        $this->assertCount(1, $responseContent);
        $this->assertEquals('Близька', $responseContent[0]['name']);
    }

    public function testInvokeWithMissingLongitudeFailsValidation(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/v1/stops',
            ['lat' => 50.0],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseStatusCodeSame(422);

        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('context', $responseContent);
        $this->assertArrayHasKey('lat', $responseContent['context']);
    }

    public function testInvokeWithInvalidRadiusFailsValidation(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/v1/stops',
            ['radius' => -100],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseStatusCodeSame(422);
    }
}
