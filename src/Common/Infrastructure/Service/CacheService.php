<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Service;

use Psr\Log\LoggerInterface;
use App\Common\Domain\Cache\CacheProxyInterface;
use App\Common\Domain\Serializer\JsonSerializerInterface;
use App\Common\Infrastructure\Service;

class CacheService implements CacheServiceInterface
{
    public function __construct(
        protected CacheProxyInterface $cache,
        protected JsonSerializerInterface $serializer,
        protected LoggerInterface $logger
    ) {}

    public function getOrSetCache(string $cacheKey, callable $retrieveDataCallback, string $entityClass): mixed
    {
        try {
            if ($this->cache->exists($cacheKey)) {
                $cachedData = $this->cache->get($cacheKey);
                return $cachedData ? $this->serializer->deserialize($cachedData, $entityClass) : null;
            }

            $data = $retrieveDataCallback();
            if ($data !== null) {
                $this->cache->set($cacheKey, $this->serializer->serialize($data));
            }
            return $data;
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            return null;
        }
    }
}
