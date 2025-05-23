<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use RuntimeException;

class HttpException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 500,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}