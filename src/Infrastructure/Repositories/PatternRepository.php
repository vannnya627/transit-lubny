<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entity\Pattern\Pattern;
use App\Domain\Exceptions\PatternNotFoundException;
use App\Domain\Repositories\PatternRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pattern>
 */
final class PatternRepository extends ServiceEntityRepository implements PatternRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pattern::class);
    }

    public function getPatternById(int $patternId): Pattern
    {
        return $this->find($patternId) ?? throw new PatternNotFoundException($patternId);
    }

    /**
     * @param array<int, int> $patternIds
     *
     * @return list<Pattern>
     */
    public function findPatternsWithRoutesByIds(array $patternIds): array
    {
        $qb = $this->createQueryBuilder('p')
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
        $qb = $this->createQueryBuilder('p')
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
