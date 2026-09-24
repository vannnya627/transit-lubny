<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Route\Pattern;
use App\Entity\Route\Route;
use App\Exceptions\Exception\PatternNotFoundException;
use App\Exceptions\Exception\RouteNotFoundException;
use App\Repository\Interfaces\RouteRepositoryInterface;
use App\Repository\Traits\RepositorySupportTrait;
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
}
