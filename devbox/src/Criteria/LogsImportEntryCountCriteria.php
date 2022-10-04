<?php
declare(strict_types=1);

namespace App\Criteria;

use App\Core\DateTime\DateTimeValidator;
use DateTime;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

class LogsImportEntryCountCriteria
{
    private ?array $serviceNames = null;

    private ?DateTime $startDate = null;

    private ?DateTime $endDate = null;

    private ?int $statusCode = null;


    public function __construct(Request $request)
    {
        $this->assignFields($request);
    }

    private function assignFields(Request $request): void
    {
        $this->assignServiceNames($request->get('serviceNames'));
        $this->assignStartDate($request->get('startDate'));
        $this->assignEndDate($request->get('endDate'));
        $this->assignStatusCode($request->get('statusCode'));
    }

    private function assignServiceNames(?array $serviceNames): void
    {
        if (null !== $serviceNames && false === is_array($serviceNames)) {
            throw new BadRequestException('serviceNames parameter value must be an array');
        } else {
            $this->serviceNames = $serviceNames;
        }
    }

    private function assignStartDate(?string $startDate): void
    {
        if (self::isDateParameterInvalid($startDate)) {
            throw new BadRequestException('Invalid startDate parameter value');
        } else if (null !== $startDate) {
            $this->startDate = new DateTime($startDate);
        }
    }

    private function assignEndDate(?string $endDate): void
    {
        if (self::isDateParameterInvalid($endDate)) {
            throw new BadRequestException('Invalid endDate parameter value');
        } else if (null !== $endDate) {
            $this->endDate = new DateTime($endDate);
        }
    }

    private function assignStatusCode(?string $statusCode): void
    {
        $this->statusCode = null !== $statusCode ? (int) $statusCode : null;
    }

    private static function isDateParameterInvalid(?string $dateStr): bool
    {
        return null !== $dateStr && false === DateTimeValidator::isValid($dateStr);
    }

    public function getServiceNames(): ?array
    {
        return $this->serviceNames;
    }

    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
}
