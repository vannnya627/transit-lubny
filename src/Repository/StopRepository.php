<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Stop;
use App\Repository\Interfaces\StopRepositoryInterface;
use App\Repository\Traits\RepositorySupportTrait;
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

    public function findStopsByIds(array $stopsIds): array
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
            }
        }

        return $orderedStops;
    }
}
