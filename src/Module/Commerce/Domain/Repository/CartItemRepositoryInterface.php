<?php

declare(strict_types=1);

namespace App\Module\Commerce\Domain\Repository;

use App\Module\Commerce\Domain\Entity\CartItem;
use App\Module\Commerce\Domain\Entity\Cart;

interface CartItemRepositoryInterface
{
    public function save(CartItem $cartItem, bool $flush = false): void;
    public function findByCart(Cart $cart): array;
}

