<?php

declare(strict_types=1);

namespace App\Integration\Some\Exception;

use Exception as BaseException;
use Throwable;

class Exception extends BaseException implements ExceptionInterface
{
    public function __construct(Throwable $previous)
    {
        parent::__construct('Some integration error', 0, $previous);
    }
}
