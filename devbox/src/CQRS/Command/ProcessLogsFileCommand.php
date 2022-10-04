<?php
declare(strict_types=1);

namespace App\CQRS\Command;

use App\Core\CQRS\CommandInterface;
use Symfony\Component\Uid\Uuid;

class ProcessLogsFileCommand implements CommandInterface
{
    /*
     * TODO: Rework $filepath into separate LogsImportFile Entity which wraps up filepath and other file data
     */
    public function __construct(
        private readonly Uuid $logsImportUuid,
        private readonly string $filepath
    ) {}

    public function getLogsImportUuid(): Uuid
    {
        return $this->logsImportUuid;
    }

    public function getFilepath(): string
    {
        return $this->filepath;
    }
}
