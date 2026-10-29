<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\Console\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class UploadRouteFromJsonControllerTest extends KernelTestCase
{
    private CommandTester $commandTester;
    private string $importDirectory;

    protected function setUp(): void
    {
        self::bootKernel();

        $application = new Application(self::$kernel);
        $command = $application->find('app:route-json:upload');

        $this->commandTester = new CommandTester($command);
        $this->importDirectory = (string) self::getContainer()->getParameter('app.import_dir');


        if (!is_dir($this->importDirectory)) {
            mkdir($this->importDirectory, 0777, true);
        }
    }

    public function testUploadRouteSuccessfully(): void
    {
        $testFile = $this->importDirectory . '/route_test.json';
        file_put_contents($testFile, json_encode([
            [
                'shortName' => '99',
                'longName' => 'Тестовий маршрут',
                'patterns' => [],
            ],
        ]));

        try {
            $this->commandTester->setInputs(['0']);

            $this->commandTester->execute([]);

            $this->commandTester->assertCommandIsSuccessful();
            $this->assertStringContainsString('Маршрут успішно завантажений!', $this->commandTester->getDisplay());
        } finally {
            if (file_exists($testFile)) {
                unlink($testFile);
            }
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
