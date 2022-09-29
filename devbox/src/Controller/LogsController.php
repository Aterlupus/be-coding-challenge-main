<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\CQRS\QueryInterface;
use App\Core\DateTime\DateTimeValidator;
use App\Core\Response\BadRequestResponse;
use App\Core\Response\SuccessResponse;
use App\CQRS\Query\LogsEntriesCountQuery;
use DateTime;
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
        $serviceNames = $request->get('serviceNames');
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $statusCode = $request->get('statusCode');

        if (null !== $serviceNames && false === is_array($serviceNames)) {
            return new BadRequestResponse(['error' => 'serviceNames parameter value must be an array']);
        }

        if (null !== $startDate && false === DateTimeValidator::isValid($startDate)) {
            return new BadRequestResponse(['error' => 'Invalid startDate parameter value']);
        }

        if (null !== $endDate && false === DateTimeValidator::isValid($endDate)) {
            return new BadRequestResponse(['error' => 'Invalid endDate parameter value']);
        }

        $result = $this->dispatchQuery(new LogsEntriesCountQuery(
            $serviceNames,
            null !== $startDate ? new DateTime($startDate) : null,
            null !== $endDate ? new DateTime($endDate) : null,
            null !== $statusCode ? (int) $statusCode : null,
        ));

        return new SuccessResponse(['counter' => $result]);
    }

    //TODO: Abstract into dedicated QueryBus
    private function dispatchQuery(QueryInterface $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);

        return $envelope->last(HandledStamp::class)->getResult();
    }
}
