<?php

declare(strict_types=1);

namespace App\Controller;

use InvalidArgumentException;
use SimpleXMLElement;

/**
 * По хорошему надо возвращать объект ответа
 * Везде получается код ответа 200, но возможно стоит использовать другие при
 * - ненайденных данных;
 * - непрошедней валидации;
 * - ошибке формирования ответа.
 * В случае ошибки необходимо передавать сообщение в требуемом формате (json или xml)
 */
class ResponseFactory
{
    public function createSuccess(array $data, string $format): string
    {
        //вижу намек на yoga style `if ('xml' === $format)` { но использовать не буду, тк ухудшает читаемость кода
        if ($format === 'xml') {
            // заниматься маппингов должен отдельный сервис
            $xml = new SimpleXMLElement('<root/>');
            array_walk_recursive($data, array ($xml, 'addChild'));
            $responseString = $xml->asXML();
        } elseif ($format === 'json') {
            $responseString = json_encode($data);
        } else {
            throw new InvalidArgumentException('Invalid response format "'.$format.'"');
        }

        return $responseString;
    }

    public function createFailure(): string
    {
        return 'error';
    }

    public function createFailureValidation(): string
    {
        return 'validation error';
    }

    public function createFailureNotFound(): string
    {
        return 'not found error';
    }
}
