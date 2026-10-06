<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\WEB\Controller\Trip;

use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Route\Route;
use App\Data\Entity\Trip\Trip;
use App\Data\ValueObjects\ServiceDays;
use App\Enum\Direction;
use App\Tests\AbstractWebTestCase;

class GetTripControllerTest extends AbstractWebTestCase
{
    public function testInvoke(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $route = Route::create('3', 'Маршрут 3');
        $em->persist($route);

        $pattern = Pattern::create($route, Direction::FORWARD, 'Залізничний вокзал - Центр');
        $route->addPattern($pattern);
        $em->persist($pattern);
        $em->flush();

        $trip = Trip::create($pattern->id, ServiceDays::everyday());
        $em->persist($trip);
        $em->flush();

        $client->request('GET', "/trips/$trip->id");

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'Залізничний вокзал - Центр');
        $this->assertSelectorTextContains('.route-badge', '№ 3');
        $this->assertSelectorTextContains('.page-header, .brut-box', "РЕЙС #$trip->id");
    }

    public function testInvokeWhenTripNotFound(): void
    {
        $client = static::createClient();

        $client->request('GET', '/trips/999');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'СТОРІНКУ НЕ ЗНАЙДЕНО');
    }
}
