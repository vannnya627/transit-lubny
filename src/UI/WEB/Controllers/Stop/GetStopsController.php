<?php

declare(strict_types=1);

namespace App\UI\WEB\Controllers\Stop;

use App\Actions\Stop\GetStopsAction;
use App\UI\WEB\Requests\StopQueryParameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'stops', name: 'web_stops', methods: ['GET'])]
final class GetStopsController extends AbstractController
{
    public function __construct(
        private readonly GetStopsAction $action,
    ) {}

    public function __invoke(#[MapQueryString] StopQueryParameter $parameterStop): Response
    {
        $stopDTOs = $this->action->run($parameterStop->toInput());

        return $this->render('stop/get_stops.html.twig', ['stops' => $stopDTOs]);
    }
}
