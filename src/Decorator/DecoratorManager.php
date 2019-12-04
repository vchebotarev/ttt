<?php
declare(strict_types=1);

namespace App\Decorator;

use Psr\Cache\CacheItemPoolInterface;

class DecoratorManager extends \App\Integration\DataProvider
{
    const CACE_SUFFIX = 'lesons';
    public $cache = null;
    public $logger;

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct($host, $user, $password, \Psr\Log\LoggerInterface $logger)
    {
        parent::__construct($host, $user, $password);
        $this->logger = $logger;
    }

    public function setCache(CacheItemPoolInterface $cache) {
        $this->cache = $cache;
    }

    public function getResponse(array $input)
    {
        try {
            $cache_key = self::CACE_SUFFIX .json_encode($input);
            $cacheItem = $this->cache->getItem($cache_key);
            if ($cacheItem->isHit()) return $cacheItem->get();

            $result = parent::get($input);

            $cacheItem
                ->set($result)
                ->expiresAt(
                    (new \DateTime())->modify('+1 day')
                );

            return $result;
        } catch (\Exception $e) {
            $this->logger->critical("Error");
        }

        return [];
    }
}