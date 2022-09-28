<?php
declare(strict_types=1);

namespace App\Command;

use App\Core\File\FileHandler;
use App\CQRS\Command\CreateLogsEntryCommand;
use App\CQRS\Command\CreateLogsImportCommand;
use App\Entity\LogsImport;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Iterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Uid\Uuid;

class ImportLogsConsoleCommand extends Command
{
    private const LOG_ENTRY_REGEX = '#(\S*) - - \[(.*)\] \"(\S*) (\S*) (\S*)\" (\d*)#';

    private $processedLinesCount = 0;

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

    //TODO: flush batches
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
        $output->writeln(sprintf('%d lines processed in "%s s"', $this->processedLinesCount, $stopwatch->getEndTime() / 1000));

        return 0;
    }

    private function processLogsFile(LogsImport $logsImport, string $filepath): void
    {
        foreach (self::getLogFileIterator($filepath, self::getLastReadLine($logsImport)) as $line) {
            if (0 !== strlen($line)) {
                $this->storeLogLine($logsImport->getUuid(), $line);
                $this->processedLinesCount++;
            }
        }
    }

    private static function getLastReadLine(?LogsImport $logsImport): int
    {
        if (null !== $logsImport) {
            return $logsImport->getLogsEntries()->count();
        } else {
            return 0;
        }
    }

    private static function getLogFileIterator(string $filepath, int $lastReadLine): Iterator
    {
        return FileHandler::getIterator($filepath, $lastReadLine);
    }

    private function storeLogLine(Uuid $logsImportUuid, string $line): void
    {
        list(
            $line,
            $serviceName,
            $dateTimeString,
            $method,
            $uri,
            $protocolVersion,
            $responseCode
        ) = self::getParsedLogLine($line);

        $this->commandBus->dispatch(new CreateLogsEntryCommand(
            $logsImportUuid,
            $serviceName,
            new DateTime($dateTimeString),
            $method,
            $uri,
            $protocolVersion,
            (int) $responseCode
        ));
    }

    private static function getParsedLogLine(string $line): array
    {
        preg_match(self::LOG_ENTRY_REGEX, $line, $matches);
        return $matches;
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

    private function getLogsImport(array $criteria): ?LogsImport
    {
        return $this->entityManager->getRepository(LogsImport::class)->findOneBy($criteria);
    }
}
