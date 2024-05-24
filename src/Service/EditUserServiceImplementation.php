<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\EditUserProfileDto;
use App\Entity\User;
use App\Exception\FieldMissingException;
use App\Exception\FullNameFieldMissingException;
use App\Exception\PhoneFieldEmptyException;
use App\Exception\UnauthorizedProfileChangeException;
use App\Service\EditorHandler\EmailEditorHandler;
use App\Service\EditorHandler\ProfileEditorHandler;
use App\Service\EditorHandler\UserRolesEditorHandler;

class EditUserServiceImplementation
{
    public function __construct(
        private readonly ProfileEditorHandler $profileEditorHandler,
        private readonly EmailEditorHandler $emailEditorHandler,
        private readonly UserRolesEditorHandler $userRolesEditorHandler,
    ) {
    }

    /**
     * @throws PhoneFieldEmptyException
     * @throws FieldMissingException
     * @throws FullNameFieldMissingException
     * @throws UnauthorizedProfileChangeException
     */
    public function editUserProfile(EditUserProfileDto $profileDto, User $user): void
    {
        if ($this->isEditProfile($profileDto, $user)) {
            $this->profileEditorHandler->edit($profileDto, $user);
        }

        if ($profileDto->getEmail()) {
            $this->emailEditorHandler->edit($profileDto, $user);
        }

        $this->userRolesEditorHandler->edit($profileDto, $user);

        $this->userManager->update($profileDto, $user, false, []);
    }


    private function isEditProfile(EditUserProfileDto $profileDto, User $user): bool
    {
        return null !== $profileDto->getClientProfileId() && $user->getUserType() === Model_User::TYPE_USER;
    }
}
