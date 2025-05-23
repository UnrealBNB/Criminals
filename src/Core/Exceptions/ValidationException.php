<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use RuntimeException;

class ValidationException extends RuntimeException
{
    public function __construct(
        private readonly array $errors,
        string $message = 'The given data was invalid.',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}