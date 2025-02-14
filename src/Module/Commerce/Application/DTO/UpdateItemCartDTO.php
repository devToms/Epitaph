<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\DTO;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;
use App\Common\Application\DTO\AbstractDTO;

class UpdateItemCartDTO extends AbstractDTO
{
    #[Assert\NotBlank(message: 'Quantity is required.')]
    #[Assert\Positive(message: 'Quantity must be a positive number.')]
    #[OA\Property(type: 'integer', description: 'Quantity of the product')]
    #[Groups(['default'])]
    public readonly int $quantity;

    public function __construct(
        int $quantity
    ) {
        $this->quantity = $quantity;
    }
}
