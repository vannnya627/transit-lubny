<?php

declare(strict_types=1);

namespace App\Data\Repository;

use App\Data\Entity\Stop;
use App\Data\Repository\Interfaces\StopRepositoryInterface;
use App\Data\Repository\Traits\RepositorySupportTrait;
use App\DTOs\Input\SearchStopsInput;
use App\Exceptions\Exception\StopNotFoundException;
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
    public function findStopsBySearchQuery(SearchStopsInput $input): array
    {
        $qb = $this->createQueryBuilder('s');

        if (null !== $input->q) {
            $qb->andWhere('LOWER(s.name) LIKE LOWER(:q)')
                ->setParameter('q', '%' . $input->q . '%');
        }

        if (null !== $input->coordinates) {

            $qb->andWhere('ACOS(SIN(RADIANS(s.coordinates.lat)) * SIN(RADIANS(:lat)) + COS(RADIANS(s.coordinates.lat)) * COS(RADIANS(:lat)) * COS(RADIANS(s.coordinates.lon - :lon))) * 6371000 <= :radius')
                ->setParameter('lat', $input->coordinates->lat)
                ->setParameter('lon', $input->coordinates->lon)
                ->setParameter('radius', $input->radius);
        }

        /** @var list<Stop> $stops */
        $stops =  $qb->getQuery()->getResult();

        return  $stops;
    }

    public function getStopById(int $stopId): Stop
    {
        return $this->find($stopId) ?? throw new StopNotFoundException($stopId);
    }
}
