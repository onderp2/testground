<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\AddressDataDto;
use App\Dto\BankAccountDto;
use App\Dto\EditUserProfileDto;
use App\Dto\ProfileRequirementsDto;
use App\Entity\User;
use App\Exception\FieldMissingException;

class BankDataProcessor
{
    /**
     * @throws FieldMissingException
     */
    private function processBankData(EditUserProfileDto $profileDto, User $user): void
    {
        $bankAccountDto = $profileDto->getBankAccount();

        $this->validateBankAccountData($profileDto, $bankAccountDto);
        $this->updateBankAccountData($bankAccountDto, $user->getId());
        $this->updateProfileRequirements($profileDto, $user);
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

    private function isPhysicalAndNoData(EditUserProfileDto $profileDto): bool
    {
        return $profileDto->getClientProfileId() === 3 && $profileDto->getBankAccount() === null;
    }

    /**
     * @throws FieldMissingException
     */
    private function validateBankAccountData(EditUserProfileDto $profileDto, ?BankAccountDto $bankAccountDto): void
    {
        if ($this->isPhysicalAndNoData($profileDto) || $profileDto->isQuickRegistration()) {
            return;
        }

        if (null === $bankAccountDto){
            throw new FieldMissingException();
        }
        $fieldMissing = empty($bankAccountDto->getAccount())
            || empty($bankAccountDto->getBank())
            || empty($bankAccountDto->getBankAddr())
            || empty($bankAccountDto->getBik())
        ;

        if ($fieldMissing) {
            throw new FieldMissingException();
        }
    }

    private function getBankConfigArray(BankAccountDto $bankAccountDto, int $ownerId): array
    {
        return [
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
    }

    /**
     * @throws FieldMissingException
     */
    private function updateBankData(BankAccountDto $bankAccountDto, array $bankConfig): void
    {
        Model_BankAccount::load($bankAccountDto->getId())?->update($bankConfig) ?? throw new FieldMissingException();
    }

    private function createAccountBank(array $bankConfig, int $ownerId): void
    {
        Model_BankAccount::create($bankConfig, $ownerId, Model_BankAccount::OWNER_TYPE_USER);
    }

    /**
     * @throws FieldMissingException
     */
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

    private function preProcessAddressData(AddressDataDto $postalDataDto): void
    {
        if ($postalDataDto->getId()) {
            $postalDataDto->setId(null);
        }

        if (!$postalDataDto->getCountryIsoNr()) {
            $postalDataDto->setCountryIsoNr(643);
        }
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

    private function savePostalData(array $postalConfigData, int $modelType): void
    {
        Model_Address::saveAddress($postalConfigData, $modelType);
    }
}
