<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\TransactionInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class Transaction implements TransactionInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function executeInTransaction(callable $operation): mixed
    {
        return $this->entityManager->wrapInTransaction($operation);
    }
}
