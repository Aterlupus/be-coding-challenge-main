<?php
declare(strict_types=1);

namespace App\Core\CQRS;

use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}
