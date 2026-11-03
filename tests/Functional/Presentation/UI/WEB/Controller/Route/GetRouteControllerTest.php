<?php

declare(strict_types=1);

namespace App\Tests\Functional\Presentation\UI\WEB\Controller\Route;

use App\Domain\Entity\Route\Route;
use App\Domain\Enums\Direction;
use App\Tests\AbstractWebTestCase;

class GetRouteControllerTest extends AbstractWebTestCase
{
    public function testInvoke(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $route = Route::create('1', 'Вокзал - Автопарк');
        $em->persist($route);

        $pattern = $route->createPattern(Direction::FORWARD, 'Прямий рейс');
        $em->persist($pattern);
        $em->flush();

        $client->request('GET', "/route/$route->id");

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'Вокзал - Автопарк');
        $this->assertSelectorTextContains('.route-badge', '№ 1');
        $this->assertSelectorTextContains('.pattern-card', 'Прямий рейс');
        $this->assertSelectorTextContains('.badge-forward', 'Прямо');
    }

    public function testInvokeWhenRouteNotFound(): void
    {
        $client = static::createClient();

        $client->request('GET', '/route/999');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'СТОРІНКУ НЕ ЗНАЙДЕНО');
        $this->assertSelectorTextContains('.badge', 'ПОМИЛКА 404');
    }
}
