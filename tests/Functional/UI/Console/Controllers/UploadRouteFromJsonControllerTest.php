<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\Console\Controllers;

use App\Data\Entity\Route\Route;
use App\Data\Entity\Stop;
use App\Data\Entity\Trip\Trip;
use App\Enum\Direction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class UploadRouteFromJsonControllerTest extends KernelTestCase
{
    private CommandTester $commandTester;
    private EntityManagerInterface $entityManager;
    private string $importDirectory;

    private const string SOURCE_FILE = 'tests/data/route_test.json';

    protected function setUp(): void
    {
        self::bootKernel();

        $application = new Application(self::$kernel);
        $command = $application->find('app:route-json:upload');

        $this->commandTester = new CommandTester($command);
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->importDirectory = (string) self::getContainer()->getParameter('app.import_dir');

        if (!is_dir($this->importDirectory)) {
            mkdir($this->importDirectory, 0777, true);
        }
    }

    public function testUploadRouteSuccessfully(): void
    {
        $this->clearImportDirectory();

        $targetFile = $this->importDirectory . '/route_test.json';
        copy(self::SOURCE_FILE, $targetFile);

        try {
            $this->commandTester->setInputs(['route_test.json']);
            $this->commandTester->execute([]);

            $this->commandTester->assertCommandIsSuccessful();
            $this->assertStringContainsString('Маршрут успішно завантажений!', $this->commandTester->getDisplay());

            /** @var Route|null $route */
            $route = $this->entityManager->getRepository(Route::class)->findOneBy(['shortName' => '99']);
            $this->assertNotNull($route);
            $this->assertSame('Тестовий автомаш - Тестові терни', $route->longName);
            $this->assertCount(1, $route->patterns);

            $pattern = $route->patterns->first();
            $this->assertNotNull($pattern);
            $this->assertSame(Direction::FORWARD, $pattern->direction);
            $this->assertCount(2, $pattern->patternStops);

            /** @var Stop|null $stop1 */
            $stop1 = $this->entityManager->getRepository(Stop::class)->findOneBy(['cityCode' => 991]);
            $this->assertNotNull($stop1);
            $this->assertSame('Зупинка 1', $stop1->name);

            /** @var Trip|null $trip */
            $trip = $this->entityManager->getRepository(Trip::class)->findOneBy(['patternId' => $pattern->id]);
            $this->assertNotNull($trip);
            $this->assertCount(2, $trip->stopTimes);
        } finally {
            $this->clearImportDirectory();
        }
    }

    public function testUploadRouteOverwritesExistingRoute(): void
    {
        $this->clearImportDirectory();

        $targetFile = $this->importDirectory . '/route_test.json';
        copy(self::SOURCE_FILE, $targetFile);

        try {
            $this->commandTester->setInputs(['route_test.json']);
            $this->commandTester->execute([]);
            $this->commandTester->assertCommandIsSuccessful();

            $this->commandTester->setInputs(['route_test.json']);
            $this->commandTester->execute([]);
            $this->commandTester->assertCommandIsSuccessful();

            $routes = $this->entityManager->getRepository(Route::class)->findBy(['shortName' => '99']);
            $this->assertCount(1, $routes);
        } finally {
            $this->clearImportDirectory();
        }
    }

    public function testFailsWhenImportDirectoryIsEmpty(): void
    {
        $this->clearImportDirectory();

        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Папка порожня. Спочатку додайте файл маршруту.', $this->commandTester->getDisplay());
    }

    private function clearImportDirectory(): void
    {
        $files = glob($this->importDirectory . '/*') ?: [];
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
