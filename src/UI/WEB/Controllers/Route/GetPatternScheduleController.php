<?php

declare(strict_types=1);

namespace App\UI\WEB\Controllers\Route;

use App\Actions\Route\GetPatternWithTripsAndStopsAction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'patterns/{id}/schedule', name: 'web_get_info_about_pattern', methods: ['GET'])]
final class GetPatternScheduleController extends AbstractController
{
    public function __construct(
        private readonly GetPatternWithTripsAndStopsAction $action,
    ) {}

    public function __invoke(int $id): Response
    {
        $patternSchedule = $this->action->run($id);

        return $this->render('route/get_pattern_schedule.html.twig', ['schedule' => $patternSchedule]);
    }
}
