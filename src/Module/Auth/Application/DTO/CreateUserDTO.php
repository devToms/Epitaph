<?php

declare(strict_types=1);

namespace App\Module\Auth\Application\DTO;

use App\Module\Auth\Domain\Entity\User;
use App\Common\Application\Constraint\UniqueFieldInEntity;
use App\Common\Application\DTO\AbstractDTO;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Sequentially;

class CreateUserDTO extends AbstractDTO
{
    #[Sequentially([
        new NotBlank(['message' => 'Email is required.']),
        new Email(['message' => 'Please provide a valid email.']),
        new Length([
            'min' => 2,
            'minMessage' => 'Email must be at least 2 characters long.',
            'max' => 100,
            'maxMessage' => 'Email cannot exceed 100 characters.',
        ]),
        new UniqueFieldInEntity(field: 'email', entityClassName: User::class),
    ])]
    #[Groups(['default'])]
    public readonly ?string $email;

    #[Sequentially([
        new NotBlank(['message' => 'Name is required.']),
        new Length([
            'min' => 2,
            'minMessage' => 'Name must be at least 2 characters long.',
            'max' => 100,
            'maxMessage' => 'Name cannot exceed 100 characters.',
        ]),
    ])]
    #[Groups(['default'])]
    public readonly ?string $name;

    #[Sequentially([
        new NotBlank(['message' => 'Password is required.']),
        new Length([
            'min' => 2,
            'minMessage' => 'Password must be at least 2 characters long.',
            'max' => 100,
            'maxMessage' => 'Password cannot exceed 100 characters.',
        ]),
    ])]
    #[Groups(['default'])]
    public readonly ?string $password;

    #[Sequentially([
        new NotBlank(['message' => 'Surname is required.']),
        new Length([
            'min' => 2,
            'minMessage' => 'Surname must be at least 2 characters long.',
            'max' => 100,
            'maxMessage' => 'Surname cannot exceed 100 characters.',
        ]),
    ])]
    #[Groups(['default'])]
    public readonly ?string $surname;

    public function __construct(
        ?string $email,
        ?string $password,
        ?string $name,
        ?string $surname,
    ) {
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->surname = $surname;
    }
}
