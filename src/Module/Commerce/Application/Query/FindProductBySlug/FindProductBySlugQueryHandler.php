<?php

declare(strict_types=1);

namespace App\Module\Commerce\Application\Query\FindProductBySlug;

use App\Common\Application\Query\QueryHandlerInterface;
use App\Common\Application\BusResult\QueryResult;
use App\Module\Commerce\Domain\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use App\Common\Infrastructure\Service\CacheServiceInterface;
use App\Module\Commerce\Domain\Entity\Product;

#[AsMessageHandler]
readonly class FindProductBySlugQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected CacheServiceInterface $cacheService,
        protected LoggerInterface $logger,
    ) {}

    public function __invoke(FindProductBySlugQuery $query): QueryResult
    {
        try {
            $product = $this->productRepository->findBySlug($query->slug);
           dump(v);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
            return new QueryResult(
                success: false,
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
       
        return new QueryResult(
            success: true,
            statusCode: Response::HTTP_OK,
            data: json_decode($this->serializer->serialize($product, true))
        );
    }
}
