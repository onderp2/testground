<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidEntityType extends FieldMissingException
{
    public function __construct($message = 'Invalid entity name provided')
    {
        parent::__construct($message, '');
    }
}
