<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\Command\RemoveCart;

use App\Module\Commerce\Domain\Entity\Cart;
use App\Common\Application\Command\CommandInterface;

class RemoveCartCommand implements CommandInterface
{
    public function __construct(
        public $cartId
    ) {}
}
