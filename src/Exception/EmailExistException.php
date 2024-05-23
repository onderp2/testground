<?php

declare(strict_types=1);

namespace App\Exception;

class EmailExistException extends BaseUserEditException
{
    public function __construct(string $email = '', string $message = 'Email {} exist already')
    {
        parent::__construct(str_replace('{}', $email, $message));
    }
}
