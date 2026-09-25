<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTOs\Input\GetStopDeparturesInput;
use App\Entity\Trip\Trip;
use App\Repository\Interfaces\TripRepositoryInterface;
use App\Repository\Traits\RepositorySupportTrait;
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
            ->getQuery()
            ->getResult();

        return $trips;
    }

    public function findTripsWithStopTimesForStop(GetStopDeparturesInput $input): array
    {
        $qb = $this->createQueryBuilder('t')
            ->join('t.stopTimes', 'st')
            ->addSelect('st')
            ->where('st.stopId = :stopId')
            ->andWhere('st.arrivalTime >= :time')
            ->orderBy('st.arrivalTime', 'ASC')
            ->setMaxResults($input->limit)
            ->setParameter('stopId', $input->stopId);

        $secondsFromMidnight =
            ((int) $input->time->format('H') * 3600) +
            ((int) $input->time->format('i') * 60) +
            ((int) $input->time->format('s'));

        $qb->setParameter('time', $secondsFromMidnight);

        /** @var list<Trip> $stopTimes */
        $stopTimes = $qb->getQuery()->getResult();

        return $stopTimes;
    }
}
