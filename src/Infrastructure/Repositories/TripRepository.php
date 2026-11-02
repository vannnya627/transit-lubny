<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entity\Trip\Trip;
use App\Domain\Exceptions\TripNotFoundException;
use App\Domain\Repositories\TripRepositoryInterface;
use App\Domain\ValueObjects\TimeOfDay;
use App\Infrastructure\Repositories\Traits\RepositorySupportTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trip>
 */
final class TripRepository extends ServiceEntityRepository implements TripRepositoryInterface
{
    use RepositorySupportTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }

    public function findTripsWithStopTimes(int $patternId): array
    {
        /** @var list<Trip> $trips */
        $trips = $this->createQueryBuilder('t')
            ->where('t.patternId = :patternId')
            ->setParameter('patternId', $patternId)
            ->leftJoin('t.stopTimes', 'st')
            ->addSelect('st')
            ->orderBy('st.arrivalTime.value', 'ASC')
            ->getQuery()
            ->getResult();

        return $trips;
    }

    public function findTripsWithStopTimesForStop(int $stopId, TimeOfDay $time, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->join('t.stopTimes', 'st')
            ->addSelect('st')
            ->where('st.stopId = :stopId')
            ->andWhere('st.arrivalTime.value >= :time')
            ->orderBy('st.arrivalTime.value', 'ASC')
            ->setMaxResults($limit)
            ->setParameter('stopId', $stopId)
            ->setParameter('time', $time->value);

        /** @var list<Trip> $stopTimes */
        $stopTimes = $qb->getQuery()->getResult();

        return $stopTimes;
    }

    public function getTripById(int $tripId): Trip
    {
        return $this->find($tripId) ?? throw new TripNotFoundException($tripId);
    }

    public function findTrips(array $patternIds): array
    {
        if ([] === $patternIds) {
            return [];
        }

        return $this->findBy(['patternId' => $patternIds]);
    }
}
