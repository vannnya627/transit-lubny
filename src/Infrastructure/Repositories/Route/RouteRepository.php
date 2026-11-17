<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Route;

use App\Domain\Entity\Route\Route;
use App\Domain\Exceptions\RouteNotFoundException;
use App\Domain\Repositories\Route\RouteRepositoryInterface;
use App\Infrastructure\Repositories\Traits\RepositorySupportTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Route>
 */
final class RouteRepository extends ServiceEntityRepository implements RouteRepositoryInterface
{
    use RepositorySupportTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Route::class);
    }

    public function getRouteById(int $id): Route
    {
        return $this->find($id) ?? throw new RouteNotFoundException($id);
    }

    public function getAllRoutes(): array
    {
        return $this->findAll();
    }

    public function findRouteByShortName(string $shortName): ?Route
    {
        return $this->findOneBy(['shortName' => $shortName]);
    }

    /**
     * @return list<Route>
     */
    public function findRoutesByStopId(int $stopId): array
    {
        $qb =  $this->createQueryBuilder('r')
            ->select('DISTINCT r')
            ->innerJoin('r.patterns', 'p')
            ->innerJoin('p.patternStops', 'ps')
            ->andWhere('ps.stopId = :stopId')
            ->setParameter('stopId', $stopId);

        /** @var list<Route> $routes */
        $routes = $qb->getQuery()->getResult();

        usort(
            $routes,
            static fn(Route $a, Route $b): int => strnatcmp($a->shortName, $b->shortName),
        );

        return $routes;
    }
}
