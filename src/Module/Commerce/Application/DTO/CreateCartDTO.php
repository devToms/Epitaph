<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\DTO;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;
use App\Common\Application\DTO\AbstractDTO;

class CreateCartDTO extends AbstractDTO
{

    #[Assert\Uuid(message: 'Invalid user ID format.')]
    #[OA\Property(type: 'string', format: 'uuid', description: 'Optional user ID')]
    #[Groups(['default'])]
    public readonly ?string $clientUuid;

    public function __construct(
        string $clientUuid,
    ) {
        $this->clientUuid = $clientUuid;
    }
}

