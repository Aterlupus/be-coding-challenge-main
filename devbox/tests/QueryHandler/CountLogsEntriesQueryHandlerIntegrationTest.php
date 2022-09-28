<?php
declare(strict_types=1);

namespace Tests\QueryHandler;

use App\Core\CQRS\QueryInterface;
use App\CQRS\Query\LogsEntriesCountQuery;
use App\Entity\LogsEntry;
use App\Entity\LogsImport;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Tests\Fixtures\EntityGenerator;

class CountLogsEntriesQueryHandlerIntegrationTest extends KernelTestCase
{
    private EntityGenerator $entityGenerator;

    private EntityManagerInterface $entityManager;

    private MessageBusInterface $queryBus;


    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $this->entityGenerator = new EntityGenerator($this->entityManager);
        $this->queryBus = $this->getContainer()->get('query.bus');
        $this->entityManager->beginTransaction();
    }

    public function testItGetsCountFilteredByServiceName()
    {
        $logsImport = $this->entityGenerator->getLogsImport();

        $this->getLogsEntryWithServiceName($logsImport, 'SER1');
        $this->getLogsEntryWithServiceName($logsImport, 'SER1');

        $this->getLogsEntryWithServiceName($logsImport, 'SER2');
        $this->getLogsEntryWithServiceName($logsImport, 'SER2');
        $this->getLogsEntryWithServiceName($logsImport, 'SER2');

        $this->getLogsEntryWithServiceName($logsImport, 'SER3');
        $this->getLogsEntryWithServiceName($logsImport, 'SER3');
        $this->getLogsEntryWithServiceName($logsImport, 'SER3');
        $this->getLogsEntryWithServiceName($logsImport, 'SER3');

        $this->entityManager->flush();

        self::assertEquals(0, $this->dispatchQuery(new LogsEntriesCountQuery(servicesNames: ['NON'])));
        self::assertEquals(2, $this->dispatchQuery(new LogsEntriesCountQuery(servicesNames: ['SER1'])));
        self::assertEquals(2, $this->dispatchQuery(new LogsEntriesCountQuery(servicesNames: ['SER1', 'NON'])));
        self::assertEquals(3, $this->dispatchQuery(new LogsEntriesCountQuery(servicesNames: ['SER2'])));
        self::assertEquals(4, $this->dispatchQuery(new LogsEntriesCountQuery(servicesNames: ['SER3'])));

        self::assertEquals(5, $this->dispatchQuery(new LogsEntriesCountQuery(servicesNames: ['SER1', 'SER2'])));
        self::assertEquals(7, $this->dispatchQuery(new LogsEntriesCountQuery(servicesNames: ['SER2', 'SER3'])));
        self::assertEquals(9, $this->dispatchQuery(new LogsEntriesCountQuery(servicesNames: ['SER1', 'SER2', 'SER3'])));
    }

    private function getLogsEntryWithServiceName(LogsImport $logsImport, string $serviceName): LogsEntry
    {
        $logsEntry1 = $this->entityGenerator->getLogsEntry($logsImport);
        $logsEntry1->setServiceName($serviceName);

        return $logsEntry1;
    }

    public function testItGetsCountFilteredByDateRange()
    {
        $logsImport = $this->entityGenerator->getLogsImport();

        $this->getLogsEntryWithDateTime($logsImport, new DateTime('2020-01-01'));
        $this->getLogsEntryWithDateTime($logsImport, new DateTime('2020-02-01'));
        $this->getLogsEntryWithDateTime($logsImport, new DateTime('2020-03-01'));
        $this->getLogsEntryWithDateTime($logsImport, new DateTime('2020-04-01'));
        $this->getLogsEntryWithDateTime($logsImport, new DateTime('2020-05-01'));
        $this->getLogsEntryWithDateTime($logsImport, new DateTime('2020-06-01'));

        $this->entityManager->flush();

        self::assertEquals(6, $this->dispatchQuery(new LogsEntriesCountQuery(startDate: new DateTime('2019-01-01'))));
        self::assertEquals(4, $this->dispatchQuery(new LogsEntriesCountQuery(startDate: new DateTime('2020-03-01'))));
        self::assertEquals(0, $this->dispatchQuery(new LogsEntriesCountQuery(startDate: new DateTime('2020-09-01'))));

        self::assertEquals(6, $this->dispatchQuery(new LogsEntriesCountQuery(endDate: new DateTime('2022-01-01'))));
        self::assertEquals(2, $this->dispatchQuery(new LogsEntriesCountQuery(endDate: new DateTime('2020-02-01'))));
        self::assertEquals(0, $this->dispatchQuery(new LogsEntriesCountQuery(endDate: new DateTime('2018-01-01'))));

        self::assertEquals(3, $this->dispatchQuery(new LogsEntriesCountQuery(startDate: new DateTime('2020-03-01'), endDate: new DateTime('2020-05-01'))));
        self::assertEquals(1, $this->dispatchQuery(new LogsEntriesCountQuery(startDate: new DateTime('2020-03-02'), endDate: new DateTime('2020-04-30'))));
    }

    private function getLogsEntryWithDateTime(LogsImport $logsImport, DateTime $dateTime): LogsEntry
    {
        $logsEntry1 = $this->entityGenerator->getLogsEntry($logsImport);
        $logsEntry1->setDateTime($dateTime);

        return $logsEntry1;
    }

    public function testItGetsCountFilteredByStatusCode()
    {
        $logsImport = $this->entityGenerator->getLogsImport();

        $this->getLogsEntryWithStatusCode($logsImport, 200);
        $this->getLogsEntryWithStatusCode($logsImport, 200);

        $this->getLogsEntryWithStatusCode($logsImport, 204);
        $this->getLogsEntryWithStatusCode($logsImport, 204);
        $this->getLogsEntryWithStatusCode($logsImport, 204);

        $this->getLogsEntryWithStatusCode($logsImport, 404);
        $this->getLogsEntryWithStatusCode($logsImport, 404);
        $this->getLogsEntryWithStatusCode($logsImport, 404);
        $this->getLogsEntryWithStatusCode($logsImport, 404);

        $this->entityManager->flush();

        self::assertEquals(2, $this->dispatchQuery(new LogsEntriesCountQuery(statusCode: 200)));
        self::assertEquals(3, $this->dispatchQuery(new LogsEntriesCountQuery(statusCode: 204)));
        self::assertEquals(4, $this->dispatchQuery(new LogsEntriesCountQuery(statusCode: 404)));
    }

    private function getLogsEntryWithStatusCode(LogsImport $logsImport, int $statusCode): LogsEntry
    {
        $logsEntry1 = $this->entityGenerator->getLogsEntry($logsImport);
        $logsEntry1->setStatusCode($statusCode);

        return $logsEntry1;
    }

    private function dispatchQuery(QueryInterface $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);

        return $envelope->last(HandledStamp::class)->getResult();
    }
}
