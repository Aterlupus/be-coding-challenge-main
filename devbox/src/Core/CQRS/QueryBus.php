<?php
declare(strict_types=1);

namespace App\Core\CQRS;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class QueryBus
{
    public function __construct(
        private readonly MessageBusInterface $queryBus
    ) {}

    public function dispatch(object $message, array $stamps = []): mixed
    {
        $envelope = $this->queryBus->dispatch($message, $stamps);
        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        return $stamp->getResult();
    }
}
