<?php

declare(strict_types=1);

namespace App\Common\Application\Response;

use App\Common\Application\BusResult\BusResultInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

interface ResponseBuilderInterface
{
    public function buildResponse(BusResultInterface $result, ?string $successMessage = null, ?string $errorMessage = null): JsonResponse;
}
