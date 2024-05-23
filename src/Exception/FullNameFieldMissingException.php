<?php

declare(strict_types=1);

namespace App\Exception;

class FullNameFieldMissingException extends BaseUserEditException
{
    public function __construct($message = 'Full name field is empty')
    {
        parent::__construct($message);
    }
}
