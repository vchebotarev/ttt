<?php

declare(strict_types=1);

namespace App\Integration\Some\Exception;

use Exception as BaseException;
use Throwable;

class Exception extends BaseException implements ExceptionInterface
{
    /**
     * Данные по запросу, чтобы было понятно с какими именно параметрами произошла ошибка
     * @var array
     */
    private $request;

    public function __construct(Throwable $previous, array $request)
    {
        $this->request = $request;
        parent::__construct('Some integration error', 0, $previous);
    }
}
