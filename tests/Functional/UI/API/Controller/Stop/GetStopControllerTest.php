<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\API\Controller\Stop;

use App\Data\Entity\Stop;
use App\Data\ValueObjects\Coordinates;
use App\Tests\AbstractWebTestCase;

class GetStopControllerTest extends AbstractWebTestCase
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
            "/api/v1/stops/$stop->id",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseIsSuccessful();

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('id', $responseContent);
        $this->assertEquals($stop->id, $responseContent['id']);
        $this->assertEquals('test', $responseContent['name']);
        $this->assertEquals(50.0, $responseContent['lat']);
        $this->assertEquals(32.0, $responseContent['lon']);
        $this->assertEquals(1, $responseContent['cityCode']);

        $db = $em->getRepository(Stop::class)->find($stop->id);
        $this->assertNotNull($db);
    }

    public function testInvokeWhenStopNotFound(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/v1/stops/999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
