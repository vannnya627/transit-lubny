<?php

declare(strict_types=1);

namespace App\Presentation\UI\API\Controller\Trip;

use App\Application\Actions\Trip\GetTripAction;
use App\Application\DTOs\Inputs\GetTripInput;
use App\Presentation\Exceptions\DTOs\ErrorResponseDTO;
use App\Presentation\UI\API\Responses\TripDetailsResponse;
use App\Presentation\UI\API\Transformers\TripDetailsTransformer;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag('Trips')]
#[Route(path: 'api/v1/trips/{id}', name: 'api_trips', methods: ['GET'])]
final class GetTripController extends AbstractController
{
    public function __construct(
        private readonly GetTripAction $action,
        private readonly TripDetailsTransformer $transformer,
    ) {}

    #[OA\Get(
        operationId: 'api_trips',
        description: 'Отримання конкретного рейсу за id',
        summary: 'Отримання рейсу',
    )]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(ref: new Model(type: TripDetailsResponse::class)),
    )]
    #[OA\Response(
        response: 404,
        description: 'Trip not found',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponseDTO::class)),
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'trip id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    public function __invoke(int $id): JsonResponse
    {
        $tripDetailsDto = $this->action->run(new GetTripInput($id));

        return $this->json($this->transformer->transform($tripDetailsDto));
    }
}
