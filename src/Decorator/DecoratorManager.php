<?php

namespace src\App\Decorator;

use DateTime;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use src\App\Integration\DataProvider;

final class DecoratorManager extends DataProvider
{
    public $cache = null;
    public $logger;

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param LoggerInterface $logger
     */
    public function __construct($host, $user, $password, LoggerInterface $logger)
    {
        parent::__construct($host, $user, $password);
        $this->logger = $logger;
    }

    public function setCache(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse(array $input)
    {
        try {
            $cacheKey = $this->getCacheKey($input);
            $cacheItem = $this->cache->getItem($cacheKey);
            if ($cacheItem->isHit()) return $cacheItem->get();

            $result = parent::get($input);

            $cacheItem
                ->set($result)
                ->expiresAt(
                    (new DateTime())->modify('+1 day')
                );

            return $result;
        } catch (\Exception $e) {
            $this->logger->critical('Error');
        }

        return [];
    }

    public function getCacheKey(array $input)
    {
        return json_encode($input);
    }
}