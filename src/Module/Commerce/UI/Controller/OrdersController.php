<?php

declare(strict_types=1);

namespace App\Module\Commerce\UI\Controller;

use App\Module\Commerce\Application\Command\ChangeOrderStatus\ChangeOrderStatusCommand;
use App\Module\Commerce\Application\Command\CreateOrder\CreateOrderCommand;
use App\Module\Commerce\Application\DTO\ChangeOrderStatusDTO;
use App\Module\Commerce\Application\DTO\CreateOrderDTO;
use App\Module\Commerce\Application\Query\FindOrderByUuid\FindOrderByUuidQuery;
use App\Module\Commerce\Application\Query\GetPaginatedOrders\GetPaginatedOrdersQuery;
use App\Module\Commerce\Application\Voter\OrdersVoter;
use App\Module\Commerce\Domain\Entity\Order;
use App\Common\Application\Bus\CommandBus\CommandBusInterface;
use App\Common\Application\Bus\QueryBus\QueryBusInterface;
use App\Common\Application\Response\ResponseBuilderInterface;
use App\Common\Application\DTO\PaginationUuidDTO;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/orders')]
class OrdersController extends AbstractController
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
        description: 'Return paginated orders',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Order::class, groups: ['default'])),
                ),
            ],
        ),
    )]
    #[OA\Parameter(
        name: 'cursor',
        description: 'Set cursor (UUID) for pagination',
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Set limit for pagination',
        schema: new OA\Schema(type: 'int'),
    )]
    #[Route('/get-paginated', methods: [Request::METHOD_GET])]
    #[IsGranted(OrdersVoter::GET_PAGINATED)]
    public function getPaginated(#[ValueResolver('get_paginated_orders')] PaginationUuidDTO $dto): Response
    {
        if ($dto->hasErrors()) {
            return $this->json([
                'success' => false,
                'errors' => $dto->getErrors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $queryResult = $this->queryBus->handle(new GetPaginatedOrdersQuery($dto->cursor, $dto->limit));

        return $this->responseBuilder->buildResponse(
            $queryResult,
            'Page loaded successfully.',
            'Failed to getting paginated.',
            $queryResult->data
        );     
    }

    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Show order',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(
                    property: 'data',
                    ref: new Model(type: Order::class, groups: ['default']),
                    type: 'object',
                ),
            ],
        ),
    )]
    #[Route('/show/{uuid}', methods: [Request::METHOD_GET])]
    public function show(string $uuid): Response
    {
        $queryResult = $this->queryBus->handle(new FindOrderByUuidQuery($uuid));

        $order = $this->entityManager->getReference(Order::class, $queryResult->data['id']);
        if (!$this->isGranted(OrdersVoter::SHOW, $order)) {
            throw $this->createAccessDeniedException();
        }

        return $this->responseBuilder->buildResponse(
            $queryResult, 
            'Order found successfully.', 
            'Failed to retrieve ordert.',
            $queryResult->data 
        );
    }

    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Create order',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'bool'),
                new OA\Property(property: 'message', type: 'string'),
            ],
        ),
    )]
    #[OA\RequestBody(content: new Model(type: CreateOrderDTO::class, groups: ['default']))]
    #[Route('/new', methods: [Request::METHOD_POST])]
    #[IsGranted(OrdersVoter::CREATE)]
    public function new(#[ValueResolver('create_order_dto')] CreateOrderDTO $dto): Response
    {
        if ($dto->hasErrors()) {
            return $this->json([
                'success' => false,
                'errors' => $dto->getErrors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $commandResult = $this->commandBus->handle(new CreateOrderCommand($dto));
     
        if (!$commandResult->success) {
            throw new \RuntimeException('Order creation failed');
        }

        return $this->responseBuilder->buildResponse(
            $commandResult, 
            'Order created successfully.', 
            'Failed to created order'
        );
    }

    #[Route('/change-status/{uuid}', methods: [Request::METHOD_POST])]
    #[IsGranted(OrdersVoter::UPDATE_STATUS)]
    public function changeStatus(
        #[ValueResolver('change_order_status_dto')] ChangeOrderStatusDTO $dto,
        string $uuid,
    ): Response {
        if ($dto->hasErrors()) {
            return $this->json([
                'success' => false,
                'errors' => $dto->getErrors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $commandResult = $this->commandBus->handle(new ChangeOrderStatusCommand($dto, $uuid));
     
        return $this->responseBuilder->buildResponse(
            $commandResult, 
            'Order updated successfully.', 
            'Failed to update order'
        );
    }
}
