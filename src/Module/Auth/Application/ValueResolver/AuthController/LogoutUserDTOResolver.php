<?php

declare(strict_types=1);

namespace App\Module\Auth\UI\ValueResolver\AuthController;

use App\Module\Auth\Application\DTO\LogoutUserDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsTargetedValueResolver('logout_user_dto')]
class LogoutUserDTOResolver implements ValueResolverInterface
{

    public function __construct(
        protected ValidatorInterface $validator,
    ) {
    }

    /**
     * @return iterable<LogoutUserDTO>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
       
        if ($argument->getType() !== LoginUserDTO::class) {
            return [];
        }

        $data = $request->toArray();
        
        $dto = new LogoutUserDTO(
            $data['path'] ?? '',     
        );

        yield $dto;
    }
}
