<?php

declare(strict_types=1);

namespace App\Module\Commerce\Domain\Repository;

use App\Module\Commerce\Domain\Entity\Cart;

interface CartRepositoryInterface
{
    public function save(Cart $cart, bool $flush = false): void;

    public function findById(string $id): ?Cart;
}
