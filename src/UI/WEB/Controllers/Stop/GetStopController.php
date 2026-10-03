<?php

declare(strict_types=1);

namespace App\UI\WEB\Controllers\Stop;

use App\Actions\Stop\GetStopAction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'stops/{id}', name: 'web_stop', methods: ['GET'])]
final class GetStopController extends AbstractController
{
    public function __construct(
        private readonly GetStopAction $action,
    ) {}

    public function __invoke(int $id): Response
    {
        $stopDTO = $this->action->run($id);

        return $this->render('stop/get_stop.html.twig', ['stop' => $stopDTO]);
    }
}
