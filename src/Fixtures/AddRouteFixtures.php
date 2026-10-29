<?php

declare(strict_types=1);

namespace App\Fixtures;

use App\Actions\UploadRoute\UploadRouteFromJsonAction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Throwable;

final class AddRouteFixtures extends Fixture
{
    public function __construct(
        private readonly UploadRouteFromJsonAction $uploadRouteFromJsonAction,
    ) {}

    /**
     * @throws Throwable
     */
    public function load(ObjectManager $manager): void
    {
        $files = glob(__DIR__ . '/data/*.json') ?: [];

        foreach ($files as $filePath) {
            $this->uploadRouteFromJsonAction->run($filePath);
            $manager->clear();
        }
    }
}
