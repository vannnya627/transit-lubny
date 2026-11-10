<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Trip;

use App\Domain\Entity\Trip\StopTime;
use App\Domain\Repositories\Trip\StopTimeReadRepositoryInterface;
use App\Domain\ValueObjects\TimeOfDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StopTime>
 */
final class StopTimeReadRepository extends ServiceEntityRepository implements StopTimeReadRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StopTime::class);
    }

    public function findStopTimesWithTrip(int $stopId, TimeOfDay $time, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('st')
            ->join('st.trip', 't')
            ->addSelect('t')
            ->where('st.stopId = :stopId')
            ->andWhere('st.arrivalTime.value >= :time')
            ->orderBy('st.arrivalTime.value', 'ASC')
            ->setMaxResults($limit)
            ->setParameter('stopId', $stopId)
            ->setParameter('time', $time->value);

        /** @var list<StopTime> $stopTimes */
        $stopTimes = $qb->getQuery()->getResult();

        return $stopTimes;

    }
}
