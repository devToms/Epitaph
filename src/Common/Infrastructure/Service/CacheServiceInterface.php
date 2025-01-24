<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Service;

use Psr\Log\LoggerInterface;
use App\Common\Domain\Cache\CacheProxyInterface;
use App\Common\Domain\Serializer\JsonSerializerInterface;

interface CacheServiceInterface
{
    public function getOrSetCache(string $cacheKey, callable $retrieveDataCallback, string $entityClass): mixed;
}
