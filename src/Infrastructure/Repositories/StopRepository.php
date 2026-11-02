<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entity\Stop;
use App\Domain\Exceptions\StopNotFoundException;
use App\Domain\Repositories\StopRepositoryInterface;
use App\Domain\ValueObjects\Coordinates;
use App\Infrastructure\Repositories\Traits\RepositorySupportTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stop>
 */
final class StopRepository extends ServiceEntityRepository implements StopRepositoryInterface
{
    use RepositorySupportTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stop::class);
    }

    public function getStopsByIds(array $stopsIds): array
    {
        $stops = $this->findBy(['id' => $stopsIds]);

        $stopsById = [];
        foreach ($stops as $stop) {
            $stopsById[$stop->id] = $stop;
        }

        $orderedStops = [];
        foreach ($stopsIds as $id) {
            if (isset($stopsById[$id])) {
                $orderedStops[] = $stopsById[$id];
            } else {
                throw new StopNotFoundException($id);
            }
        }

        return $orderedStops;
    }

    /**
     * @return list<Stop>
     */
    public function findStopsBySearchQuery(?string $query = null, ?Coordinates $coordinates = null, ?int $radius = null): array
    {
        $qb = $this->createQueryBuilder('s');

        if (null !== $query) {
            $qb->andWhere('LOWER(s.name) LIKE LOWER(:q)')
                ->setParameter('q', '%' . $query . '%');
        }

        if (null !== $coordinates) {

            $qb->andWhere('ACOS(SIN(RADIANS(s.coordinates.lat)) * SIN(RADIANS(:lat)) + COS(RADIANS(s.coordinates.lat)) * COS(RADIANS(:lat)) * COS(RADIANS(s.coordinates.lon - :lon))) * 6371000 <= :radius')
                ->setParameter('lat', $coordinates->lat)
                ->setParameter('lon', $coordinates->lon)
                ->setParameter('radius', $radius);
        }

        /** @var list<Stop> $stops */
        $stops =  $qb->getQuery()->getResult();

        return  $stops;
    }

    public function getStopById(int $stopId): Stop
    {
        return $this->find($stopId) ?? throw new StopNotFoundException($stopId);
    }

    public function findStopsByCityCodes(array $cityCodes): array
    {
        return $this->findBy(['cityCode' => $cityCodes]);
    }
}
