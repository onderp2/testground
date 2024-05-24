<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\EditUserProfileDto;
use App\Entity\User;
use App\Exception\BaseUserEditException;
use App\Exception\FieldMissingException;
use App\Exception\FullNameFieldMissingException;
use App\Exception\PhoneFieldEmptyException;
use App\Exception\UnauthorizedProfileChangeException;
use App\Service\EditorHandler\EmailEditorHandler;
use App\Service\EditorHandler\ProfileEditorHandler;
use App\Service\EditorHandler\UserRolesEditorHandler;
use App\Service\Manager\UserManager;
use Psr\Log\LoggerInterface;
use Throwable;

class EditUserServiceImplementation
{
    public function __construct(
        private readonly ProfileEditorHandler $profileEditorHandler,
        private readonly EmailEditorHandler $emailEditorHandler,
        private readonly UserRolesEditorHandler $userRolesEditorHandler,
        private readonly UserManager $userManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws UnauthorizedProfileChangeException
     * @throws BaseUserEditException
     */
    public function editUserProfile(EditUserProfileDto $profileDto, User $user): void
    {
        try {
            $this->handleProfileEdit($profileDto, $user);
            $this->handleEmailEdit($profileDto, $user);
            $this->handleUserRolesEdit($profileDto, $user);

            $this->userManager->update($profileDto, $user, false, []);
        } catch (\Exception $e) {
            $this->logger->error('Error editing user profile', ['exception' => $e]);
            throw $e;
        }
    }

    private function handleProfileEdit(EditUserProfileDto $profileDto, User $user): void
    {
        if ($this->shouldEditProfile($profileDto, $user)) {
            $this->profileEditorHandler->edit($profileDto, $user);
        }
    }

    private function handleEmailEdit(EditUserProfileDto $profileDto, User $user): void
    {
        if ($profileDto->getEmail()) {
            $this->emailEditorHandler->edit($profileDto, $user);
        }
    }

    private function handleUserRolesEdit(EditUserProfileDto $profileDto, User $user): void
    {
        $this->userRolesEditorHandler->edit($profileDto, $user);
    }

    private function shouldEditProfile(EditUserProfileDto $profileDto, User $user): bool
    {
        return null !== $profileDto->getClientProfileId() && $user->getUserType() === Model_User::TYPE_USER;
    }
}
