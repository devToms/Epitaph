<?php

declare(strict_types=1);

namespace App\Module\Commerce\UI\Controller;

use App\Module\Commerce\Application\Command\CreateProduct\CreateProductCommand;
use App\Module\Commerce\Application\Command\DeleteProduct\DeleteProductCommand;
use App\Module\Commerce\Application\Command\UpdateProduct\UpdateProductCommand;
use App\Module\Commerce\Application\DTO\CreateProductDTO;
use App\Module\Commerce\Application\DTO\UpdateProductDTO;
use App\Module\Commerce\Application\Query\FindProductById\FindProductByIdQuery;
use App\Module\Commerce\Application\Query\FindProductBySlug\FindProductBySlugQuery;
use App\Module\Commerce\Application\Query\GetPaginatedProducts\GetPaginatedProductsQuery;
use App\Module\Commerce\Application\Voter\ProductsVoter;
use App\Module\Commerce\Domain\Entity\Product;
use App\Common\Application\Bus\CommandBus\CommandBusInterface;
use App\Common\Application\Bus\QueryBus\QueryBusInterface;
use App\Common\Application\Response\ResponseBuilderInterface;
use App\Common\Application\DTO\PaginationIdDTO;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/products')]
class ProductsController extends AbstractController
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
        description: 'Return paginated products',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Product::class, groups: ['default'])),
                ),
            ],
        ),
    )]
    #[OA\Parameter(
        name: 'offset',
        description: 'Set offset (ID) for pagination',
        schema: new OA\Schema(type: 'int'),
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Set limit for pagination',
        schema: new OA\Schema(type: 'int'),
    )]
    #[Route('/get-paginated', methods: [Request::METHOD_GET])]
    #[IsGranted(ProductsVoter::GET_PAGINATED)]
    public function getPaginated(#[ValueResolver('get_paginated_products')] PaginationIdDTO $dto): Response
    {
        if ($dto->hasErrors()) {
            return $this->json([
                'success' => false,
                'errors' => $dto->getErrors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $queryResult = $this->queryBus->handle(new GetPaginatedProductsQuery($dto->offset, $dto->limit));

        return $this->responseBuilder->buildResponse(
            $queryResult,
            'Page loaded successfully.',
            'Failed to getting paginated e.',
            $queryResult->data
        );        
    }

    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Create product',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(property: 'message', type: 'string'),
            ],
        ),
    )]
    #[OA\RequestBody(content: new Model(type: CreateProductDTO::class, groups: ['default']))]
    #[Route('/create', methods: [Request::METHOD_POST])]
    #[IsGranted(ProductsVoter::CREATE)]
    public function create(#[ValueResolver('create_product_dto')] CreateProductDTO $dto): Response
    {
        if ($dto->hasErrors()) {
            return $this->json([
                'success' => false,
                'errors' => $dto->getErrors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $commandResult = $this->commandBus->handle(new CreateProductCommand($dto));
 
        return $this->responseBuilder->buildResponse(
            $commandResult, 
            'Product created successfully.', 
            'Failed to created product'
        );
    }

    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Update product',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(property: 'message', type: 'string'),
            ],
        ),
    )]
    #[OA\RequestBody(content: new Model(type: UpdateProductDTO::class, groups: ['default']))]
    #[Route('/update/{id}', methods: [Request::METHOD_PUT])]
    #[IsGranted(ProductsVoter::UPDATE)]
    public function update(
        #[ValueResolver('update_product_dto')] UpdateProductDTO $dto,
        int $id,
    ): Response {
        if ($dto->hasErrors()) {
            return $this->json([
                'success' => false,
                'errors' => $dto->getErrors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $queryResult = $this->queryBus->handle(new FindProductByIdQuery($id));
      
        if ($queryResult->data !== null) {
            $product = $this->entityManager->getReference(Product::class, $queryResult->data['id']);
            $commandResult = $this->commandBus->handle(new UpdateProductCommand($product->getId(), $dto));
        }
        return $this->responseBuilder->buildResponse(
            $commandResult, 
            'Product updated successfully.', 
            'Failed to update product'
        );
    }

    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Show product',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(
                    property: 'data',
                    ref: new Model(type: Product::class, groups: ['default']),
                    type: 'object',
                ),
            ],
        ),
    )]
    #[Route('/show/{slug}', methods: [Request::METHOD_GET])]
    #[IsGranted(ProductsVoter::SHOW)]
    public function show(string $slug): Response
    {
        $queryResult = $this->queryBus->handle(new FindProductBySlugQuery($slug));

        return $this->responseBuilder->buildResponse(
            $queryResult, 
            'Product found successfully.', 
            'Failed to retrieve product.',
            $queryResult->data 
        );
    }

    #[OA\Response(
        response: Response::HTTP_ACCEPTED,
        description: 'Soft delete product',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(property: 'message', type: 'string'),
            ],
        ),
    )]
    #[Route('/delete/{productId}', methods: [Request::METHOD_DELETE])]
    #[IsGranted(ProductsVoter::DELETE)]
    public function delete(int $productId): Response
    {
        $queryResult = $this->queryBus->handle(new FindProductByIdQuery($productId));
        if ($queryResult->data !== null) {
            $product = $this->entityManager->getReference(Product::class, $queryResult->data['id']);
            $commandResult = $this->commandBus->handle(new DeleteProductCommand($product->getId()));
        }
        
        return $this->responseBuilder->buildResponse(
            $commandResult, 
            'Product deleted successfully.', 
            'Failed to delete product'
        );
    }
}
