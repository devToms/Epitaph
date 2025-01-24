<?php

declare(strict_types=1);

namespace App\Module\Commerce\UI\Controller;

use App\Module\Commerce\Application\Command\AddItemToCart\AddItemToCartCommand;
use App\Module\Commerce\Application\Command\UpdateCart\UpdateCartCommand;
use App\Module\Commerce\Application\Command\RemoveItemFromCart\RemoveItemFromCartCommand;
use App\Module\Commerce\Application\Query\GetCartItems\GetCartItemsQuery;
use App\Module\Commerce\Application\DTO\AddItemToCartDTO;
use App\Module\Commerce\Application\DTO\UpdateItemCartDTO;
use App\Module\Commerce\Application\DTO\RemoveItemFromCartDTO;
use App\Module\Commerce\Application\Query\FindCartById\FindCartByIdQuery;
use App\Common\Application\Bus\CommandBus\CommandBusInterface;
use App\Common\Application\Bus\QueryBus\QueryBusInterface;
use App\Common\Application\Response\ResponseBuilderInterface;
use App\Common\Application\DTO\PaginationUuidDTO;
use App\Module\Commerce\Domain\Entity\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/cart')]
class CartController extends AbstractController
{
    public function __construct(
        protected readonly CommandBusInterface $commandBus,
        protected readonly QueryBusInterface $queryBus,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly ResponseBuilderInterface $responseBuilder
    ) {
    }

    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Add an item to the cart',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(property: 'message', type: 'string'),
            ],
        ),
    )]
    #[OA\RequestBody(content: new Model(type: AddItemToCartDTO::class, groups: ['default']))]
    #[Route('/add-item', methods: [Request::METHOD_POST])]
    #[IsGranted('ROLE_USER')]
    public function addItemToCart(#[ValueResolver('add_item_to_cart_dto')] AddItemToCartDTO $dto): Response
    {
        if ($dto->hasErrors()) {
            return $this->json([
                'success' => false,
                'errors' => $dto->getErrors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $commandResult = $this->commandBus->handle(new AddItemToCartCommand($dto));

        return $this->responseBuilder->buildResponse(
            $commandResult, 
            'Cart add successfully.', 
            'Failed to adding order'
        );
    }

    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Update items in the cart',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(property: 'message', type: 'string'),
            ],
        ),
    )]
    #[OA\RequestBody(content: new Model(type: UpdateItemCartDTO::class, groups: ['default']))]
    #[Route('/update/{id}', methods: [Request::METHOD_PUT])]
    #[IsGranted('ROLE_USER')]
    public function updateItemsCart(
        #[ValueResolver('update_item_cart_dto')] UpdateItemCartDTO $dto,
        string $id,
        ): Response
    {
        if ($dto->hasErrors()) {
            return $this->json([
                'success' => false,
                'errors' => $dto->getErrors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $queryResult = $this->queryBus->handle(new FindCartByIdQuery($id));
       
       
        if ($queryResult->data !== null) {
            $cart = $this->entityManager->getReference(Cart::class, $queryResult->data['id']);
            $commandResult = $this->commandBus->handle(new UpdateCartCommand($cart, $dto));
        }

        return $this->responseBuilder->buildResponse(
            $commandResult, 
            'Cart updated successfully.', 
            'Failed to update cart'
        );
    }
}
