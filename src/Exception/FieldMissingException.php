<?php

declare(strict_types=1);

namespace App\Exception;

class FieldMissingException extends \Exception
{
    public function __construct($message = 'One of fields is empty', string $fieldName = 'One of fields')
    {
        $message = str_replace($message, 'One of fields', $fieldName);

        parent::__construct($message);
    }
}
