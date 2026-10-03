<?php

declare(strict_types=1);

namespace App\UI\WEB\Controllers\Trip;

use App\Actions\Trip\GetTripAction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'trips/{id}', name: 'web_trips', methods: ['GET'])]
final class GetTripController extends AbstractController
{
    public function __construct(
        private readonly GetTripAction $action,
    ) {}

    public function __invoke(int $id): Response
    {
        $tripDetailsDto = $this->action->run($id);

        return $this->render('trip/get_trip.html.twig', ['trip' => $tripDetailsDto]);
    }
}
