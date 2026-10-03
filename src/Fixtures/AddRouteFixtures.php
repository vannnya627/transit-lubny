<?php

declare(strict_types=1);

namespace App\Fixtures;

use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Route\PatternStop;
use App\Data\Entity\Route\Route;
use App\Data\Entity\Stop;
use App\Data\Entity\Trip\StopTime;
use App\Data\Entity\Trip\Trip;
use App\Data\ValueObjects\Coordinates;
use App\Data\ValueObjects\ServiceDays;
use App\Enum\Direction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use LogicException;

class AddRouteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $data = [];
        $files = glob(__DIR__ . '/Routes/*.json');

        if (is_array($files)) {
            foreach ($files as $jsonFile) {
                $jsonContent = file_get_contents($jsonFile);
                if (is_string($jsonContent)) {
                    $routesFromFile = json_decode($jsonContent, true);
                    if (is_array($routesFromFile)) {
                        $data = array_merge($data, $routesFromFile);
                    }
                }
            }
        }

        $serviceDays = clone new ServiceDays(true, true, true, true, true, true, true);

        $uniqueStops = [];

        foreach ($data as $routeData) {
            if (!is_array($routeData)) {
                continue;
            }
            $patterns = $routeData['patterns'] ?? [];
            if (!is_array($patterns)) {
                continue;
            }

            foreach ($patterns as $patternData) {
                if (!is_array($patternData)) {
                    continue;
                }
                $stops = $patternData['stops'] ?? [];
                if (!is_array($stops)) {
                    continue;
                }

                foreach ($stops as $stopData) {
                    if (!is_array($stopData)) {
                        continue;
                    }

                    $codeVal = $stopData['cityCode'] ?? 0;
                    $code = is_numeric($codeVal) ? (int) $codeVal : 0;

                    $latVal = $stopData['lat'] ?? null;
                    $lat = is_numeric($latVal) ? (float) $latVal : null;

                    $lonVal = $stopData['lon'] ?? null;
                    $lon = is_numeric($lonVal) ? (float) $lonVal : null;

                    $nameVal = $stopData['name'] ?? '';
                    $name = is_scalar($nameVal) ? (string) $nameVal : '';

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

        /** @var array<int, Stop> $stopEntities */
        $stopEntities = [];
        foreach ($uniqueStops as $code => $stopInfo) {
            $lat = (float) $stopInfo['lat'];
            $lon = (float) $stopInfo['lon'];
            $name = (string) $stopInfo['name'];

            $coordinates = Coordinates::create($lat, $lon);
            $stop = Stop::create($name, $coordinates, $code);
            $manager->persist($stop);
            $stopEntities[$code] = $stop;
        }

        $manager->flush();

        foreach ($data as $routeData) {
            if (!is_array($routeData)) {
                continue;
            }

            $snVal = $routeData['shortName'] ?? '';
            $shortName = is_scalar($snVal) ? (string) $snVal : '';

            $lnVal = $routeData['longName'] ?? '';
            $longName = is_scalar($lnVal) ? (string) $lnVal : '';

            $route = Route::create($shortName, $longName);

            $patterns = $routeData['patterns'] ?? [];
            if (!is_array($patterns)) {
                continue;
            }

            foreach ($patterns as $patternData) {
                if (!is_array($patternData)) {
                    continue;
                }

                $dirVal = $patternData['direction'] ?? 0;
                $directionInt = is_numeric($dirVal) ? (int) $dirVal : 0;
                $direction = Direction::from($directionInt);

                $pnVal = $patternData['name'] ?? '';
                $patternName = is_scalar($pnVal) ? (string) $pnVal : '';

                $pattern = Pattern::create($route, $direction, $patternName);

                $sequence = 1;
                $stops = $patternData['stops'] ?? [];
                if (is_array($stops)) {
                    foreach ($stops as $stopData) {
                        if (!is_array($stopData)) {
                            continue;
                        }

                        $codeVal = $stopData['cityCode'] ?? 0;
                        $code = is_numeric($codeVal) ? (int) $codeVal : 0;

                        if (!isset($stopEntities[$code])) {
                            continue;
                        }

                        $stopId = $stopEntities[$code]->id;
                        $patternStop = PatternStop::create($pattern, $sequence, $stopId);
                        $pattern->patternStops->add($patternStop);
                        ++$sequence;
                    }
                }

                $route->patterns->add($pattern);
                $manager->persist($route);
                $manager->flush();

                $trips = $patternData['trips'] ?? null;
                if (is_array($trips)) {
                    foreach ($trips as $tripConfig) {
                        if (!is_array($tripConfig)) {
                            continue;
                        }

                        $trip = Trip::create($pattern->id, $serviceDays);
                        $stopsList = $pattern->patternStops->toArray();
                        $times = $tripConfig['times'] ?? [];

                        if (is_array($times)) {
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
                                    $trip->stopTimes->add($stopTime);
                                }
                            }
                        }
                        $manager->persist($trip);
                    }
                }
            }
        }

        $manager->flush();
    }
}
