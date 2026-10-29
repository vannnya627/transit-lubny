<?php

declare(strict_types=1);

namespace App\Data\Repository;

use App\Data\Repository\Interfaces\TransactionInterface;
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
