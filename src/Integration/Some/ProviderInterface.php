<?php

declare(strict_types=1);

namespace App\Integration\Some;

use App\Integration\Some\Exception\ExceptionInterface;

interface ProviderInterface
{
    /**
     * @throws ExceptionInterface
     */
    public function get(array $request): array;
}
