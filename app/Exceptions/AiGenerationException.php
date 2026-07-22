<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class AiGenerationException extends RuntimeException
{
    public function __construct(
        string $message = 'AI generation is temporarily unavailable. Please try again later.',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
