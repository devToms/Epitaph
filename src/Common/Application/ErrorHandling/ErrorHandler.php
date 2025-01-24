<?php

declare(strict_types=1);

namespace App\Common\Application\ErrorHandling;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class ErrorHandler 
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function handleError(string $message): CommandResult
    {
        $this->logger->error($message);
        return new CommandResult(
            success: false,
            statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
            message: $message
        );
    }

    public function handleException(\Throwable $exception): CommandResult
    {
        $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        return new CommandResult(
            success: false,
            statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
            message: $exception->getMessage()
        );
    }
}

