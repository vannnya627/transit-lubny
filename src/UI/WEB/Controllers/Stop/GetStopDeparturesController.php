<?php

declare(strict_types=1);

namespace App\UI\WEB\Controllers\Stop;

use App\Actions\Stop\GetStopDeparturesAction;
use App\UI\WEB\Requests\QueryParameters\DeparturesQueryParameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'stops/{id}/departures', name: 'web_stop_departures', methods: ['GET'])]
final class GetStopDeparturesController extends AbstractController
{
    public function __construct(
        private readonly GetStopDeparturesAction $action,
    ) {}

    public function __invoke(int $id, #[MapQueryString] DeparturesQueryParameter $parameter): Response
    {
        $stopWithDepartures = $this->action->run($parameter->toInput($id));

        return $this->render('stop/get_stop_departures.html.twig', ['departures' => $stopWithDepartures]);
    }
}
