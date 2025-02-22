<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\Command\UpdateCart;

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
readonly class UpdateCartCommandHandler implements CommandHandlerInterface
{
    protected CacheProxyInterface $cache;

    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        private readonly ProductRepositoryInterface $productRepository,
        protected EntityManagerInterface $entityManager,
        protected LoggerInterface $logger,
        CacheCreatorInterface $cacheCreator,
    ) {
    }

    public function __invoke(UpdateCartCommand $command): CommandResult
    {
        try {
           
            $cart = $this->entityManager->getReference(Cart::class, $command->cartUuid);
            
            if (!$cart) {
                throw new \InvalidArgumentException('Cart not found.');
            }
    
            $product = $this->productRepository->find($command->productId);
            if (!$product) {
                throw new \InvalidArgumentException('Product not found.');
            }
         
            $cart->updateItemQuantity($product, $command->dto->quantity);
            $cart->updateItemPrice($product);

            $this->cartRepository->save($cart, true);
          

        } catch (Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
            return new CommandResult(success: false, statusCode: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new CommandResult(success: true, statusCode: Response::HTTP_OK);
    }
}
