<?php

declare(strict_types=1);

namespace App\Service\EditorHandler;

use App\Dto\EditUserProfileDto;
use App\Entity\User;

class EmailEditorHandler implements EditorHandlerInterface
{
    public function edit(EditUserProfileDto $profileDto, User $user): void
    {
        $this->changeUserEmail($user, $profileDto);
    }

    private function changeUserEmail(User $user, EditUserProfileDto $profileDto): void
    {
        $existingEmail = $user->getEmail();
        $newEmail = $profileDto->getEmail();
        $emailExist = $this->isEmailExits($newEmail?? '');

        if ($newEmail && ($newEmail !== $existingEmail) && !$emailExist) {
            return;
        }

        if ($emailExist) {
            throw new EmailExistException($newEmail);
        }

        $profileDto->setUserName($newEmail);
    }

    private function isEmailExits(string $email): bool
    {
        return null !== $this->userRepository->findOneBy(['email' => $email]);
    }
}
