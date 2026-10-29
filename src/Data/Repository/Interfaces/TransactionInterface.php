<?php

declare(strict_types=1);

namespace App\Data\Repository\Interfaces;

interface TransactionInterface
{
    public function executeInTransaction(callable $operation): mixed;
}
