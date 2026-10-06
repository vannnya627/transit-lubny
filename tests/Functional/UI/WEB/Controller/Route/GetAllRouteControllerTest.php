<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\WEB\Controller\Route;

use App\Data\Entity\Route\Route;
use App\Tests\AbstractWebTestCase;

class GetAllRouteControllerTest extends AbstractWebTestCase
{
    public function testInvoke(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $route = Route::create('1', 'test-route');
        $em->persist($route);
        $em->flush();

        $client->request('GET', '/routes');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'МАРШРУТИ ЛУБЕН');
        $this->assertSelectorTextContains('.route-badge', '№ 1');
        $this->assertSelectorTextContains('.route-title', 'test-route');

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'МАРШРУТИ ЛУБЕН');
    }

    public function testInvokeWhenArrayIsEmpty(): void
    {
        $client = static::createClient();

        $client->request('GET', '/routes');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.empty-state');
        $this->assertSelectorTextContains('.empty-state', 'Маршрутів не знайдено');
    }
}
