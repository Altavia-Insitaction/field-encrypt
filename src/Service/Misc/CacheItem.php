<?php

namespace Insitaction\FieldEncryptBundle\Service\Misc;

use InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Response;

class CacheItem
{
    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function get(string $cacheItemId): mixed
    {
        $this->testKeyIntegrity($cacheItemId);
        $cacheItem = $this->cache->getItem($cacheItemId);

        if (!$cacheItem->isHit()) {
            return null;
        }

        return $cacheItem->get();
    }

    public function remove(string $cacheItemId): void
    {
        $this->cache->deleteItem($cacheItemId);
    }

    public function cache(mixed $item, string $cacheItemId, int $duration = 3600): void
    {
        $this->testKeyIntegrity($cacheItemId);
        $cacheItem = $this->cache->getItem($cacheItemId);

        $cacheItem->set($item);
        $cacheItem->expiresAfter($duration);

        $this->cache->save($cacheItem);
    }

    private function testKeyIntegrity(string $key): void
    {
        if (64 < strlen($key)) {
            throw new InvalidArgumentException('Argument "$key" provided to "' . self::class . '::testKeyIntegrity()" must be a length of up to 64 characters.', Response::HTTP_BAD_REQUEST);
        }

        if ('' === $key) {
            throw new InvalidArgumentException('Argument "$key" provided to "' . self::class . '::testKeyIntegrity()" must be a string of at least one character.', Response::HTTP_BAD_REQUEST);
        }

        if (false === preg_match('/^[a-zA-Z_.0-9]+$/', $key)) {
            throw new InvalidArgumentException('Argument "$key" provided to "' . self::class . '::testKeyIntegrity()" is not a valid key (Allowed characters: A-Z, a-z, 0-9, _, and .).', Response::HTTP_BAD_REQUEST);
        }
    }
}
