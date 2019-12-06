<?php

declare(strict_types=1);

namespace App\Integration\Some;

use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class ProviderDecoratorCachable implements ProviderInterface
{
    /**
     * "lesons" очень абстрактный префик, к тому же с ошибкой
     */
    private const CACHE_PREFIX = 'lessons_some';

    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * В контроллере было, что в зависимости от окружения применяется кеширование или нет
     * для этого лучше использовать некий класс NullCache по аналогии с NullLogger
     * Есть вариант не использовать декоратор вообще, но из-за этого можно пропустить ошибки внутри декоратора на этапе тестирования
     */
    public function __construct(ProviderInterface $provider, CacheItemPoolInterface $cache, LoggerInterface $logger)
    {
        $this->provider = $provider;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function get(array $request): array
    {
        $cachingIsAvailable = true;

        try {
            $cacheKey = $this->getCacheKey($request);
            $cacheItem = $this->cache->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
        } catch (CacheException $e) {
            /**
             * Если кеш не работает - продолжаем работу без него и логируем ошибку.
             * Если кол-во запросов ограничено - нельзя игнорировать
             */
            $this->logger->error('Error getting cache item', [
                'exception' => $e,
            ]);
            $cachingIsAvailable = false;
        }

        /**
         * @throws GuzzleException | Exception
         */
        $data = $this->provider->get($request);

        if ($cachingIsAvailable) {
            $cacheItem
                ->set($data)
                ->expiresAt(
                    (new DateTime())->modify('+1 day')
                );
            try {
                $this->cache->save($cacheItem);
            } catch (CacheException $e) {
                $this->logger->error('Error saving cache item', [
                    'exception' => $e,
                ]);
            }
        }

        return $data;
    }

    private function getCacheKey(array $request): string
    {
        /**
         * сортируем дабы ключ оставался неизменным при изменении порядка ключей массива
         * json_encode не все может сериализовать
         * В данном случае оба момента неактуальны
         * А вообще стоит явно указывать какой набор данных сериализовать, и это неизбежно при использовании DTO
         */
        ksort($request);
        return self::CACHE_PREFIX . md5(serialize($request));
    }
}
