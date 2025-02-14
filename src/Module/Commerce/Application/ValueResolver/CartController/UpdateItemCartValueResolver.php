<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\ValueResolver\CartController;

use App\Module\Commerce\Application\DTO\UpdateItemCartDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsTargetedValueResolver('update_item_cart_dto')]
readonly class UpdateItemCartValueResolver implements ValueResolverInterface
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }
   
    /**
     * @return iterable<UpdateItemCartDTO>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $data = $request->toArray();

        $dto = new UpdateItemCartDTO(
            quantity: (int) ($data['quantity'] ?? 1)
        );

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors);
        }

        yield $dto;
    }
}
