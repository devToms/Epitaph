<?php

declare(strict_types=1);

namespace App\Module\Auth\Application\DTO;

use Symfony\Component\Validator\Constraints\NotBlank;

class LogoutUserDTO
{

    #[Sequentially([
        new NotBlank(['message' => 'Path cannot be blank.']),
    ])]

    #[Groups(['default'])]
    public readonly ?string $path;

    public function __construct(string $path = '/')
    {
        $this->path = $path;
    }
}


