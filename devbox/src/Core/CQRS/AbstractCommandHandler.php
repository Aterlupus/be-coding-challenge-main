<?php
declare(strict_types=1);

namespace App\Core\CQRS;

abstract class AbstractCommandHandler extends AbstractHandler implements CommandHandlerInterface
{
    protected function getEntity(string $entityClass, array $criteria): ?object
    {
        return $this->getEntityManager()->getRepository($entityClass)->findOneBy($criteria);
    }

    protected function getEntities(string $entityClass, array $criteria): array
    {
        return $this->getEntityManager()->getRepository($entityClass)->findBy($criteria);
    }

    protected function saveEntity(object $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }
}
