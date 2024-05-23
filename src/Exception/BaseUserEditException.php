<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class BaseUserEditException extends Exception
{
    public function __construct($message = 'Error occurred during editing', $code = 405, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
