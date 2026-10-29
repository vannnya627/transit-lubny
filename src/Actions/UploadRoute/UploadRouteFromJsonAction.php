<?php

declare(strict_types=1);

namespace App\Actions\UploadRoute;

use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Route\PatternStop;
use App\Data\Entity\Route\Route;
use App\Data\Entity\Stop;
use App\Data\Entity\Trip\StopTime;
use App\Data\Entity\Trip\Trip;
use App\Data\Repository\Interfaces\RouteRepositoryInterface;
use App\Data\Repository\Interfaces\StopRepositoryInterface;
use App\Data\Repository\Interfaces\TransactionInterface;
use App\Data\Repository\Interfaces\TripRepositoryInterface;
use App\Data\ValueObjects\Coordinates;
use App\Data\ValueObjects\ServiceDays;
use App\Enum\Direction;
use App\Tasks\Route\FindRouteByShortNameTask;
use App\Tasks\Stop\FindStopsByCityCodesTask;
use App\Tasks\Trip\FindTripsByPatternIdsTask;
use LogicException;
use Throwable;

/**
 * @phpstan-type StopData array{cityCode?: int|string, lat?: float|string|null, lon?: float|string|null, name?: string}
 * @phpstan-type PatternData array{direction?: int|string, name?: string, stops?: list<StopData>, trips?: list<array{times?: list<string>}>}
 * @phpstan-type RouteData array{shortName: string, longName?: string, patterns?: list<PatternData>}
 */
final readonly class UploadRouteFromJsonAction
{
    public function __construct(
        private StopRepositoryInterface $stopRepository,
        private RouteRepositoryInterface $routeRepository,
        private TripRepositoryInterface $tripRepository,
        private FindTripsByPatternIdsTask $findTripsByPatternIdsTask,
        private FindRouteByShortNameTask $findRouteByShortNameTask,
        private FindStopsByCityCodesTask $findStopsByCityCodesTask,
        private TransactionInterface $transaction,
    ) {}

    /**
     * @throws Throwable
     */
    public function run(string $filePath): void
    {
        $data = $this->getData($filePath);

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
    }

    /**
     * @return list<RouteData>
     */
    private function getData(string $filePath): array
    {
        /** @var list<RouteData> $data */
        $data = [];

        $jsonContent = file_get_contents($filePath);
        if (is_string($jsonContent)) {
            $routesFromFile = json_decode($jsonContent, true);
            if (is_array($routesFromFile)) {
                /** @var list<RouteData> $routesFromFile */
                $data = array_merge($data, $routesFromFile);
            }
        }

        return $data;
    }

    /**
     * @param list<RouteData> $data
     *
     * @return array<int, array{name: string, lat: float, lon: float}>
     */
    private function getUniqueStops(array $data): array
    {
        $uniqueStops = [];

        foreach ($data as $routeData) {
            $patterns = $routeData['patterns'] ?? [];

            foreach ($patterns as $patternData) {
                $stops = $patternData['stops'] ?? [];

                foreach ($stops as $stopData) {
                    $codeVal = $stopData['cityCode'] ?? 0;
                    $code = is_numeric($codeVal) ? (int) $codeVal : 0;

                    $latVal = $stopData['lat'] ?? null;
                    $lat = is_numeric($latVal) ? (float) $latVal : null;

                    $lonVal = $stopData['lon'] ?? null;
                    $lon = is_numeric($lonVal) ? (float) $lonVal : null;

                    $name = $stopData['name'] ?? '';

                    if (isset($uniqueStops[$code]) && $uniqueStops[$code]['name'] !== $name) {
                        throw new LogicException(sprintf('Колізія cityCode %d: вже прив’язаний до "%s", але маршрут намагається призначити "%s"', $code, $uniqueStops[$code]['name'], $name));
                    }

                    if (!isset($uniqueStops[$code]) || (0.0 !== $lat && null !== $lat)) {
                        $uniqueStops[$code] = [
                            'name' => $name,
                            'lat' => $lat ?? 0.0,
                            'lon' => $lon ?? 0.0,
                        ];
                    }
                }
            }
        }

        return $uniqueStops;
    }

    /**
     * @param list<RouteData> $data
     *
     * @throws Throwable
     */
    private function deleteDuplicateBeforeUpload(array $data): void
    {
        foreach ($data as $routeData) {
            $route = $this->findRouteByShortNameTask->run($routeData['shortName']);

            if (null !== $route) {

                $patternsId = array_column($route->patterns->getValues(), 'id');
                $trips = $this->findTripsByPatternIdsTask->run($patternsId);

                foreach ($trips as $trip) {
                    $this->tripRepository->remove($trip);
                }

                $this->routeRepository->remove($route);
            }
        }
        $this->routeRepository->commit();
    }

    /**
     * @param array<int, array{name: string, lat: float, lon: float}> $uniqueStops
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

            $lat = $stopInfo['lat'];
            $lon = $stopInfo['lon'];
            $name = $stopInfo['name'];

            $stop = Stop::create($name, Coordinates::create($lat, $lon), $code);

            $this->stopRepository->save($stop);
            $stopEntities[$code] = $stop;
        }
        $this->stopRepository->commit();

        return $stopEntities;
    }

    /**
     * @param RouteData        $routeData
     * @param array<int, Stop> $stopEntities
     *
     * @throws Throwable
     */
    private function processRoute(array $routeData, array $stopEntities): void
    {
        $shortName = $routeData['shortName'];
        $longName = $routeData['longName'] ?? '';

        $route = Route::create($shortName, $longName);

        $patternsData = $routeData['patterns'] ?? [];
        /** @var list<array{pattern: Pattern, trips: list<array{times?: list<string>}>}> $patternsWithTrips */
        $patternsWithTrips = [];

        foreach ($patternsData as $patternData) {
            $pattern = $this->createPattern($route, $patternData, $stopEntities);
            $route->addPattern($pattern);

            $patternsWithTrips[] = [
                'pattern' => $pattern,
                'trips' => $patternData['trips'] ?? [],
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
     * @param PatternData      $patternData
     * @param array<int, Stop> $stopEntities
     */
    private function createPattern(Route $route, array $patternData, array $stopEntities): Pattern
    {
        $dirVal = $patternData['direction'] ?? 0;
        $directionInt = is_numeric($dirVal) ? (int) $dirVal : 0;
        $direction = Direction::from($directionInt);

        $patternName = $patternData['name'] ?? '';

        $pattern = Pattern::create($route, $direction, $patternName);

        $sequence = 1;
        $stops = $patternData['stops'] ?? [];

        foreach ($stops as $stopData) {
            $codeVal = $stopData['cityCode'] ?? 0;
            $code = is_numeric($codeVal) ? (int) $codeVal : 0;

            if (!isset($stopEntities[$code])) {
                continue;
            }

            $stopId = $stopEntities[$code]->id;
            $patternStop = PatternStop::create($pattern, $sequence, $stopId);
            $pattern->addPatternStop($patternStop);
            ++$sequence;
        }

        return $pattern;
    }

    /**
     * @param list<array{times?: list<string>}> $tripsData
     */
    private function createTripsForPattern(Pattern $pattern, array $tripsData, ServiceDays $serviceDays): void
    {
        foreach ($tripsData as $tripConfig) {
            $trip = $this->createSingleTrip($pattern, $tripConfig, $serviceDays);
            $this->tripRepository->save($trip);
        }
    }

    /**
     * @param array{times?: list<string>} $tripConfig
     */
    private function createSingleTrip(Pattern $pattern, array $tripConfig, ServiceDays $serviceDays): Trip
    {
        $trip = Trip::create($pattern->id, clone $serviceDays);
        $stopsList = $pattern->patternStops->toArray();
        $times = $tripConfig['times'] ?? [];

        foreach ($stopsList as $index => $patternStop) {
            $timeStr = $times[$index] ?? null;
            if (!is_string($timeStr)) {
                continue;
            }

            $parts = explode(':', $timeStr);
            if (count($parts) >= 2) {
                $hour = is_numeric($parts[0]) ? (int) $parts[0] : 0;
                $minute = is_numeric($parts[1]) ? (int) $parts[1] : 0;
                $arrivalTime = ($hour * 3600) + ($minute * 60);

                $stopTime = StopTime::create(
                    $trip,
                    $patternStop->stopSequence,
                    $patternStop->stopId,
                    $arrivalTime,
                    false,
                );
                $trip->addStopTime($stopTime);
            }
        }

        return $trip;
    }
}
