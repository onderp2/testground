<?php

declare(strict_types=)1;

namespace App\Service\Mapper;

use App\Dto\AddressDataDto;
use App\Dto\BankAccountDto;

class ConfigMapper
{
    public static function getBankConfigArray(BankAccountDto $bankAccountDto, int $ownerId): array
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

    public static function getAddressDataConfigArray(AddressDataDto $postalAddress, int $ownerId): array
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
}
