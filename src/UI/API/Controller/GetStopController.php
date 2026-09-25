<?php

declare(strict_types=1);

namespace App\UI\API\Controller;

use App\Actions\Stop\GetStopAction;
use App\Exceptions\DTO\ErrorResponseDTO;
use App\UI\API\Responses\StopResponse;
use App\UI\API\Transformers\StopTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag('Stops')]
#[Route(path: 'api/v1/stops/{id}', name: 'api_stop', methods: ['GET'])]
final class GetStopController extends AbstractController
{
    public function __construct(
        private readonly GetStopAction $action,
        private readonly StopTransformer $transformer,
    ) {}

    #[OA\Get(
        operationId: 'api_stop',
        description: 'Отримання зупинки за id',
        summary: 'Отримання зупинки',
    )]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(ref: new Model(type: StopResponse::class)),
    )]
    #[OA\Response(
        response: 404,
        description: 'Stop not found',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponseDTO::class)),
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'stop id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    public function __invoke(int $id): JsonResponse
    {
        $stopDTO = $this->action->run($id);

        return $this->json($this->transformer->transform($stopDTO));
    }
}
