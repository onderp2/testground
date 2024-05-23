<?php

declare(strict_types=1);

namespace App\Service\EditorHandler;

use App\Dto\EditUserProfileDto;
use App\Entity\User;
use App\Exception\UserTypeFieldMissing;

class UserRolesEditorHandler implements EditorHandlerInterface
{
    public function edit(EditUserProfileDto $profileDto, User $user): void
    {
        if ($profileDto->getRole()) {
            $this->validateUserRole($profileDto);
            $user->setUserRoles([Model_User::USER_ROLE_AUTHORIZED, intval($profileDto->getRole())]);

            $this->clearUnnecessaryData($profileDto);
        } else {
            $profileDto->setUserType(null);
        }
    }

    private function clearUnnecessaryData(EditUserProfileDto $profileDto): void
    {
        $userType = $profileDto->getUserType();
        $isChanged = false;

        if ($userType === Model_User::TYPE_PARTNER || Model_User::TYPE_CALLSPEC === $userType) {
            $profileDto->setPointId(null);

            $isChanged = true;
        }

        if ($userType === Model_User::TYPE_OPERATOR || Model_User::TYPE_CALLSPEC) {
            $profileDto->setPartnerId(null);

            $isChanged = true;
        }

        if (false === $isChanged) {
            throw new UserTypeFieldMissing();
        }
    }

    private function validateUserRole(EditUserProfileDto $userProfileDto): void
    {
        if (null === $userProfileDto->getUserType()) {
            throw new UserTypeFieldMissing();
        }

        self::checkTypeRoleRequires($userProfileDto);
    }
}
