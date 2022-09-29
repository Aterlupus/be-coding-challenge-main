<?php
declare(strict_types=1);

namespace App\Core\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SuccessResponse extends JsonResponse
{
    public function __construct(array $data)
    {
        parent::__construct($data, Response::HTTP_OK);
    }
}
