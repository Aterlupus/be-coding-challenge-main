<?php
declare(strict_types=1);

namespace App\Command;

use App\CQRS\Command\CreateLogsImportCommand;
use App\CQRS\Command\ProcessLogsFileCommand;
use App\Entity\LogsImport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Uid\Uuid;

class ImportLogsConsoleCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $commandBus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:import-logs')
            ->setDescription('Imports logs from file stored in ./logs directory into database')
            ->addArgument('filepath', InputArgument::REQUIRED, 'Name of the logs file store in "logs" directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = (new Stopwatch)->start('');
        $filepath = $input->getArgument('filepath');

        if (false === is_file($filepath)) {
            $output->writeln(sprintf('Invalid filepath "%s"', $filepath));
            return 1;
        }

        $logsImport = $this->getOrCreateLogsImport($filepath);
        $this->processLogsFile($logsImport, $filepath);

        $stopwatch->stop();
        $output->writeln(sprintf('%d lines processed in "%s s"', $this->getLogsImportLinesCountDifference($logsImport), $stopwatch->getEndTime() / 1000));

        return 0;
    }

    private function getOrCreateLogsImport(string $filepath): ?LogsImport
    {
        $logsImport = $this->getLogsImport(['filepath' => $filepath]);

        if (null !== $logsImport) {
            return $logsImport;
        } else {
            $logsImportUuid = Uuid::v6();
            $this->commandBus->dispatch(new CreateLogsImportCommand($logsImportUuid, $filepath));
            return $this->getLogsImport(['uuid' => $logsImportUuid]);
        }
    }

    private function processLogsFile(LogsImport $logsImport, string $filepath): void
    {
        $this->commandBus->dispatch(new ProcessLogsFileCommand($logsImport->getUuid(), $filepath));
    }

    private function getLogsImportLinesCountDifference(LogsImport $logsImport): int
    {
        $oldLinesCount = $logsImport->getLogsEntriesCount();

        $newLogsImport = $this->getLogsImport(['id' => $logsImport->getId()]);
        $this->entityManager->refresh($newLogsImport);
        $newLinesCounts = $newLogsImport->getLogsEntriesCount();

        return $newLinesCounts - $oldLinesCount;
    }

    private function getLogsImport(array $criteria): ?LogsImport
    {
        return $this->entityManager->getRepository(LogsImport::class)->findOneBy($criteria);
    }
}
