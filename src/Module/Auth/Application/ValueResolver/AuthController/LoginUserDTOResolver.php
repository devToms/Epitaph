<?php

declare(strict_types=1);

namespace App\Module\Auth\UI\ValueResolver\AuthController;

use App\Module\Auth\Application\DTO\LoginUserDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsTargetedValueResolver('login_user_dto')]
class LoginUserDTOResolver implements ValueResolverInterface
{

    public function __construct(
        protected ValidatorInterface $validator,
    ) {
    }

    /**
     * @return iterable<LoginUserDTO>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
       
        if ($argument->getType() !== LoginUserDTO::class) {
            return [];
        }

        $data = $request->toArray();
        
        $dto = new LoginUserDTO(
            $data['email'] ?? '',     
            $data['password'] ?? '' 
        );
        
        yield $dto;
    }
}
