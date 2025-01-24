<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\Query\FindCartById;

use App\Common\Domain\Serializer\JsonSerializerInterface;
use App\Module\Commerce\Domain\Repository\CartRepositoryInterface;
use App\Common\Application\BusResult\QueryResult;
use App\Common\Application\Query\QueryHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
readonly class FindCartByIdQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected JsonSerializerInterface $serializer,
        protected LoggerInterface $logger,
    ) {
    }

    public function __invoke(FindCartByIdQuery $query): QueryResult
    {
        $cart = $this->cartRepository->findById($query->id);
        if ($cart === null) {
            throw new \InvalidArgumentException('Cart not found');
        }
       
        $cartItems = $cart->getItems();

        $cartItemsData = [];
        foreach ($cartItems as $item) {
            $cartItemsData[] = [
                'id' => $item->getCart()->getId() ?? null, 
                'quantity' => $item->getQuantity() ?? 0, 
                'price' => $item->getPrice() ?? 0.0,  
            ];
        }
        try {
            if (null === $cart) {
                return new QueryResult(
                    success: false,
                    statusCode: Response::HTTP_NOT_FOUND,
                );
            }
        } catch (Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
            return new QueryResult(
                success: false,
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return new QueryResult(
            success: true,
            statusCode: Response::HTTP_OK,
            data:  $cartItemsData[0]    
        );
    }
}

