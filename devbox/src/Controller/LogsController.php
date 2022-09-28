<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\CQRS\QueryInterface;
use App\CQRS\Query\LogsEntriesCountQuery;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

class LogsController
{
    public function __construct(private readonly MessageBusInterface $queryBus)
    {
    }

    #[Route('/count', methods: ['GET'])]
    public function count(Request $request): Response
    {
        $serviceNames = $request->get('serviceNames');
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $statusCode = $request->get('statusCode');

        if (null !== $serviceNames && false === is_array($serviceNames)) {
            return new JsonResponse(['error' => 'serviceNames parameter value must be an array'], Response::HTTP_BAD_REQUEST);
        }

        if (null !== $startDate && false === self::isValidDate($startDate)) {
            return new JsonResponse(['error' => 'Invalid startDate parameter value'], Response::HTTP_BAD_REQUEST);
        }

        if (null !== $endDate && false === self::isValidDate($endDate)) {
            return new JsonResponse(['error' => 'Invalid endDate parameter value'], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->dispatchQuery(new LogsEntriesCountQuery(
            $serviceNames,
            null !== $startDate ? new DateTime($startDate) : null,
            null !== $endDate ? new DateTime($endDate) : null,
            null !== $statusCode ? (int) $statusCode : null,
        ));

        return new JsonResponse(['counter' => $result]);
    }

    private static function isValidDate(?string $date, string $format = 'Y-m-d'): bool
    {
        if (null === $date) {
            return true;
        } else {
            $dateTime = DateTime::createFromFormat($format, $date);
            return $dateTime && $dateTime->format($format) === $date;
        }
    }

    //TODO: Abstract into dedicated QueryBus
    private function dispatchQuery(QueryInterface $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);

        return $envelope->last(HandledStamp::class)->getResult();
    }
}