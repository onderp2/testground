<?php

declare(strict_types=1);

namespace App\Service\EditorHandler;

use App\Dto\EditUserProfileDto;
use App\Entity\User;

interface EditorHandlerInterface
{
    public function edit(EditUserProfileDto $profileDto, User $user): void;
}
