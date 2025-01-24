<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\Query\FindCartById;

use App\Common\Application\Query\QueryInterface;

readonly class FindCartByIdQuery implements QueryInterface
{
    public function __construct(
        public string $id,
    ) {
    }
}
