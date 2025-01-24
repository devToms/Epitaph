<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\ValueResolver\CartController;

use App\Module\Commerce\Application\DTO\AddItemToCartDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsTargetedValueResolver('add_item_to_cart_dto')]
readonly class AddItemToCartValueResolver implements ValueResolverInterface
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @return iterable<AddItemToCartDTO>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $data = $request->toArray();

        $dto = new AddItemToCartDTO(
            cartUuid: (string) ($data['cartUuid'] ?? ''), 
            productId: (int) ($data['productId'] ?? ''),
            quantity: (int) ($data['quantity'] ?? 1),
            userId: $data['userId'] ?? null,
        );

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors);
        }

        yield $dto;
    }
}
