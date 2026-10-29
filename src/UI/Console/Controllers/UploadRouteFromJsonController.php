<?php

declare(strict_types=1);

namespace App\UI\Console\Controllers;

use App\Actions\UploadRoute\UploadRouteFromJsonAction;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

#[AsCommand('app:route-json:upload')]
final readonly class UploadRouteFromJsonController
{
    public function __construct(
        #[Autowire(param: 'app.import_dir')]
        private string $importDirectory,
        private UploadRouteFromJsonAction $action,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(SymfonyStyle $io): int
    {
        /** @var list<string> $files */
        $files = $this->importDirectory
                |> scandir(...)
                |> (fn($x): array => array_diff($x, ['.', '..']))
                |> array_values(...);

        if (empty($files)) {
            $io->error('Папка порожня. Спочатку додайте файл маршруту.');

            return Command::FAILURE;
        }

        /** @var string $chosenFile */
        $chosenFile = $io->choice('Який файл маршруту хочете завантажити у БД?', $files);

        $this->action->run($this->importDirectory . '/' . $chosenFile);

        $io->success('Маршрут успішно завантажений! Дані записані в БД.');

        return Command::SUCCESS;
    }
}
