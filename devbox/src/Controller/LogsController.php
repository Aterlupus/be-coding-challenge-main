<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Response\SuccessResponse;
use App\CQRS\Query\LogsEntriesCountQuery;
use App\Criteria\LogsImportEntryCountCriteria;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogsController extends AbstractController
{
    #[Route('/count', methods: ['GET'])]
    public function count(Request $request): Response
    {
        $criteria = new LogsImportEntryCountCriteria($request);
        $result = $this->dispatchQuery(LogsEntriesCountQuery::createFromCriteria($criteria));

        return new SuccessResponse(['counter' => $result]);
    }
}
