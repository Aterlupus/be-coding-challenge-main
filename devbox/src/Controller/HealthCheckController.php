<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthCheckController extends AbstractController
{
    #[Route('/healthz', methods: ['GET'])]
    public function index(): Response
    {
        return new Response('LGTM 👍');
    }
}
