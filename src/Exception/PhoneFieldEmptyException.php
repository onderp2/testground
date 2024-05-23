<?php

declare(strict_types=1);

namespace App\Exception;

class PhoneFieldEmptyException extends BaseUserEditException
{
    public function __construct($message = 'Phone field is empty')
    {
        parent::__construct($message);
    }
}
