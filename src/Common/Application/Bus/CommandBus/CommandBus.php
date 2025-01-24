<?php

declare(strict_types=1);

namespace App\Common\Application\Bus\CommandBus;

use App\Common\Application\BusResult\CommandResult;
use App\Common\Application\Command\CommandInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Throwable;

readonly class CommandBus implements CommandBusInterface
{
    public function __construct(
        protected MessageBusInterface $bus,
        protected LoggerInterface $logger,
    ) {
    }

    public function handle(CommandInterface $command): CommandResult
    {
        try {
           
            $this->logger->info('Dispatching command', ['command' => get_class($command)]);
            
            $envelope = $this->bus->dispatch($command);
            $handledStamps = $envelope->all(HandledStamp::class);

            if (count($handledStamps) === 1) {
                $commandResult = $handledStamps[0]->getResult();
                if ($commandResult instanceof CommandResult) {
                    return $commandResult;
                }
            }

            throw new \RuntimeException('Invalid CommandResult');
            
        } catch (HandlerFailedException $exception) {
           
            $this->logger->error('Command handler failed', [
                'command' => get_class($command),
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

           
            return new CommandResult(
                success: false,
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        } catch (Throwable $exception) {
        
            $this->logger->critical('Unexpected error while dispatching command', [
                'command' => get_class($command),
                'error' => $exception->getMessage(),
            ]);

            return new CommandResult(
                success: false,
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
