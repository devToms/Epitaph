<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\Command\CreateCart;

use App\Module\Commerce\Application\DTO\CreateCartDTO;
use App\Common\Application\Command\CommandInterface;

class CreateCartCommand implements CommandInterface
{
    public function __construct(
        public CreateCartDTO $dto
    ){}
}
