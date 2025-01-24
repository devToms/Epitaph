<?php

declare(strict_types=1);

namespace App\Common\Application\Bus\QueryBus;

use App\Common\Application\BusResult\QueryResult;
use App\Common\Application\Query\QueryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Throwable;

readonly class QueryBus implements QueryBusInterface
{
    public function __construct(
        protected MessageBusInterface $bus,
        protected LoggerInterface $logger,
    ) {
    }

    public function handle(QueryInterface $query): QueryResult
    {
        try {
         
            $this->logger->info('Dispatching query', ['query' => get_class($query)]);
            
            $envelope = $this->bus->dispatch($query);
            $handledStamps = $envelope->all(HandledStamp::class);

            if (count($handledStamps) === 1) {
                $queryResult = $handledStamps[0]->getResult();
                if ($queryResult instanceof QueryResult) {
                    return $queryResult;
                }
            }

            throw new \RuntimeException('Invalid QueryResult');
        } catch (HandlerFailedException $exception) {
          
            $this->logger->error('Query handler failed', [
                'query' => get_class($query),
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return new QueryResult(
                success: false,
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        } catch (Throwable $exception) {
          
            $this->logger->critical('Unexpected error while dispatching query', [
                'query' => get_class($query),
                'error' => $exception->getMessage(),
            ]);

       
            return new QueryResult(
                success: false,
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
