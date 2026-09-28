<?php

declare(strict_types=1);

namespace App\UI\API\Controller\Route;

use App\Actions\Route\GetAllRouteAction;
use App\UI\API\Responses\RouteResponse;
use App\UI\API\Transformers\RouteTransformer;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag('Route')]
#[Route(path: 'api/v1/routes', name: 'api_all_routes', methods: ['GET'])]
final class GetAllRouteController extends AbstractController
{
    public function __construct(
        private readonly GetAllRouteAction $action,
        private readonly RouteTransformer $transformer,
    ) {}

    #[OA\Get(
        operationId: 'web_all_routes',
        description: 'Отримання списку усіх маршруток',
        summary: 'Отримання списку маршруток',
    )]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(type: RouteResponse::class),
            ),
        ),
    )]
    public function __invoke(): JsonResponse
    {
        $routeDTOs = $this->action->run();

        return $this->json(array_map($this->transformer->transform(...), $routeDTOs));
    }
}
