<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Route\Route;
use App\Exceptions\Exception\RouteNotFoundException;
use App\Repository\Interfaces\RouteRepositoryInterface;
use App\Repository\Traits\RepositorySupportTrait;
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
}
