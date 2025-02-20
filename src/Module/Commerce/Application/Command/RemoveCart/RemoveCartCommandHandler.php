<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\Command\RemoveCart;

use App\Common\Domain\Cache\CacheCreatorInterface;
use App\Common\Domain\Cache\CacheProxyInterface;
use App\Module\Commerce\Domain\Entity\Cart;
use App\Module\Commerce\Domain\Repository\CartRepositoryInterface;
use App\Module\Commerce\Infrastructure\Doctrine\Repository\ProductRepository;
use App\Module\Commerce\Domain\Repository\ProductRepositoryInterface;
use App\Common\Application\Command\CommandHandlerInterface;
use App\Common\Application\BusResult\CommandResult;
use App\Common\Application\Command\CommandInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
readonly class RemoveCartCommandHandler implements CommandHandlerInterface
{
    protected CacheProxyInterface $cache;

    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected EntityManagerInterface $entityManager,
        protected LoggerInterface $logger,
        CacheCreatorInterface $cacheCreator,
    ) {
    }

    public function __invoke(RemoveCartCommand $command): CommandResult
    {
        $cart = $this->cartRepository->find($command->cartUuid);
        
        if (!$cart) {
            return new CommandResult(success: false, statusCode: Response::HTTP_NOT_FOUND);
        }

        try {
            if (!$this->cartRepository->softDelete($cart)) {
                return new CommandResult(success: false, statusCode: Response::HTTP_NOT_FOUND);
            }
        } catch (Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
            return new CommandResult(success: false, statusCode: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new CommandResult(success: true, statusCode: Response::HTTP_OK);
    }
}
