<?php

declare(strict_types=1);

namespace App\Tests\Functional\Presentation\UI\WEB\Controller\Stop;

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

        $client->request('GET', "/stops/$stop->id/routes");

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'Школа №1');
        $this->assertSelectorCount(1, '.route-card');
        $this->assertSelectorTextContains('.route-card', '№ 7');
        $this->assertSelectorTextContains('.route-card', 'Вокзал - Автопарк');
        $this->assertSelectorExists(sprintf('a[href="/route/%d"]', $route->id));
    }

    public function testInvokeWhenStopHasNoRoutes(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $stop = Stop::create('Зупинка без маршрутів', Coordinates::create(50.1, 32.1), 43);
        $em->persist($stop);
        $em->flush();

        $client->request('GET', "/stops/$stop->id/routes");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(0, '.route-card');
        $this->assertSelectorTextContains('.empty-state', 'маршрути не проходять');
        $this->assertSelectorExists(sprintf('a[href="/stops/%d/departures"]', $stop->id));
    }

    public function testInvokeWhenStopNotFound(): void
    {
        $client = static::createClient();

        $client->request('GET', '/stops/999/routes');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'СТОРІНКУ НЕ ЗНАЙДЕНО');
    }
}
