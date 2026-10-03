<?php

declare(strict_types=1);

namespace App\UI\WEB\Controllers\Route;

use App\Actions\Route\GetAllRouteAction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/', name: 'web_home', methods: ['GET'])]
#[Route(path: 'routes', name: 'web_all_routes', methods: ['GET'])]
final class GetAllRouteController extends AbstractController
{
    public function __construct(
        private readonly GetAllRouteAction $action,
    ) {}

    public function __invoke(): Response
    {
        $routeDTOs = $this->action->run();

        return $this->render('route/get_all_route.html.twig', ['routes' => $routeDTOs]);
    }
}
