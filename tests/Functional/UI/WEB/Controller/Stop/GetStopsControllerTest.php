<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\WEB\Controller\Stop;

use App\Data\Entity\Stop;
use App\Data\ValueObjects\Coordinates;
use App\Tests\AbstractWebTestCase;

class GetStopsControllerTest extends AbstractWebTestCase
{
    public function testInvoke(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $stop = Stop::create('Автовокзал', Coordinates::create(50.0, 32.0), 10);
        $em->persist($stop);
        $em->flush();

        $client->request('GET', '/stops');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'ЗУПИНКИ ТРАНСПОРТУ');
        $this->assertSelectorTextContains('.stop-name', 'Автовокзал');
        $this->assertSelectorTextContains('.badge', '#10');
    }

    public function testInvokeWithSearchQuery(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $stop1 = Stop::create('Центральна', Coordinates::create(50.0, 32.0), 1);
        $stop2 = Stop::create('Вокзал', Coordinates::create(50.1, 32.1), 2);
        $em->persist($stop1);
        $em->persist($stop2);
        $em->flush();

        $client->request('GET', '/stops', ['q' => 'Центр']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.stop-name', 'Центральна');
        $this->assertSelectorTextContains('body', 'Запит: "Центр"');
    }

    public function testInvokeWithCoordinatesAndRadius(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $nearStop = Stop::create('Близька зупинка', Coordinates::create(50.0, 32.0), 1);
        $farStop = Stop::create('Далека зупинка', Coordinates::create(50.6, 32.0), 2);
        $em->persist($nearStop);
        $em->persist($farStop);
        $em->flush();

        $client->request('GET', '/stops', [
            'lat' => 50.0,
            'lon' => 32.0,
            'radius' => 500,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.stop-name', 'Близька зупинка');
    }

    public function testInvokeWithCombinedSearchAndCoordinates(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $stop1 = Stop::create('Центральна міська', Coordinates::create(50.0, 32.0), 1);
        $stop2 = Stop::create('Центральна заміська', Coordinates::create(50.6, 32.0), 2);
        $em->persist($stop1);
        $em->persist($stop2);
        $em->flush();

        $client->request('GET', '/stops', [
            'q' => 'Центральна',
            'lat' => 50.0,
            'lon' => 32.0,
            'radius' => 1000,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.stop-name', 'Центральна міська');
    }

    public function testInvokeWithMissingLongitudeFailsValidation(): void
    {
        $client = static::createClient();

        $client->request('GET', '/stops', ['lat' => 50.0]);

        $this->assertResponseStatusCodeSame(500);
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'ПОМИЛКА СЕРВЕРА');
    }

    public function testInvokeWithInvalidRadiusFailsValidation(): void
    {
        $client = static::createClient();

        // Негативний радіус порушує #[Assert\Positive]
        $client->request('GET', '/stops', ['radius' => -100]);

        $this->assertResponseStatusCodeSame(500);
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'ПОМИЛКА СЕРВЕРА');
    }

    public function testInvokeWhenEmpty(): void
    {
        $client = static::createClient();

        $client->request('GET', '/stops');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.empty-state');
        $this->assertSelectorTextContains('.empty-state', 'зупинок не знайдено');
    }
}
