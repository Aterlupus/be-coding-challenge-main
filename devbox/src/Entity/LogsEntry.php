<?php
declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class LogsEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $serviceName;

    #[ORM\Column(type: 'datetime')]
    private DateTime $dateTime;

    #[ORM\Column(type: 'string')]
    private string $method;

    #[ORM\Column(type: 'string')]
    private string $uri;

    #[ORM\Column(type: 'string')]
    private string $protocolVersion;

    #[ORM\Column(type: 'integer')]
    private int $statusCode;

    #[ORM\ManyToOne(targetEntity: LogsImport::class, inversedBy: 'logsEntries')]
    private LogsImport $logsImport;


    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function setServiceName(string $serviceName): void
    {
        $this->serviceName = $serviceName;
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function setDateTime(DateTime $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function setProtocolVersion(string $protocolVersion): void
    {
        $this->protocolVersion = $protocolVersion;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getLogsImport(): LogsImport
    {
        return $this->logsImport;
    }

    public function setLogsImport(LogsImport $logsImport): void
    {
        $this->logsImport = $logsImport;
    }
}
