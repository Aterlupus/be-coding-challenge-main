<?php
declare(strict_types=1);

namespace App\CQRS\CommandHandler;

use App\Core\CQRS\AbstractCommandHandler;
use App\CQRS\Command\CreateLogsImportCommand;
use App\Entity\LogsImport;

class CreateLogsImportCommandHandler extends AbstractCommandHandler
{
    public function __invoke(CreateLogsImportCommand $command): void
    {
        $logsImport = new LogsImport;

        $logsImport->setUuid($command->getUuid());
        $logsImport->setFilepath($command->getFilepath());

        $this->saveEntity($logsImport);
    }
}
