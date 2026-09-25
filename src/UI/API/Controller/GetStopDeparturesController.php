<?php

declare(strict_types=1);

namespace App\UI\API\Controller;

use App\Actions\Stop\GetStopDeparturesAction;
use App\DTOs\Input\GetStopDeparturesInput;
use App\UI\API\Requests\DeparturesQueryParameter;
use App\UI\API\Responses\DepartureResponse;
use App\UI\API\Transformers\DepartureTransformer;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag('Stops')]
#[Route(path: 'api/v1/stops/{id}/departures', name: 'api_stop_departures', methods: ['GET'])]
final class GetStopDeparturesController extends AbstractController
{
    public function __construct(
        private readonly GetStopDeparturesAction $action,
        private readonly DepartureTransformer $transformer,
    ) {}

    #[OA\Get(
        operationId: 'api_stop_departures',
        description: 'Отримання найближчіх рейсів на конкретну зупинку',
        summary: 'Отримання найближчіх рейсів на зупинку',
    )]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(type: DepartureResponse::class),
            ),
        ),
    )]
    public function __invoke(int $id, #[MapQueryString] DeparturesQueryParameter $parameter): JsonResponse
    {

        $departuresDTOs = $this->action->run(
            new GetStopDeparturesInput(
                stopId: $id,
                time: $parameter->time,
                limit: $parameter->limit,
            ),
        );

        return $this->json(array_map($this->transformer->transform(...), $departuresDTOs));
    }
}
