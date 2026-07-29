<?php

declare(strict_types=1);

namespace App\Domains\TitanTrain\Domain\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class TitanTrainRuleViolation extends UnprocessableEntityHttpException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
