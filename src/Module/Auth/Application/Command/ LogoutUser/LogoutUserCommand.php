<?php

declare(strict_types=1);

namespace App\Module\Auth\Application\Command\LogoutUser;

use App\Module\Auth\Application\DTO\LogoutUserDTO;
use App\Common\Application\Command\CommandInterface;

class LogoutUserCommand implements CommandInterface
{
    public function __construct(
        public LogoutUserDTO $dto
    ) {}
}


