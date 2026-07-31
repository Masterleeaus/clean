<?php

namespace App\TitanOS\Execution\Exceptions;

use Exception;

class ExecutionException extends Exception
{
    protected $context = [];

    public function __construct(string $message = '', array $context = [], int $code = 0, ?Exception $previous = null)
    {
        $this->context = $context;
        parent::__construct($message, $code, $previous);
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
