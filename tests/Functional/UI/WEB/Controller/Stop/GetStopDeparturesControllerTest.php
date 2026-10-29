<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\WEB\Controller\Stop;

use App\Data\Entity\Route\Route;
use App\Data\Entity\Stop;
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

        $route = Route::create('5', 'Маршрут 5');
        $em->persist($route);

        $pattern = $route->createPattern(Direction::FORWARD, 'Центр - Ринок');
        $em->persist($pattern);

        $stop = Stop::create('Центральний Ринок', Coordinates::create(50.0, 32.0), 55);
        $em->persist($stop);
        $em->flush();

        $patternStop = $pattern->createPatternStop(1, $stop->id);
        $em->persist($patternStop);

        $trip = Trip::create($pattern->id, ServiceDays::everyday());
        $em->persist($trip);
        $em->flush();

        $client->request('GET', "/stops/$stop->id/departures");

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'НАЙБЛИЖЧІ ВІДПРАВЛЕННЯ');
        $this->assertSelectorTextContains('.page-header', 'Центральний Ринок');
        $this->assertSelectorTextContains('.page-header', '#55');
    }

    public function testInvokeWithFilters(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $stop = Stop::create('Тестова зупинка', Coordinates::create(50.0, 32.0), 1);
        $em->persist($stop);
        $em->flush();

        $client->request('GET', "/stops/$stop->id/departures", [
            'limit' => 15,
            'time' => '2026-10-05T15:00:00+03:00',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.page-header', 'Тестова зупинка');
    }

    public function testInvokeWithNegativeLimitFailsValidation(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $stop = Stop::create('Тестова зупинка', Coordinates::create(50.0, 32.0), 1);
        $em->persist($stop);
        $em->flush();

        $client->request('GET', "/stops/$stop->id/departures", ['limit' => -1]);

        $this->assertResponseStatusCodeSame(500);
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'ПОМИЛКА СЕРВЕРА');
    }

    public function testInvokeWhenStopNotFound(): void
    {
        $client = static::createClient();

        $client->request('GET', '/stops/999/departures');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'СТОРІНКУ НЕ ЗНАЙДЕНО');
    }
}
