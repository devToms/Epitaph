<?php

declare(strict_types=1);

namespace App\Module\Commerce\UI\Controller;

use App\Module\Commerce\Application\Command\CreateCart\CreateCartCommand;
use App\Module\Commerce\Application\Command\AddItemToCart\AddItemToCartCommand;
use App\Module\Commerce\Application\Command\UpdateCart\UpdateCartCommand;
use App\Module\Commerce\Application\Command\RemoveCart\RemoveCartCommand;
use App\Module\Commerce\Application\Query\GetCartItems\GetCartItemsQuery;
use App\Module\Commerce\Application\DTO\CreateCartDTO;
use App\Module\Commerce\Application\DTO\AddItemToCartDTO;
use App\Module\Commerce\Application\DTO\UpdateItemCartDTO;
use App\Module\Commerce\Application\DTO\RemoveItemFromCartDTO;
use App\Module\Commerce\Application\Query\FindProductById\FindProductByIdQuery;
use App\Module\Commerce\Application\Query\FindCartById\FindCartByIdQuery;
use App\Common\Application\Bus\CommandBus\CommandBusInterface;
use App\Common\Application\Bus\QueryBus\QueryBusInterface;
use App\Common\Application\Response\ResponseBuilderInterface;
use App\Common\Application\DTO\PaginationUuidDTO;
use App\Module\Commerce\Domain\Entity\Cart;
use App\Module\Commerce\Domain\Entity\Product;
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
        response: Response::HTTP_OK,
        description: 'Return items in the cart',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(type: 'object'),
                ),
            ],
        ),
    )]
    #[Route('/items', methods: [Request::METHOD_GET])]
    #[IsGranted('ROLE_USER')]
    public function getCartItems(): Response
    {
        $queryResult = $this->queryBus->handle(new GetCartItemsQuery($this->getUser()->getId()));

        return $this->responseBuilder->buildResponse(
            $queryResult,
            'Items loaded successfully.',
            'Failed to get items.',
            $queryResult->data
        );  
    }

    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Create cart',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(property: 'message', type: 'string'),
            ],
        ),
    )]
    #[OA\RequestBody(content: new Model(type: CreateCartDTO::class, groups: ['default']))]
    #[Route('/create', methods: [Request::METHOD_POST])]
    #[IsGranted('ROLE_USER')]
    public function createCart(#[ValueResolver('create_cart_dto')] CreateCartDTO $dto): Response
    {
        if ($dto->hasErrors()) {
            return $this->json([
                'success' => false,
                'errors' => $dto->getErrors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $commandResult = $this->commandBus->handle(new CreateCartCommand($dto));

        return $this->responseBuilder->buildResponse(
            $commandResult, 
            'Cart was created successfully.', 
            'Failed create cart'
        );
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
            'Failed to adding item'
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
    #[Route('/update/{cartUuid}/{productId}', methods: [Request::METHOD_PUT])]
    #[IsGranted('ROLE_USER')]
    public function update(
        #[ValueResolver('update_item_cart_dto')] UpdateItemCartDTO $dto,
        string $cartUuid, 
        int $productId
    ): Response
    {
        if ($dto->hasErrors()) {
            return $this->json([
                'success' => false,
                'errors' => $dto->getErrors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $queryResult = $this->queryBus->handle(new FindCartByIdQuery($cartUuid));
        $queryProductResult = $this->queryBus->handle(new FindProductByIdQuery($productId));

        if ($queryResult->data !== null && $queryProductResult->data !== null) {
            $cart = $this->entityManager->getReference(Cart::class, $queryResult->data['id']);
            $product = $this->entityManager->getReference(Product::class, $queryProductResult->data['id']);

            $commandResult = $this->commandBus->handle(new UpdateCartCommand($cart->getId(), $product->getId(), $dto));

            return $this->responseBuilder->buildResponse(
                $commandResult, 
                'Cart updated successfully.', 
                'Failed to update cart'
            );
        }

        return $this->json([
            'success' => false,
            'errors' => ['Cart or product not found']
        ], Response::HTTP_NOT_FOUND);
    }

    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Remove an item from the cart',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(property: 'message', type: 'string'),
            ],
        ),
    )]
    #[Route('/remove-cart/{id}', methods: [Request::METHOD_POST])]
    #[IsGranted('ROLE_USER')]
    public function removeCart(string $id): Response
    {
        $queryResult = $this->queryBus->handle(new FindCartByIdQuery($id));

        if ($queryResult->data !== null) {
            $cart = $this->entityManager->getReference(Cart::class, $queryResult->data['id']);
            $commandResult = $this->commandBus->handle(new RemoveCartCommand($cart));
        }

        return $this->responseBuilder->buildResponse(
            $commandResult, 
            'Cart deleted successfully.', 
            'Failed to delete cart'
        );
    }
}
