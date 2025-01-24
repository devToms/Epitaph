<?php

namespace App\Module\Commerce\Domain\Entity;

use App\Module\Commerce\Domain\Entity\Cart;
use App\Module\Commerce\Domain\Entity\Product;
use App\Repository\CartItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;


class CartItem
{
    private readonly string $id;

    private ?Cart $cart = null;

    private ?Product $product = null;

    private ?int $quantity = null;

    private ?float $price = null;

    public function __construct(Cart $cart, Product $product, int $quantity)
    {
        $this->id = (string) Uuid::v4();
        $this->cart = $cart;
        $this->product = $product;
        $this->quantity = $quantity;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): static
    {
        $this->cart = $cart;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function increaseQuantity(int $amount): self
    {
        $this->quantity += $amount;
        return $this;
    }

    public function decreasePrice(): self
    {
        $this->quantity = max(0, $this->quantity - $this->price );
        return $this;
    }


    public function getSubtotal(): float
    {
        return $this->getQuantity() * $this->product->getPrice();
    }
}

