<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use App\Entity\LogsEntry;
use App\Entity\LogsImport;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class EntityGenerator
{
    public function __construct(
        private readonly ObjectManager $entityManager
    ) {}

    public function getLogsImport(): LogsImport
    {
        $logsImport = new LogsImport;

        $logsImport->setId(Random::positiveInteger());
        $logsImport->setUuid(Uuid::v6());
        $logsImport->setFilepath(sprintf('%s.%s', Random::getString(), 'txt'));

        $this->entityManager->persist($logsImport);

        return $logsImport;
    }

    public function getLogsEntry(?LogsImport $logsImport = null): LogsEntry
    {
        $logsEntry = new LogsEntry;

        $logsEntry->setId(Random::positiveInteger());
        $logsEntry->setServiceName(Random::getString(24));
        $logsEntry->setDateTime(Random::getPastDate());
        $logsEntry->setMethod(Random::getHttpMethod());
        $logsEntry->setUri(sprintf('/%s', Random::getString(6)));
        $logsEntry->setProtocolVersion('HTTP/1.1');
        $logsEntry->setStatusCode(Random::getArrayElement([200, 204, 404, 500]));

        if (null !== $logsImport) {
            $logsEntry->setLogsImport($logsImport);
        }

        $this->entityManager->persist($logsEntry);

        return $logsEntry;
    }
}
