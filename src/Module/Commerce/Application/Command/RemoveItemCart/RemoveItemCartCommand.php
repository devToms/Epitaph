<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\Command\RemoveItemCart;

use App\Common\Application\Command\CommandInterface;

class RemoveItemCartCommand implements CommandInterface
{
    public function __construct(
        public string $cartUuid
    ){}
}
