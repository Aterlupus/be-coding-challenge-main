<?php
declare(strict_types=1);

namespace App\CQRS\Command;

use App\Core\CQRS\CommandInterface;
use DateTime;
use Symfony\Component\Uid\Uuid;

class CreateLogsEntryCommand implements CommandInterface
{
    public function __construct(
        private readonly Uuid $logsImportUuid,
        private readonly string $serviceName,
        private readonly DateTime $dateTime,
        private readonly string $method,
        private readonly string $uri,
        private readonly string $protocolVersion,
        private readonly int $statusCode
    ) {}

    public function getLogsImportUuid(): Uuid
    {
        return $this->logsImportUuid;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
