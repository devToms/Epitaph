<?php

declare(strict_types=1);

namespace App\Module\Commerce\Domain\Entity;

use App\Module\Commerce\Domain\Entity\Order;
use App\Module\Commerce\Domain\Entity\Product;



class OrderProduct
{
    private ?int $id = null;
    private readonly Order $order;
    private readonly Product $product;
    private readonly int $productQuantity;
    private readonly float $productPricePerPiece;

    public function __construct(
        Order $order,
        Product $product,
        int $productQuantity,
        float $productPricePerPiece
    ) {
        $this->order = $order;
        $this->product = $product;
        $this->productQuantity = $productQuantity;
        $this->productPricePerPiece = $productPricePerPiece;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getProductQuantity(): int
    {
        return $this->productQuantity;
    }

    public function getProductPricePerPiece(): float
    {
        return $this->productPricePerPiece;
    }
}
