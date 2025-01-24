<?php

declare(strict_types=1);

namespace App\Module\Commerce\Domain\Entity;

use App\Module\Commerce\Domain\Enum\OrderStatus;
use App\Module\Commerce\Domain\Repository\OrderRepositoryInterface;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;
use App\Module\Commerce\Domain\Entity\OrderProduct;
use App\Module\Commerce\Domain\Entity\OrderStatusUpdate;

class Order
{
    private readonly string $id;
    private readonly Client $client;
    private Collection $ordersProducts;
    private Collection $ordersStatusUpdates;
    private ?DateTimeImmutable $completedAt;
    private readonly DateTimeImmutable $createdAt;

    public function __construct(Client $client)
    {
        $this->id = (string) Uuid::v1();
        $this->client = $client;
        $this->ordersProducts = new ArrayCollection();
        $this->ordersStatusUpdates = new ArrayCollection();
        $this->completedAt = null;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return Collection<int, OrderProduct>
     */
    public function getOrdersProducts(): Collection
    {
        return $this->ordersProducts;
    }

    /**
     * @return Collection<int, OrderStatusUpdate>
     */
    public function getOrdersStatusUpdates(): Collection
    {
        return $this->ordersStatusUpdates;
    }

    public function addProductToOrder(
        Product $product,
        int $productQuantity,
        float $productPricePerPiece
    ): self {
        $this->ordersProducts->add(
            new OrderProduct(
                order: $this,
                product: $product,
                productQuantity: $productQuantity,
                productPricePerPiece: $productPricePerPiece
            )
        );
        return $this;
    }

    public function updateOrderStatus(OrderStatus $orderStatusUpdate): self
    {
        $this->ordersStatusUpdates->add(
            new OrderStatusUpdate(
                order: $this,
                status: $orderStatusUpdate->value
            )
        );
        if ($orderStatusUpdate->value === OrderStatus::DELIVERED) {
            $this->setCompletedAt(new DateTimeImmutable());
        }
        return $this;
    }

    public function getCurrentStatus(): string
    {
        return $this->ordersStatusUpdates->last()->getStatus();
    }

    public function setCompletedAt(DateTimeImmutable $completedAt): self
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
