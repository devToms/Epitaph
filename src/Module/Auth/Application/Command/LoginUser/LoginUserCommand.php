<?php

declare(strict_types=1);

namespace App\Module\Auth\Application\Command\LoginUser;

use App\Module\Auth\Application\DTO\loginUserDTO;
use App\Common\Application\Command\CommandInterface;

class LoginUserCommand implements CommandInterface
{
    public function __construct(
        public loginUserDTO $dto,
    ) {}
}
