<?php

declare(strict_types=1);

namespace App\UI\WEB\Controllers\Route;

use App\Actions\Route\GetRouteAction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'route/{id}', name: 'web_get_route', methods: ['GET'])]
final class GetRouteController extends AbstractController
{
    public function __construct(
        private readonly GetRouteAction $action,
    ) {}

    public function __invoke(int $id): Response
    {
        $routeDTO = $this->action->run($id);

        return $this->render('route/get_route.html.twig', ['route' => $routeDTO]);
    }
}
