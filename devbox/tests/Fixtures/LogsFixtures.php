<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LogsFixtures extends Fixture
{
    private const LOGS_ENTRY_COUNT = 10;

    private ?string $logsImportFilepath = null;


    public function __construct(
        private readonly EntityGenerator $entityGenerator
    ) {}

    public function load(ObjectManager $manager): void
    {
        $logsImport = $this->entityGenerator->getLogsImport();
        if (null !== $this->getLogsImportFilepath()) {
            $logsImport->setFilepath($this->getLogsImportFilepath());
        }

        for ($i = 0; $i < self::LOGS_ENTRY_COUNT; $i++) {
            $this->entityGenerator->getLogsEntry($logsImport);
        }

        $manager->flush();
    }

    public function getLogsImportFilepath(): ?string
    {
        return $this->logsImportFilepath;
    }

    public function setLogsImportFilepath(?string $logsImportFilepath): void
    {
        $this->logsImportFilepath = $logsImportFilepath;
    }
}
