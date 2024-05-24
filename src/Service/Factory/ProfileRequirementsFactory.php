<?php

declare(strict_types=1);

namespace App\Service\Factory;

use App\Dto\ProfileRequirementsDto;
use App\Exception\InvalidEntityType;

class ProfileRequirementsFactory
{
    public static function getProfileRequirements(int $profileId): ProfileRequirementsDto
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
            default => throw new InvalidEntityType()
        };

        return new ProfileRequirementsDto($requirements);
    }
}
