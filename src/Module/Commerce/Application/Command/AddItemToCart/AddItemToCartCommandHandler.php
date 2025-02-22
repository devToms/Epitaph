<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\Command\AddItemToCart;

use App\Module\Commerce\Domain\Entity\Cart;
use App\Module\Commerce\Infrastructure\Doctrine\Repository\CartRepository;
use App\Module\Commerce\Domain\Repository\CartRepositoryInterface; 
use App\Module\Commerce\Infrastructure\Doctrine\Repository\ProductRepository;
use App\Module\Commerce\Domain\Repository\ProductRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Common\Application\BusResult\CommandResult;
use App\Common\Application\Command\CommandHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

#[AsMessageHandler]
class AddItemToCartCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        protected EntityManagerInterface $entityManager,
        private readonly ProductRepositoryInterface $productRepository,
        protected LoggerInterface $logger
    ) {}

    public function __invoke(AddItemToCartCommand $command): CommandResult
    {
        try {
            
            $cart = $this->cartRepository->find($command->dto->cartUuid);

            if (!$cart) {
                return new CommandResult(success: false, statusCode: Response::HTTP_NOT_FOUND, message: "Koszyk nie znaleziony.");
            }
    
            $product = $this->productRepository->find($command->dto->productId);
    
            if (!$product) {
                return new CommandResult(success: false, statusCode: Response::HTTP_NOT_FOUND, message: "Produkt nie znaleziony.");
            }

            if ($command->dto->quantity <= 0) {
                return new CommandResult(success: false, statusCode: Response::HTTP_BAD_REQUEST, message: "Nieprawidłowa ilość produktu.");
            }
    
            $cart->addItem($product, $command->dto->quantity);

            $this->cartRepository->save($cart, true);
           
        } catch (Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
            return new CommandResult(success: false, statusCode: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new CommandResult(success: true, statusCode: Response::HTTP_CREATED);
    }
}
