<?php
declare(strict_types=1);

namespace App\CQRS\CommandHandler;

use App\Core\CQRS\AbstractCommandHandler;
use App\CQRS\Command\CreateLogsEntryCommand;
use App\Entity\LogsEntry;
use App\Entity\LogsImport;
use Symfony\Component\Uid\Uuid;

class CreateLogsEntryCommandHandler extends AbstractCommandHandler
{
    public function __invoke(CreateLogsEntryCommand $command): void
    {
        $logsEntry = new LogsEntry;

        $logsEntry->setServiceName($command->getServiceName());
        $logsEntry->setDateTime($command->getDateTime());
        $logsEntry->setMethod($command->getMethod());
        $logsEntry->setUri($command->getUri());
        $logsEntry->setProtocolVersion($command->getProtocolVersion());
        $logsEntry->setStatusCode($command->getStatusCode());
        $logsEntry->setLogsImport($this->getLogsImport($command->getLogsImportUuid()));

        $this->saveEntity($logsEntry);
    }

    private function getLogsImport(Uuid $logsImportUuid): LogsImport
    {
        /** @var LogsImport $logsImport */
        $logsImport = $this->getEntity(LogsImport::class, ['uuid' => $logsImportUuid]);
        return $logsImport;
    }
}
