<?php

declare(strict_types=1);

namespace App\Exception;

class UserTypeFieldMissing extends FieldMissingException
{
    public function __construct($message = 'User type field missing or incorrect')
    {
        parent::__construct($message, '');
    }
}
