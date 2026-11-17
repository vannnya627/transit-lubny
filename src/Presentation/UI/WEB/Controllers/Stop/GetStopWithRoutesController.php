<?php

declare(strict_types=1);

namespace App\Presentation\UI\WEB\Controllers\Stop;

use App\Application\Actions\Stop\GetStopWithRoutesAction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'stops/{id}/routes', name: 'web_stop_with_routes', methods: ['GET'])]
final class GetStopWithRoutesController extends AbstractController
{
    public function __construct(
        private readonly GetStopWithRoutesAction $action,
    ) {}

    public function __invoke(int $id): Response
    {
        $stopWithRoutes = $this->action->run($id);

        return $this->render('stop/get_stop_with_routes.html.twig', [
            'stop' => $stopWithRoutes->stopDTO,
            'routes' => $stopWithRoutes->routesDTO,
        ]);
    }
}
