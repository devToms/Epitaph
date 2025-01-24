<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\Command\AddItemToCart;

use App\Module\Commerce\Application\DTO\AddItemToCartDTO;
use App\Common\Application\Command\CommandInterface;

class AddItemToCartCommand implements CommandInterface
{
    public function __construct(
        public AddItemToCartDTO $dto
    ) {}
}
