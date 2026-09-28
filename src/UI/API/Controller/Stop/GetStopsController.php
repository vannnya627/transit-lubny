<?php

declare(strict_types=1);

namespace App\UI\API\Controller\Stop;

use App\Actions\Stop\GetStopsAction;
use App\DTOs\Input\SearchStopsInput;
use App\UI\API\Requests\StopQueryParameter;
use App\UI\API\Responses\StopResponse;
use App\UI\API\Transformers\StopTransformer;
use App\ValueObjects\Coordinates;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag('Stops')]
#[Route(path: 'api/v1/stops', name: 'api_stops', methods: ['GET'])]
final class GetStopsController extends AbstractController
{
    public function __construct(
        private readonly GetStopsAction $action,
        private readonly StopTransformer $transformer,
    ) {}

    #[OA\Get(
        operationId: 'api_stops',
        description: 'Отримання усіх зупинок або конкретний по фільтрах',
        summary: 'Отримання списку зупинок',
    )]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(type: StopResponse::class),
            ),
        ),
    )]
    public function __invoke(#[MapQueryString] StopQueryParameter $parameterStop): JsonResponse
    {
        // todo - бо так або передавати у SearchStopsInput просто lat lon
        $coordinates = (null !== $parameterStop->lat && null !== $parameterStop->lon)
            ? Coordinates::create($parameterStop->lat, $parameterStop->lon)
            : null;

        $stopDTOs = $this->action->run(
            new SearchStopsInput(
                q: $parameterStop->q,
                coordinates: $coordinates,
                radius: $parameterStop->radius,
            ),
        );

        return $this->json(array_map($this->transformer->transform(...), $stopDTOs));
    }
}
