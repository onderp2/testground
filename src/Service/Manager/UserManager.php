<?php

declare(strict_types=1);

namespace App\Service\Manager;

use App\Dto\EditUserProfileDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserManager
{
    private function __construct(EntityManagerInterface $entityManager)
    {

    }

    public function update(EditUserProfileDto $profileDto, User $user, bool $something, array $array): void
    {
        // todo -> implement logic for updating
    }
}
