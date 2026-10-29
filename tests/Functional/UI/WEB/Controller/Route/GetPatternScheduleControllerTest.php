<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\WEB\Controller\Route;

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

        $route = Route::create('1', 'Тестовий маршрут');
        $em->persist($route);

        $pattern = $route->createPattern(Direction::FORWARD, 'Напрямок розкладу');
        $em->persist($pattern);

        $stop = Stop::create('Зупинка 1', Coordinates::create(50.0, 32.0), 1);
        $em->persist($stop);
        $em->flush();

        $patternStop = $pattern->createPatternStop(1, $stop->id);
        $em->persist($patternStop);

        $trip = Trip::create($pattern->id, ServiceDays::everyday());
        $em->persist($trip);
        $em->flush();

        $client->request('GET', "/patterns/$pattern->id/schedule");

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'Напрямок розкладу');
        $this->assertSelectorExists('.brut-table');
        $this->assertSelectorTextContains('tbody .col-stop', 'Зупинка 1');
    }

    public function testInvokeWhenPatternNotFound(): void
    {
        $client = static::createClient();

        $client->request('GET', '/patterns/999/schedule');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'СТОРІНКУ НЕ ЗНАЙДЕНО');
    }
}
