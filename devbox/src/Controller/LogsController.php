<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\CQRS\QueryInterface;
use App\Core\Response\SuccessResponse;
use App\CQRS\Query\LogsEntriesCountQuery;
use App\Criteria\LogsImportEntryCountCriteria;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

class LogsController
{
    public function __construct(
        private readonly MessageBusInterface $queryBus
    ) {}

    #[Route('/count', methods: ['GET'])]
    public function count(Request $request): Response
    {
        $criteria = new LogsImportEntryCountCriteria($request);
        $result = $this->dispatchQuery(LogsEntriesCountQuery::createFromCriteria($criteria));

        return new SuccessResponse(['counter' => $result]);
    }

    //TODO: Abstract into dedicated QueryBus
    private function dispatchQuery(QueryInterface $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);

        return $envelope->last(HandledStamp::class)->getResult();
    }
}
