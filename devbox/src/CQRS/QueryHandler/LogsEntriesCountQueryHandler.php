<?php
declare(strict_types=1);

namespace App\CQRS\QueryHandler;

use App\Core\CQRS\AbstractQueryHandler;
use App\CQRS\Query\LogsEntriesCountQuery;
use App\Entity\LogsEntry;

class LogsEntriesCountQueryHandler extends AbstractQueryHandler
{
    public function __invoke(LogsEntriesCountQuery $query): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('count(logEntry.id)')
            ->from(LogsEntry::class, 'logEntry');

        if (null !== $query->getServicesNames()) {
            $qb->andWhere('logEntry.serviceName IN (:servicesNames)')
               ->setParameter('servicesNames', $query->getServicesNames());
        }

        if (null !== $query->getStartDate()) {
            $qb->andWhere('logEntry.dateTime >= :startDate')
               ->setParameter('startDate', $query->getStartDate());
        }

        if (null !== $query->getEndDate()) {
            $qb->andWhere('logEntry.dateTime <= :endDate')
               ->setParameter('endDate', $query->getEndDate());
        }

        if (null !== $query->getStatusCode()) {
            $qb->andWhere('logEntry.statusCode = :statusCode')
               ->setParameter('statusCode', $query->getStatusCode());
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}
