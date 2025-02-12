<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\DTO;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;
use App\Common\Application\DTO\AbstractDTO;

class AddItemToCartDTO extends AbstractDTO
{
    #[Assert\NotBlank(message: 'Cart UUID cannot be empty.')]
    #[Assert\Uuid(message: 'Invalid Cart UUID format.')]
    #[OA\Property(type: 'string', format: 'uuid', description: 'UUID of the cart')]
    #[Groups(['default'])]
    public readonly string $cartUuid;


    #[Assert\NotBlank(message: 'Product ID cannot be empty.')]
    #[OA\Property(type: 'string', description: 'ID of the product')]
    #[Groups(['default'])]
    public readonly int $productId;

 
    #[Assert\NotBlank(message: 'Quantity is required.')]
    #[Assert\Positive(message: 'Quantity must be a positive number.')]
    #[OA\Property(type: 'integer', description: 'Quantity of the product')]
    #[Groups(['default'])]
    public readonly int $quantity;
    
    
    public function __construct(
        string $cartUuid,
        int $productId,
        int $quantity,
    ) {
        $this->cartUuid = $cartUuid;
        $this->productId = $productId;
        $this->quantity = $quantity;
    }
}

