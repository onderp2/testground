<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\AddressDataDto;
use App\Dto\BankAccountDto;
use App\Dto\EditUserProfileDto;
use App\Dto\ProfileRequirementsDto;
use App\Entity\User;
use App\Exception\BaseUserEditException;
use App\Exception\ExceededFieldLengthException;
use App\Exception\FieldMissingException;
use App\Service\Factory\ProfileRequirementsFactory;
use App\Service\Mapper\ConfigMapper;

class BankDataProcessor
{
    /**
     * @throws FieldMissingException
     * @throws BaseUserEditException
     */
    public function processBankData(EditUserProfileDto $profileDto, User $user): void
    {
        $bankAccountDto = $profileDto->getBankAccount();

        $this->validateBankAccountData($profileDto, $bankAccountDto);
        $this->updateBankAccountData($bankAccountDto, $user->getId());
        $this->updateProfileRequirements($profileDto, $user);
    }

    /**
     * @throws FieldMissingException
     */
    private function updateBankAccountData(BankAccountDto $bankAccountDto, int $ownerId): void
    {
        $bankConfig = ConfigMapper::getBankConfigArray($bankAccountDto, $ownerId);

        if ($bankAccountDto->getId()) {
            $this->updateAccountBankData($bankAccountDto, $bankConfig);
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

    /**
     * @throws FieldMissingException
     * @throws BaseUserEditException
     */
    private function updateProfileRequirements(EditUserProfileDto $profileDto, User $user)
    {
        $profileRequirements = ProfileRequirementsFactory::getProfileRequirements($profileDto->getClientProfileId());
        $this->validateProfileRequirements($profileRequirements, $profileDto);

        $addressTypes = [
            'PostalAddress' => 'isRequirePostalAddress',
            'LegalAddress' => 'isRequireLegalAddress'
        ];

        foreach ($addressTypes as $addressType => $requirementMethod) {
            if ($profileRequirements->$requirementMethod()) {
                $addressGetter = "get{$addressType}";
                $addressSetter = "set{$addressType}";

                $address = $profileDto->$addressGetter();
                $this->validateSpecificAddressData($address);
                $this->preProcessAddressData($address);

                $profileDto->$addressSetter($address);
                $configData = ConfigMapper::getAddressDataConfigArray($profileDto->$addressGetter(), $user->getId());

                $this->savePostalData($configData, $address->getModelType());
            }
        }
    }

    /**
     * @throws FieldMissingException
     */
    private function validateProfileRequirements(
        ProfileRequirementsDto $profileRequirementsDto,
        EditUserProfileDto $userProfileDto
    ): void {
        if ($userProfileDto->getClientProfileId() === 3 && strlen($userProfileDto->getInn()) !== 12) {
            throw new FieldMissingException('INN field');
        }

        if (empty($userProfileDto->getInn()) && $profileRequirementsDto->isRequireKpp()) {
            throw new FieldMissingException('INN field');
        } elseif($profileRequirementsDto->isRequireInn()) {
            parent::_validateStatic($userProfileDto->getInn(), 'inn', array('inn'));
        } elseif ($profileRequirementsDto->isRequireKpp() && empty($userProfileDto->getKpp())) {
            throw new FieldMissingException('KPP field');
        } elseif ($profileRequirementsDto->isRequireOgrn() && empty($userProfileDto->getOgrn())) {
            throw new FieldMissingException('OGRN field');
        }
    }

    /**
     * @throws BaseUserEditException
     */
    private function validateSpecificAddressData(AddressDataDto $addressDataDto): void
    {
        $isEmpty = $addressDataDto->getIndex()
            || $addressDataDto->getRegion()
            || $addressDataDto->getCity()
            || $addressDataDto->getStreet()
            || ($addressDataDto->getHouse() && $addressDataDto->getHouseUnit());

        if ($isEmpty) {
            throw new FieldMissingException("Не заполнены обязательные поля {$addressDataDto->getModeTypeText()} адреса");
        }

        $this->validateAddressFieldLength($addressDataDto);
    }

    /**
     * @throws BaseUserEditException
     */
    private function validateAddressFieldLength(AddressDataDto $address): void
    {
        $fields = ['houseUnit', 'housingUnit', 'officeUnit'];

        foreach ($fields as $field) {
            if (isset($address->$field) && mb_strlen($address->$field, "UTF-8") > 20) {
                $pseudo = Model_Address::_parameters()[$field]['pseudo'];

                throw new ExceededFieldLengthException("Поле '$pseudo' адреса может иметь длину не более 20 символов");
            }
        }
    }

    private function preProcessAddressData(AddressDataDto $addressData): void
    {
        if ($addressData->getId()) {
            $addressData->setId(null);
        }

        if (!$addressData->getCountryIsoNr()) {
            $addressData->setCountryIsoNr(643);
        }
    }

    private function savePostalData(array $postalConfigData, int $modelType): void
    {
        Model_Address::saveAddress($postalConfigData, $modelType);
    }

    /**
     * @throws FieldMissingException
     */
    private function updateAccountBankData(BankAccountDto $bankAccountDto, array $bankConfig): void
    {
        Model_BankAccount::load($bankAccountDto->getId())?->update($bankConfig) ?? throw new FieldMissingException();
    }

    private function createAccountBank(array $bankConfig, int $ownerId): void
    {
        Model_BankAccount::create($bankConfig, $ownerId, Model_BankAccount::OWNER_TYPE_USER);
    }
}
