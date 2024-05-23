<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\AddressDataDto;
use App\Dto\BankAccountDto;
use App\Dto\EditUserProfileDto;
use App\Dto\ProfileRequirementsDto;
use App\Entity\User;
use App\Exception\EmailExistException;
use App\Exception\FieldMissingException;
use App\Exception\FullNameFieldMissingException;
use App\Exception\PhoneFieldEmptyException;
use App\Exception\UnauthorizedProfileChangeException;
use App\Exception\UserTypeFieldMissing;

class EditUserServiceImplementation
{
    public function __construct(private readonly ProfileEditorHandler $profileEditorHandler,)
    {

    }

    public function editUserProfile(EditUserProfileDto $profileDto, User $user): void
    {
        if ($this->isEditProfile($profileDto, $user)) {
            $this->profileEditorHandler->edit($profileDto, $user);
        }

        if ($profileDto->getEmail()) {
            $this->changeUserEmail($user->getEmail(), $profileDto);
        }

        if ($profileDto->getRole()) {
            $this->validateUserRole($profileDto);
            $user->setUserRoles([Model_User::USER_ROLE_AUTHORIZED, intval($profileDto->getRole())]);

            $this->clearUnnecessaryData($profileDto);
        } else {
            $profileDto->setUserType(null);
        }

        $this->userManager->update($profileDto, $user, false, []);
    }

    private function clearUnnecessaryData(EditUserProfileDto $profileDto)
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

    private function isPhysicalAndNoData(EditUserProfileDto $profileDto): bool
    {
        return $profileDto->getClientProfileId() === 3 && $profileDto->getBankAccount() === null;
    }

    private function validateBankAccountData(BankAccountDto $bankAccountDto): void
    {
        $fieldMissing = empty($bankAccountDto->getAccount())
            || empty($bankAccountDto->getBank())
            || empty($bankAccountDto->getBankAddr())
            || empty($bankAccountDto->getBik())
        ;

        if ($fieldMissing) {
            throw new FieldMissingException();
        }
    }

    private function updateBankAccountData(BankAccountDto $bankAccountDto, int $ownerId): void
    {
        $bankConfig = $this->getBankConfigArray($bankAccountDto, $ownerId);

        if ($bankAccountDto->getId()) {
            $this->updateBankData($bankAccountDto, $bankConfig);
        } else {
            $this->createAccountBank($bankConfig, $ownerId);
        }
    }

    private function updateBankData(BankAccountDto $bankAccountDto, array $bankConfig): void
    {
        Model_BankAccount::load($bankAccountDto->getId())?->update($bankConfig) ?? throw new FieldMissingException();
    }

    private function createAccountBank(array $bankConfig, int $ownerId): void
    {
        Model_BankAccount::create($bankConfig, $ownerId, Model_BankAccount::OWNER_TYPE_USER);
    }

    private function getBankConfigArray(BankAccountDto $bankAccountDto, int $ownerId): array
    {
        $bankConfig = [
            'bank_accounts' => [
                $bankAccountDto->getBank(),
                $bankAccountDto->getAccount(),
                $bankAccountDto->getBankAddr(),
                $bankAccountDto->getBik(),
            ],
            'actual' => true,
            'owner_type' => Model_BankAccount::OWNER_TYPE_USER,
            'owner_id' => $ownerId,
        ];

        return $bankConfig;
    }


    private function isEditProfile(EditUserProfileDto $profileDto, User $user): bool
    {
        return null !== $profileDto->getClientProfileId() && $user->getUserType() === Model_User::TYPE_USER;
    }

    private function validateCompanyName(EditUserProfileDto $profileDto): void
    {
        if ($profileDto->getClientProfileId() !== 3 && empty($profileDto->getCmpFullName())) {
            throw new FullNameFieldMissingException();
        }
    }



    private function generateUserFullName(EditUserProfileDto $profileDto): string
    {
        $lastName = $profileDto->getLastName() ? '' : $profileDto->getLastName() . ' ';
        $firstName = $profileDto->getFirstName() ? '' : $profileDto->getFirstName() . ' ';
        $middleName = $profileDto->getMiddleName() ? '' : $profileDto->getMiddleName();

        return $lastName . $firstName . $middleName;
    }

    private function updateProfileRequirements(EditUserProfileDto $profileDto, User $user)
    {
        $profileRequirements = $this->getProfileRequirements($profileDto->getClientProfileId());
        $this->validateProfileRequirements($profileRequirements, $profileDto, $user->getId());

        if ($profileRequirements->isRequirePostalAddress()) {
            $postalAddress = $profileDto->getPostalAddress();
            $this->validateSpecificAddressData($postalAddress);
            $this->preProcessAddressData($postalAddress);

            $profileDto->setPostalAddress($postalAddress);
            $postalConfigData = $this->getPostalConfigData($profileDto->getPostalAddress(), $user->getId());

            $this->savePostalData($postalConfigData, $postalAddress->getModelType());
        }

        if ($profileRequirements->isRequireLegalAddress()) {
            $legalAddress = $profileDto->getLegalAddress();
            $this->validateSpecificAddressData($legalAddress);
            $this->preProcessAddressData($legalAddress);

            $profileDto->setLegalAddress($legalAddress);
            $legalConfigData = $this->getPostalConfigData($profileDto->getLegalAddress(), $user->getId());

            $this->savePostalData($legalConfigData, $legalAddress->getModelType());
        }
    }

    private function validateProfileRequirements(ProfileRequirementsDto $profileRequirementsDto, EditUserProfileDto $userProfileDto, int $ownerId): void
    {
        if ($userProfileDto->getClientProfileId() === 3 && strlen($userProfileDto->getInn()) !== 12) {
            throw new FieldMissingException('iNN');
        }

        if (empty($userProfileDto->getInn()) && $profileRequirementsDto->isRequireKpp()) {
            throw new FieldMissingException('INN');
        } elseif($profileRequirementsDto->isRequireInn()) {
            parent::_validateStatic($userProfileDto->getInn(), 'inn', array('inn'));
        } elseif ($profileRequirementsDto->isRequireKpp() && empty($userProfileDto->getKpp())) {
            throw new FieldMissingException('KPP');
        } elseif ($profileRequirementsDto->isRequireOgrn() && empty($userProfileDto->getOgrn())) {
            throw new FieldMissingException('OGRN');
        }
    }

    private function savePostalData(array $postalConfigData, int $modelType): void
    {
        Model_Address::saveAddress($postalConfigData, $modelType);
    }

    private function validateSpecificAddressData(AddressDataDto $addressDataDto): AddressDataDto
    {
        $isEmpty = $addressDataDto->getIndex()
            || $addressDataDto->getRegion()
            || $addressDataDto->getCity()
            || $addressDataDto->getStreet()
            || ($addressDataDto->getHouse() && $addressDataDto->getHouseUnit());

        if ($isEmpty) {
            throw new ResponseException("Не заполнены обязательные поля {$addressDataDto->getModeTypeText()} адреса");
        }

        $this->validateAddressFieldLength($addressDataDto);

        return $addressDataDto;
    }

    private function getPostalConfigData(AddressDataDto $postalAddress, int $ownerId): array
    {
        $postalConfigData = [
            'house' => $postalAddress->getHouse(),
            'house_unit' => $postalAddress->getHouseUnit(),
            'street' => $postalAddress->getStreet(),
            'city' => $postalAddress->getCity(),
            'region' => $postalAddress->getRegion(),
            'index' => $postalAddress->getIndex(),
            'housing_unit' => $postalAddress->getHousingUnit(),
            'office_unit' => $postalAddress->getOfficeUnit(),
            'country_iso_nr' => $postalAddress->getCountryIsoNr(),
        ];

        $response = $postalConfigData;
        $response['owner_id'] = $ownerId;
        $response['owner_type'] = 'user';
        $response['actual'] = true;

        return $response;
    }

    private function preProcessAddressData(AddressDataDto $postalDataDto): void
    {
        if ($postalDataDto->getId()) {
            $postalDataDto->setId(null);
        }

        if (!$postalDataDto->getCountryIsoNr()) {
            $postalDataDto->setCountryIsoNr(643);
        }
    }

    private function validateAddressFieldLength(AddressDataDto $addressDataDto): void
    {
        $fields = ['houseUnit', 'housingUnit', 'officeUnit'];

        foreach ($fields as $field) {
            if (isset($addressDataDto->$field) && mb_strlen($addressDataDto->$field, "UTF-8") > 20) {
                $pseudo = Model_Address::_parameters()[$field]['pseudo'];
                throw new ResponseException("Поле '$pseudo' адреса может иметь длину не более 20 символов");
            }
        }
    }

    private function getProfileRequirements(int $profileId): ProfileRequirementsDto
    {
        $requirements = match ($profileId) {
            1 => [
                'require_inn' => true,
                'require_kpp' => false,
                'require_ogrn' => true,
                'require_legal_address' => true,
                'require_postal_address' => true,
            ],
            2 => [
                'require_inn' => true,
                'require_kpp' => false,
                'require_ogrn' => true,
                'require_legal_address' => false,
                'require_postal_address' => false,
            ],
            3 => [
                'require_inn' => true,
                'require_kpp' => false,
                'require_ogrn' => false,
                'require_legal_address' => false,
                'require_postal_address' => false,
            ],
            default => throw new ResponseException('Указан невереный тип организации')
        };

        return new ProfileRequirementsDto($requirements);
    }

    private function changeUserEmail(?string $existingEmail, EditUserProfileDto $profileDto): void
    {
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
