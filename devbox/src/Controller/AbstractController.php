<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\CQRS\QueryBus;
use App\Core\CQRS\QueryInterface;

abstract class AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus
    ) {}

    public function dispatchQuery(QueryInterface $query): mixed
    {
        return $this->queryBus->dispatch($query);
    }
}
