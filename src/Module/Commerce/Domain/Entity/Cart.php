<?php

declare(strict_types=1);

namespace App\Module\Commerce\Domain\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;


class Cart
{
    private readonly string $id;
    private readonly Client $client;
    private Collection $items;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(Client $client)
    {

        $this->id = (string) Uuid::v4();
        $this->client = $client;
        $this->items = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->totalPrice = 0.0;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClient(): Client
    {
        return $this->client;
    }


    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Product $product, int $quantity): self 
    {
        foreach ($this->items as $item) {
            if ($item->getProduct() === $product) {
                $item->increaseQuantity($quantity);
                return $this;
            }
        }
        
        $this->items->add(new CartItem( $this,  $product,  $quantity));
        $this->updateItemPrice($product);  
        $this->refreshUpdatedAt();
        return $this;
    }
    
    public function updateItem(Product $product, int $quantity): self
    {    
        foreach ($this->items as $item) {
            if ($item->getProduct() === $product) {
                $item->setCart($this);
                $item->setQuantity($quantity);  // Ustaw nową ilość
                $item->setPrice($item->getSubtotal()); 
                $this->refreshUpdatedAt();
                return $this;
            }
        }

        throw new \InvalidArgumentException('Item not found in cart.');
    }

    public function removeItem(Product $product): self
    {
        foreach ($this->items as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $this->items->removeElement($item);
                $this->refreshUpdatedAt(); 
                break;
            }
        }

        return $this;
    }

    public function updateItemPrice(Product $product): void
    {
        foreach ($this->getItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $item->setPrice($item->getSubtotal());
            }
        }
    }

    public function clear(): self
    {
        $this->items->clear();
        $this->refreshUpdatedAt(); 
        return $this;
    }

    public function getTotalPrice(): float
    {
        $this->totalPrice = array_reduce(
            $this->items->toArray(),
            fn(float $total, CartItem $item) => $total + $item->getSubtotal(),
            0.0
        );

        return $this->totalPrice;
    }

    private function refreshUpdatedAt(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
