<?php

declare(strict_types=1);

namespace App\UI\API\Controller;

use App\Actions\Route\GetPatternWithTripsAndStopsAction;
use App\Exceptions\DTO\ErrorResponseDTO;
use App\UI\API\Responses\PatternWithStopsAndTripsResponse;
use App\UI\API\Transformers\PatternWithStopsAndTripsTransformer;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag('Route')]
#[Route(path: 'api/v1/patterns/{id}/schedule', name: 'api_get_info_about_pattern', methods: ['GET'])]
final class GetPatternScheduleController extends AbstractController
{
    public function __construct(
        private readonly GetPatternWithTripsAndStopsAction $action,
        private readonly PatternWithStopsAndTripsTransformer $transformer,
    ) {}

    #[OA\Get(
        operationId: 'api_get_info_about_pattern',
        description: 'Отримання повної таблиці статичного розкладу для конкретного напрямку.',
        summary: 'Отримання списку розкладу',
    )]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(ref: new Model(type: PatternWithStopsAndTripsResponse::class)),
    )]
    #[OA\Response(
        response: 404,
        description: 'Pattern not found',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponseDTO::class)),
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'pattern id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    public function __invoke(int $id): JsonResponse
    {
        $patternSchedule = $this->action->run($id);

        return $this->json($this->transformer->transform($patternSchedule));
    }
}
