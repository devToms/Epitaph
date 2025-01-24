<?php

declare(strict_types=1);

namespace App\Module\Commerce\Domain\Entity;

use App\Module\Commerce\Domain\Enum\OrderStatus;
use DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

class OrderStatusUpdate
{
    private string $id = '';
    private readonly Order $order;
    private readonly string $status;
    private readonly DateTimeImmutable $createdAt;

    public function __construct(
        Order $order,
        string $status
    ) {
        $this->id = (string) Uuid::v4(); 
        $this->order = $order;
        $this->status = $status;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    // Dodatkowe metody, jeśli potrzebujesz, np. do ustawiania danych
    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
