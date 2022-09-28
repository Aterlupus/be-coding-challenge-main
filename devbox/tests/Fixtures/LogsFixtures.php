<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LogsFixtures extends Fixture
{
    public function __construct(
        private readonly EntityGenerator $entityGenerator
    ) {}

    public function load(ObjectManager $manager): void
    {
        $logsImport = $this->entityGenerator->getLogsImport();

        for ($i = 0; $i < 10; $i++) {
            $this->entityGenerator->getLogsEntry($logsImport);
        }

        $manager->flush();
    }
}
