<?php

declare(strict_types=1);

namespace App\Exception;

class UnauthorizedProfileChangeException extends BaseUserEditException
{
    public function __construct($message = 'Cannot change the type of organization', $code = 405, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
