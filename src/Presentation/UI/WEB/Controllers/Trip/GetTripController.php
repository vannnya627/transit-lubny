<?php

declare(strict_types=1);

namespace App\Presentation\UI\WEB\Controllers\Trip;

use App\Application\Actions\Trip\GetTripAction;
use App\Application\DTOs\Inputs\GetTripInput;
use App\Domain\ValueObjects\TimeOfDay;
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
        $tripDetailsDto = $this->action->run(new GetTripInput($id, TimeOfDay::now()));

        return $this->render('trip/get_trip.html.twig', ['trip' => $tripDetailsDto]);
    }
}
