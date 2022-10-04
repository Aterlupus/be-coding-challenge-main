<?php
declare(strict_types=1);

namespace Tests\Command;

use App\Command\ImportLogsConsoleCommand;
use App\Entity\LogsEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Fixtures\EntityGenerator;
use Tests\Fixtures\LogsFixtures;

class ImportLogsConsoleCommandIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;


    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);
    }

    public function testItImportsLogsFileToDatabase()
    {
        $this->clearTestEntities();
        $commandObject = new ImportLogsConsoleCommand($this->entityManager, $this->getContainer()->get('command.bus'));
        $commandTester = $this->getCommandTester($commandObject);

        self::assertEquals(0, $this->getLogsEntryCount());

        $commandTester->execute(['filepath' => 'logs/logs.txt']);

        self::assertEquals(20, $this->getLogsEntryCount());
        $this->clearTestEntities();
    }

    public function testItResumesImportOfLogsFileToDatabase()
    {
        $this->clearTestEntities();
        $commandObject = new ImportLogsConsoleCommand($this->entityManager, $this->getContainer()->get('command.bus'));
        $commandTester = $this->getCommandTester($commandObject);

        $fixtures = new LogsFixtures(new EntityGenerator($this->entityManager));
        $fixtures->setLogsImportFilepath('logs/logs.txt');
        $fixtures->load($this->entityManager);

        self::assertEquals(10, $this->getLogsEntryCount());

        $commandTester->execute(['filepath' => 'logs/logs.txt']);

        self::assertEquals(20, $this->getLogsEntryCount());
        $this->clearTestEntities();
    }

    /*
     * Helpers
     */

    private function getCommandTester(
        Command $commandObject,
        array $inputs = []
    ): CommandTester {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $application->add($commandObject);

        $command = $application->find('app:import-logs');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs($inputs);

        return $commandTester;
    }

    private function getLogsEntryCount(array $criteria = []): int
    {
        return $this->entityManager->getRepository(LogsEntry::class)->count($criteria);
    }

    /**
     * TODO: Discover why beginTransaction/rollback method doesn't work and replace with safer solution
     */
    private function clearTestEntities(): void
    {
        $this->truncateTable('logs_entry');
        $this->truncateTable('logs_import');
    }

    private function truncateTable(string $tableName): void
    {
        $this->entityManager->getConnection()->prepare(sprintf('DELETE FROM %s WHERE 1=1', $tableName))->executeStatement();
    }
}
