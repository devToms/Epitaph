<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\Command\CreateCart;

use App\Module\Commerce\Domain\Entity\Cart;
use App\Module\Commerce\Domain\Entity\Client;
use App\Module\Commerce\Domain\Repository\CartRepositoryInterface; 
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Common\Application\BusResult\CommandResult;
use App\Common\Application\Command\CommandHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

#[AsMessageHandler]
class CreateCartCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        protected EntityManagerInterface $entityManager,
        protected LoggerInterface $logger
    ) {}

    public function __invoke(CreateCartCommand $command): CommandResult
    {
        try {
            $clientReference = $this->entityManager->getReference(Client::class, $command->dto->clientUuid);
            $cart = new Cart($clientReference);
            $this->cartRepository->save($cart, true);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
            return new CommandResult(success: false, statusCode: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new CommandResult(success: true, statusCode: Response::HTTP_CREATED);
    }
}
