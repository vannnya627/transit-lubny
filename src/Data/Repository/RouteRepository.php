<?php

declare(strict_types=1);

namespace App\Data\Repository;

use App\Data\Entity\Route\Pattern;
use App\Data\Entity\Route\Route;
use App\Data\Repository\Interfaces\RouteRepositoryInterface;
use App\Data\Repository\Traits\RepositorySupportTrait;
use App\Exceptions\Exception\PatternNotFoundException;
use App\Exceptions\Exception\RouteNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
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

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    // TODO Можливо змінити - додати репозиторій для Patter, хоча він належить Route
    public function getPatternById(int $patternId): Pattern
    {
        return $this->getEntityManager()->find(Pattern::class, $patternId) ?? throw new PatternNotFoundException($patternId);
    }

    /**
     * @param array<int, int> $patternIds
     *
     * @return list<Pattern>
     */
    public function findPatternsWithRoutesByIds(array $patternIds): array
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('p')
            ->from(Pattern::class, 'p')
            ->where('p.id IN (:patternIds)')
            ->join('p.route', 'r')
            ->addSelect('r')
            ->setParameter('patternIds', $patternIds);

        /** @var list<Pattern> $patterns */
        $patterns = $qb->getQuery()->getResult();

        return $patterns;
    }

    public function getPatternWithRouteById(int $patternId): Pattern
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('p')
            ->from(Pattern::class, 'p')
            ->where('p.id = :patternId')
            ->join('p.route', 'r')
            ->addSelect('r')
            ->setParameter('patternId', $patternId);

        /** @var Pattern|null $pattern */
        $pattern = $qb->getQuery()->getOneOrNullResult();

        if (null === $pattern) {
            throw new PatternNotFoundException($patternId);
        }

        return $pattern;
    }
}
