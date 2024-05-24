<?php

declare(strict_types=1);

namespace App\Service\EditorHandler;

use App\Dto\EditUserProfileDto;
use App\Entity\User;
use App\Exception\BaseUserEditException;
use App\Exception\FieldMissingException;
use App\Exception\FullNameFieldMissingException;
use App\Exception\PhoneFieldEmptyException;
use App\Exception\UnauthorizedProfileChangeException;
use App\Service\BankDataProcessor;


class ProfileEditorHandler implements EditorHandlerInterface
{
    public function __construct(private readonly BankDataProcessor $bankDataProcessor,)
    {

    }

    /**
     * @throws FieldMissingException
     * @throws FullNameFieldMissingException
     * @throws PhoneFieldEmptyException
     * @throws UnauthorizedProfileChangeException
     * @throws BaseUserEditException
     */
    public function edit(EditUserProfileDto $profileDto, User $user): void
    {
        $this->validateClientProfile($profileDto, $user);
        $this->adjustCmpNames($profileDto, $user);

        $this->bankDataProcessor->processBankData($profileDto, $user);
    }

    /**
     * @throws UnauthorizedProfileChangeException
     * @throws FullNameFieldMissingException|PhoneFieldEmptyException
     */
    private function validateClientProfile(EditUserProfileDto $profileDto, User $user): void
    {
        $this->preventClientProfileChange($profileDto, $user);
        $this->validateCompanyName($profileDto);
        $this->validatePhoneField($profileDto);
    }

    /**
     * @throws UnauthorizedProfileChangeException
     */
    private function preventClientProfileChange(EditUserProfileDto $profileDto, User $user): void
    {
        $isActiveUser = getActiveUser() === $user->getId();
        $clientProfileIdChanged = $profileDto->getClientProfileId() !== $user->getClientProfileId();

        if ($isActiveUser && $clientProfileIdChanged) {
            throw new UnauthorizedProfileChangeException();
        }
    }

    private function validateCompanyName(EditUserProfileDto $profileDto): void
    {
        if ($profileDto->getClientProfileId() !== 3 && empty($profileDto->getCmpFullName())) {
            throw new FullNameFieldMissingException();
        }
    }

    /**
     * @throws PhoneFieldEmptyException
     */
    private function validatePhoneField(EditUserProfileDto $profileDto): void
    {
        if ($profileDto->getPhone() === null) {
            throw new PhoneFieldEmptyException();
        }
    }

    private function adjustCmpNames(EditUserProfileDto $profileDto, User $user): void
    {
        // только для не юр лиц
        if ($profileDto->getClientProfileId() !== 1 && !$profileDto->getCmpShortName()) {
            $profileDto->setCmpShortName($profileDto->getCmpFullName());
        }

        // Полное наименование организации только для физлиц
        if ($profileDto->getClientProfileId() === 3 && empty($user->getCmpFullName()) && empty($profileDto->getCmpFullName())) {
            $profileDto->setCmpFullName($this->generateUserFullName($profileDto));
        }
    }

    private function generateUserFullName(EditUserProfileDto $profileDto): string
    {
        return trim(
            (empty($profileDto->getLastName()) ? '' : $profileDto->getLastName()) . ' ' .
            (empty($profileDto->getFirstName()) ? '' : $profileDto->getFirstName()) . ' ' .
            (empty($profileDto->getMiddleName()) ? '' : $profileDto->getMiddleName())
        );
    }
}
