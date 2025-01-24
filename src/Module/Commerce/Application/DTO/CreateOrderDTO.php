<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\DTO;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;
use App\Common\Application\DTO\AbstractDTO;

class CreateOrderDTO extends AbstractDTO
{

    #[Assert\NotBlank(message: 'Cart UUID cannot be empty.')]
    #[Assert\Uuid(message: 'Invalid Cart UUID format.')]
    #[OA\Property(type: 'string', format: 'uuid', description: 'UUID of the cart')]
    #[Groups(['default'])]
    public string $cartUuid;

    public function __construct(
        string  $cartUuid,
    ) {
        $this->cartUuid = $cartUuid;
    }
}
