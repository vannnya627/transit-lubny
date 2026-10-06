<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\WEB\Controller\Stop;

use App\Data\Entity\Stop;
use App\Data\ValueObjects\Coordinates;
use App\Tests\AbstractWebTestCase;

class GetStopControllerTest extends AbstractWebTestCase
{
    public function testInvoke(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        $stop = Stop::create('Школа №1', Coordinates::create(50.0, 32.0), 42);
        $em->persist($stop);
        $em->flush();

        $client->request('GET', "/stops/$stop->id");

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'Школа №1');
        $this->assertSelectorTextContains('.page-header', 'МІСЬКИЙ КОД: 42');
        $this->assertSelectorTextContains('.btn', 'НАЙБЛИЖЧІ РЕЙСИ');
    }

    public function testInvokeWhenStopNotFound(): void
    {
        $client = static::createClient();

        $client->request('GET', '/stops/999');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
        $this->assertSelectorTextContains('h1', 'СТОРІНКУ НЕ ЗНАЙДЕНО');
    }
}
