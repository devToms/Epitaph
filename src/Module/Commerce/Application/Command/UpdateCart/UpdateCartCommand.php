<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\Command\UpdateCart;

use App\Module\Commerce\Application\DTO\UpdateItemCartDTO;
use App\Module\Commerce\Domain\Entity\Cart;
use App\Common\Application\Command\CommandInterface;

class UpdateCartCommand implements CommandInterface
{
    public function __construct(
        public Cart $cart,
        public UpdateItemCartDTO $dto
    ) {}
}
