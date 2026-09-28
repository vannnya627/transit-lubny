<?php

declare(strict_types=1);

namespace App\UI\API\Controller\Route;

use App\Actions\Route\GetRouteAction;
use App\Exceptions\DTO\ErrorResponseDTO;
use App\UI\API\Responses\RouteResponse;
use App\UI\API\Transformers\RouteWithPatternsTransformer;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag('Route')]
#[Route(path: 'api/v1/route/{id}', name: 'api_get_route', methods: ['GET'])]
final class GetRouteController extends AbstractController
{
    public function __construct(
        private readonly GetRouteAction $action,
        private readonly RouteWithPatternsTransformer $transformer,
    ) {}

    #[OA\Get(
        operationId: 'web_all_routes',
        description: 'Отримання списку усіх маршруток',
        summary: 'Отримання списку маршруток',
    )]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(ref: new Model(type: RouteResponse::class)),
    )]
    #[OA\Response(
        response: 404,
        description: 'Route not found',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponseDTO::class)),
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'route id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer'),
    )]
    public function __invoke(int $id): JsonResponse
    {
        $routeDTO = $this->action->run($id);

        return $this->json($this->transformer->transform($routeDTO));
    }
}
