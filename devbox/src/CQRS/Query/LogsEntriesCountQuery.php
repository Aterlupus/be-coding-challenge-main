<?php
declare(strict_types=1);

namespace App\CQRS\Query;

use App\Core\CQRS\QueryInterface;

class LogsEntriesCountQuery implements QueryInterface
{
    public function __construct(
        private readonly ?array $servicesNames = null,
        private readonly ?\DateTime $startDate = null,
        private readonly ?\DateTime $endDate = null,
        private readonly ?int $statusCode = null
    ) {}

    public function getServicesNames(): ?array
    {
        return $this->servicesNames;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
}
