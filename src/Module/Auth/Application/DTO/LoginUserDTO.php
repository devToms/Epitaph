<?php

declare(strict_types=1);

namespace App\Module\Auth\Application\DTO;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Serializer\Annotation\Groups;

class LoginUserDTO
{
    #[Sequentially([
        new NotBlank(['message' => 'Email cannot be blank.']),
        new Email(['message' => 'Email must be valid.']),
        new Length([
            'min' => 2,
            'minMessage' => 'Email should be at least 2 characters long.',
            'max' => 100,
            'maxMessage' => 'Email can be up to 100 characters long.',
        ]),
    ])]
    #[Groups(['default'])]
    public readonly ?string $email;

    #[Sequentially([
        new NotBlank(['message' => 'Password cannot be blank.']),
        new Length([
            'min' => 6,
            'minMessage' => 'Password should be at least 6 characters long.',
            'max' => 100,
            'maxMessage' => 'Password can be up to 100 characters long.',
        ]),
    ])]
    #[Groups(['default'])]
    public readonly ?string $password;

    public function __construct(?string $email, ?string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
}