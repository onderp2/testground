<?php

declare(strict_types=)1;

namespace App\Service;

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
}
