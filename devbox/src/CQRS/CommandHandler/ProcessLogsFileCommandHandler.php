<?php
declare(strict_types=1);

namespace App\CQRS\CommandHandler;

use App\Core\CQRS\AbstractCommandHandler;
use App\Core\File\FileHandler;
use App\CQRS\Command\ProcessLogsFileCommand;
use App\Entity\LogsEntry;
use App\Entity\LogsImport;
use DateTime;
use Iterator;

class ProcessLogsFileCommandHandler extends AbstractCommandHandler
{
    private const LOG_ENTRY_REGEX = '#(\S*) - - \[(.*)\] \"(\S*) (\S*) (\S*)\" (\d*)#';

    private const BATCH_SIZE = 1000;

    private int $processedLinesCount = 0;


    public function __invoke(ProcessLogsFileCommand $command): void
    {
        $logsImport = $this->getLogsImport(['uuid' => $command->getLogsImportUuid()]);

        foreach (self::getLogFileIterator($command->getFilepath(), self::getLastReadLine($logsImport)) as $line) {
            if (self::isLineNonEmpty($line)) {
                $this->storeLogLine($logsImport, $line);
                $this->processedLinesCount++;
                $this->flushBatch();
            }
        }

        $this->getEntityManager()->flush();
    }

    private static function isLineNonEmpty(string $line): bool
    {
        return 0 !== strlen($line);
    }

    private function flushBatch(): void
    {
        if (0 === $this->processedLinesCount % self::BATCH_SIZE) {
            $this->getEntityManager()->flush();
        }
    }

    private static function getLastReadLine(?LogsImport $logsImport): int
    {
        return $logsImport?->getLogsEntries()->count() ?? 0;
    }

    private static function getLogFileIterator(string $filepath, int $lastReadLine): Iterator
    {
        return FileHandler::getIterator($filepath, $lastReadLine);
    }

    private function storeLogLine(LogsImport $logsImport, string $line): void
    {
        list(
            $line,
            $serviceName,
            $dateTimeString,
            $method,
            $uri,
            $protocolVersion,
            $statusCode
        ) = self::getParsedLogLine($line);

        $this->persistLogsEntry(
            $logsImport,
            $serviceName,
            new DateTime($dateTimeString),
            $method,
            $uri,
            $protocolVersion,
            (int) $statusCode
        );
    }

    private function persistLogsEntry(
        LogsImport $logsImport,
        string $serviceName,
        DateTime $dateTime,
        string $method,
        string $uri,
        string $protocolVersion,
        int $statusCode
    ): void {
        $logsEntry = new LogsEntry;

        $logsEntry->setServiceName($serviceName);
        $logsEntry->setDateTime($dateTime);
        $logsEntry->setMethod($method);
        $logsEntry->setUri($uri);
        $logsEntry->setProtocolVersion($protocolVersion);
        $logsEntry->setStatusCode($statusCode);
        $logsEntry->setLogsImport($logsImport);

        $this->getEntityManager()->persist($logsEntry);
    }

    private static function getParsedLogLine(string $line): array
    {
        preg_match(self::LOG_ENTRY_REGEX, $line, $matches);
        return $matches;
    }

    private function getLogsImport(array $criteria): ?LogsImport
    {
        return $this->getEntityManager()->getRepository(LogsImport::class)->findOneBy($criteria);
    }
}
