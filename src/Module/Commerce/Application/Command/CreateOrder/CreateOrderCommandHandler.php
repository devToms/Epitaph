<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\Command\CreateOrder;

use App\Module\Commerce\Domain\Entity\Order;
use App\Module\Commerce\Domain\Repository\OrderRepositoryInterface;
use App\Module\Commerce\Domain\Repository\CartRepositoryInterface; // Dodane repozytorium koszyka
use App\Module\Commerce\Infrastructure\Doctrine\Repository\CartRepository;
use App\Module\Commerce\Domain\Entity\Product;
use App\Module\Commerce\Domain\Entity\Cart;
use App\Module\Auth\Domain\Entity\User;
use App\Module\Commerce\Domain\Entity\Client;
use App\Common\Application\BusResult\CommandResult;
use App\Common\Application\Command\CommandHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Throwable;
use App\Module\Commerce\Domain\Enum\OrderStatus;

#[AsMessageHandler]
readonly class CreateOrderCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected CartRepositoryInterface $cartRepository, 
        protected EntityManagerInterface $entityManager,
        protected LoggerInterface $logger,
        protected TokenStorageInterface $tokenStorage
    ) {
    }

    public function __invoke(CreateOrderCommand $command): CommandResult
    {
        $token = $this->tokenStorage->getToken();

        if (!$token || !$token->getUser() instanceof User) {

            throw new \LogicException('Nie znaleziono zalogowanego użytkownika.');
        }

        $user = $token->getUser();
        $client = new Client($user->getEmail(), $user->getName(), $user->getSurname());
        
        $this->logger->debug('Dane wejściowe zamówienia', [
            'cartId' => $command->dto->cartUuid,
            'user' => $user->getEmail(),
        ]);

        try {
            $cart = $this->cartRepository->findByIdWithFind($command->dto->cartUuid);
            
            if (!$cart) {
                throw new \InvalidArgumentException('Koszyk o podanym ID nie istnieje.');
            }

            $order = new Order($client);
            foreach ($cart->getItems() as $item) {
            
                $order->addProductToOrder(
                    $item->getProduct(),
                    $item->getQuantity(),
                    $item->getSubtotal()
                );
            }
            $order->updateOrderStatus(OrderStatus::WAITING_FOR_PAYMENT);
            $this->orderRepository->save($order, true);

        } catch (Throwable $throwable) {
            $this->logger->error('Błąd w CreateOrderCommandHandler', [
                'message' => $throwable->getMessage(),
                'exception' => get_class($throwable),
                'trace' => $throwable->getTraceAsString(),
            ]);
            return new CommandResult(success: false, statusCode: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new CommandResult(success: true, statusCode: Response::HTTP_CREATED);
    }
}


