<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\DTO;

use App\Common\Application\DTO\AbstractDTO;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Sequentially;

class UpdateProductDTO extends AbstractDTO
{
    #[Sequentially([
        new NotBlank(['message' => 'Product name is required.']),
        new Length([
            'min' => 2,
            'minMessage' => 'The product name must be at least 2 characters long.',
            'max' => 100,
            'maxMessage' => 'The product name can be a maximum of 100 characters long.',
        ]),
    ])]
    #[Groups(['default'])]
    public readonly ?string $name;

    #[Sequentially([
        new NotBlank(['message' => 'Product price cannot be empty.']),
        new Positive([
            'message' => 'The price must be a valid positive number.',
        ]),
    ])]
    #[Groups(['default'])]
    public readonly ?float $price;

    public function __construct(
        ?string $name,
        ?float $price,
    ) {
        $this->name = $name;
        $this->price = $price;
    }
}

