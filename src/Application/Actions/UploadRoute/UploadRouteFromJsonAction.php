<?php

declare(strict_types=1);

namespace App\Application\Actions\UploadRoute;

use App\Application\Events\RouteImportCompletedEvent;
use App\Application\Import\DTOs\PatternImportData;
use App\Application\Import\DTOs\RouteImportData;
use App\Application\Import\DTOs\StopImportData;
use App\Application\Import\DTOs\TripImportData;
use App\Application\Import\Interfaces\RouteImportFileReaderInterface;
use App\Application\Import\Interfaces\RouteImportParserInterface;
use App\Application\Tasks\Route\DeleteRouteTask;
use App\Application\Tasks\Route\FindRouteByShortNameTask;
use App\Application\Tasks\Stop\FindStopsByCityCodesTask;
use App\Domain\Entity\Route\Pattern\Pattern;
use App\Domain\Entity\Route\Route;
use App\Domain\Entity\Stop;
use App\Domain\Entity\Trip\Trip;
use App\Domain\Repositories\Route\RouteRepositoryInterface;
use App\Domain\Repositories\StopRepositoryInterface;
use App\Domain\Repositories\TransactionInterface;
use App\Domain\Repositories\Trip\TripRepositoryInterface;
use App\Domain\ValueObjects\ServiceDays;
use LogicException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

final readonly class UploadRouteFromJsonAction
{
    public function __construct(
        private StopRepositoryInterface $stopRepository,
        private RouteRepositoryInterface $routeRepository,
        private TripRepositoryInterface $tripRepository,
        private FindRouteByShortNameTask $findRouteByShortNameTask,
        private FindStopsByCityCodesTask $findStopsByCityCodesTask,
        private TransactionInterface $transaction,
        private DeleteRouteTask $deleteRouteTask,
        private EventDispatcherInterface $dispatcher,
        private RouteImportFileReaderInterface $fileReader,
        private RouteImportParserInterface $parser,
    ) {}

    /**
     * @throws Throwable
     */
    public function run(string $filePath): void
    {
        $data = $this->parser->parse(
            $this->fileReader->read($filePath),
            $filePath,
        );

        $this->transaction->executeInTransaction(
            function () use ($data): void {
                $this->deleteDuplicateBeforeUpload($data);

                $uniqueStops = $this->getUniqueStops($data);
                $stopEntities = $this->saveMissingStops($uniqueStops);

                foreach ($data as $routeData) {
                    $this->processRoute($routeData, $stopEntities);
                }
            },
        );

        $this->dispatcher->dispatch(new RouteImportCompletedEvent());
    }

    /**
     * @param list<RouteImportData> $data
     *
     * @return array<int, StopImportData>
     */
    private function getUniqueStops(array $data): array
    {
        $uniqueStops = [];

        foreach ($data as $routeData) {
            foreach ($routeData->patterns as $patternData) {
                foreach ($patternData->stops as $stopData) {
                    $code = $stopData->cityCode;

                    if (isset($uniqueStops[$code])) {
                        if ($uniqueStops[$code]->name !== $stopData->name) {
                            throw new LogicException(sprintf('Колізія cityCode %d: вже прив’язаний до "%s", але маршрут намагається призначити "%s"', $code, $uniqueStops[$code]->name, $stopData->name));
                        }

                        if (
                            0.0 === $uniqueStops[$code]->coordinates->lat
                            && 0.0 !== $stopData->coordinates->lat
                        ) {
                            $uniqueStops[$code] = $stopData;
                        }

                        continue;
                    }

                    $uniqueStops[$code] = $stopData;
                }
            }
        }

        return $uniqueStops;
    }

    /**
     * @param list<RouteImportData> $data
     *
     * @throws Throwable
     */
    private function deleteDuplicateBeforeUpload(array $data): void
    {
        foreach ($data as $routeData) {
            $route = $this->findRouteByShortNameTask->run($routeData->shortName);

            if (null !== $route) {
                $this->deleteRouteTask->run($route);
            }
        }
    }

    /**
     * @param array<int, StopImportData> $uniqueStops
     *
     * @return array<int, Stop>
     */
    private function saveMissingStops(array $uniqueStops): array
    {
        /** @var array<int, Stop> $stopEntities */
        $stopEntities = [];

        $cityCodes = array_keys($uniqueStops);

        $existingStops = $this->findStopsByCityCodesTask->run($cityCodes);

        foreach ($existingStops as $existingStop) {
            $stopEntities[$existingStop->cityCode] = $existingStop;
        }

        foreach ($uniqueStops as $code => $stopInfo) {
            if (isset($stopEntities[$code])) {
                continue;
            }

            $stop = Stop::create($stopInfo->name, $stopInfo->coordinates, $code);

            $this->stopRepository->save($stop);
            $stopEntities[$code] = $stop;
        }

        $this->stopRepository->commit();

        return $stopEntities;
    }

    /**
     * @param array<int, Stop> $stopEntities
     *
     * @throws Throwable
     */
    private function processRoute(RouteImportData $routeData, array $stopEntities): void
    {
        $route = Route::create($routeData->shortName, $routeData->longName);

        /** @var list<array{pattern: Pattern, trips: list<TripImportData>}> $patternsWithTrips */
        $patternsWithTrips = [];

        foreach ($routeData->patterns as $patternData) {
            $patternsWithTrips[] = [
                'pattern' => $this->createPattern($route, $patternData, $stopEntities),
                'trips' => $patternData->trips,
            ];
        }

        $this->routeRepository->saveAndCommit($route);

        $serviceDays = new ServiceDays(true, true, true, true, true, true, true);

        foreach ($patternsWithTrips as $item) {
            $this->createTripsForPattern($item['pattern'], $item['trips'], $serviceDays);
        }

        $this->tripRepository->commit();
    }

    /**
     * @param array<int, Stop> $stopEntities
     */
    private function createPattern(Route $route, PatternImportData $patternData, array $stopEntities): Pattern
    {
        $pattern = $route->createPattern($patternData->direction, $patternData->name);

        $sequence = 1;

        foreach ($patternData->stops as $stopData) {
            $stop = $stopEntities[$stopData->cityCode] ?? null;

            if (null === $stop) {
                throw new LogicException(sprintf('Зупинку cityCode %d не знайдено після збереження', $stopData->cityCode));
            }

            $pattern->createPatternStop($sequence, $stop->id);
            ++$sequence;
        }

        return $pattern;
    }

    /**
     * @param list<TripImportData> $tripsData
     */
    private function createTripsForPattern(
        Pattern $pattern,
        array $tripsData,
        ServiceDays $serviceDays,
    ): void {
        foreach ($tripsData as $tripConfig) {
            $this->tripRepository->save(
                $this->createSingleTrip($pattern, $tripConfig, $serviceDays),
            );
        }
    }

    private function createSingleTrip(Pattern $pattern, TripImportData $tripConfig, ServiceDays $serviceDays): Trip
    {
        $trip = Trip::create($pattern->id, clone $serviceDays);
        $stopsList = $pattern->patternStops->toArray();

        if (count($tripConfig->times) !== count($stopsList)) {
            throw new LogicException(sprintf('Кількість часів (%d) не дорівнює кількості зупинок pattern (%d)', count($tripConfig->times), count($stopsList)));
        }

        foreach ($stopsList as $index => $patternStop) {
            $time = $tripConfig->times[$index];

            if (null === $time) {
                continue;
            }

            $trip->createStopTime(
                $patternStop->stopSequence,
                $patternStop->stopId,
                $time,
                false,
            );
        }

        return $trip;
    }
}
