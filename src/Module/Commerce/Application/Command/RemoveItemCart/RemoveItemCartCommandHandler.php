<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\Command\RemoveItemCart;

use App\Common\Domain\Cache\CacheCreatorInterface;
use App\Common\Domain\Cache\CacheProxyInterface;
use App\Module\Commerce\Domain\Repository\ProductRepositoryInterface;
use App\Common\Application\BusResult\CommandResult;
use App\Common\Application\Command\CommandHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
readonly class RemoveItemCartCommandHandler implements CommandHandlerInterface
{
    protected CacheProxyInterface $cache;

    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected LoggerInterface $logger,
        CacheCreatorInterface $cacheCreator,
    ) {
        $this->cache = $cacheCreator->create('query.products.findProductBySlugQuery.');
    }

    public function __invoke(RemoveItemCartCommand $command): CommandResult
    {
        try {
            $cart = $this->cartRepository->find($command->cartUuid);

       
        } catch (Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
            return new CommandResult(success: false, statusCode: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new CommandResult(success: true, statusCode: Response::HTTP_ACCEPTED);
    }
}
