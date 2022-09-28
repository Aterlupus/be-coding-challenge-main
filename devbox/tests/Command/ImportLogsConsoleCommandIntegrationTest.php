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
        $commandObject = new ImportLogsConsoleCommand($this->entityManager, $this->getContainer()->get('command.bus'));
        $commandTester = $this->getCommandTester($commandObject);

        self::assertEquals(0, $this->entityManager->getRepository(LogsEntry::class)->count([]));

        $commandTester->execute(['filepath' => 'logs/logs.txt']);

        self::assertEquals(20, $this->entityManager->getRepository(LogsEntry::class)->count([]));
        $this->entityManager->getConnection()->prepare('DELETE FROM logs_entry WHERE 1=1')->executeStatement(); //TODO: Discover why beginTransaction/rollback method doesn't work and replace with safer solution
        $this->entityManager->getConnection()->prepare('DELETE FROM logs_import WHERE 1=1')->executeStatement();
    }

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
}
