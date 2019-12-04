<?php

declare(strict_types=1);

namespace App\Integration\Some;

use App\Integration\Some\Exception\Exception as SomeException;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Log\LoggerInterface;

class Provider implements ProviderInterface
{
    /**
     * @var GuzzleClient
     */
    private $guzzleClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Передается уже настроенный клиент, где зашиты хост, юзер и пароль, тк, например, в зависимости от окружения они могут быть разные
     */
    public function __construct(GuzzleClient $guzzleClient, LoggerInterface $logger)
    {
        $this->guzzleClient = $guzzleClient;
        $this->logger = $logger;
    }

    public function get(array $request): array
    {
        try {
            $response = $this->guzzleClient->get('somepath', [
                'query' => $request,
            ]);

            //В зависимости от апи, может и другие коды можно считать валидными
            if ($response->getStatusCode() !== 200) {
                throw new Exception('Unexpected response code');
            }

            $data = json_decode($response->getBody(), true);

            /**
             * Указано, что  на сервере установлен php 7.1, но если бы был 7.3+, то исключение мог бы бросить сам json_decode
             * или же можно использовать symfony/polyfill-php73
             */
            $code = \json_last_error();
            if ($code !== \JSON_ERROR_NONE) {
                throw new Exception(json_last_error_msg(), $code);
            }
            // на случай если в ответе неверный формат, например строка "null" при json_decode превратится в null
            if (!is_array($data)) {
                throw new Exception('Unexpected response format');
            }

            return $data;
        } catch (Exception $e) {
            $this->logger->error('Error getting data from remote server', [
                'exception' => $e,
            ]);
            throw new SomeException($e);
        }
    }
}
