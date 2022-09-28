<?php
declare(strict_types=1);

namespace App\CQRS\Command;

use App\Core\CQRS\CommandInterface;
use Symfony\Component\Uid\Uuid;

class CreateLogsImportCommand implements CommandInterface
{
    public function __construct(
        private readonly Uuid $uuid,
        private readonly string $filepath
    ) {}

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getFilepath(): string
    {
        return $this->filepath;
    }
}
