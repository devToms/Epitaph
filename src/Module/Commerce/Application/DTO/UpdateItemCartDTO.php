<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\DTO;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;
use App\Common\Application\DTO\AbstractDTO;

class UpdateItemCartDTO extends AbstractDTO
{
    #[Assert\NotBlank(message: 'Product ID cannot be empty.')]
    #[OA\Property(type: 'string', description: 'ID of the product')]
    #[Groups(['default'])]
    public readonly int $productId;

    #[Assert\NotBlank(message: 'Quantity is required.')]
    #[Assert\Positive(message: 'Quantity must be a positive number.')]
    #[OA\Property(type: 'integer', description: 'Quantity of the product')]
    #[Groups(['default'])]
    public readonly int $quantity;

    #[Assert\Uuid(message: 'Invalid user ID format.')]
    #[OA\Property(type: 'string', format: 'uuid', description: 'Optional user ID')]
    #[Groups(['default'])]
    public readonly ?string $userId;

    public function __construct(
        int $productId,
        int $quantity,
        ?string $userId = null
    ) {
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->userId = $userId;
    }
}
