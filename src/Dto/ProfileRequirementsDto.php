<?php

declare(strict_types=1);

namespace App\Dto;

class ProfileRequirementsDto
{
    private bool $requireInn = false;

    private bool $requireKpp = false;

    private bool $requireOgrn = false;

    private bool $requireLegalAddress = false;

    private bool $requirePostalAddress = false;

    public function __construct(array $requirements)
    {
        $this->setRequireInn($requirements['require_inn'] ?? false);
        $this->setRequireKpp($requirements['require_kpp'] ?? false);
        $this->setRequireOgrn($requirements['require_ogrn'] ?? false);
        $this->setRequireLegalAddress($requirements['require_legal_address'] ?? false);
        $this->setRequirePostalAddress($requirements['require_postal_address'] ?? false);
    }

    public function isRequirePostalAddress(): bool
    {
        return $this->requirePostalAddress;
    }

    public function setRequirePostalAddress(bool $requirePostalAddress): void
    {
        $this->requirePostalAddress = $requirePostalAddress;
    }

    public function isRequireLegalAddress(): bool
    {
        return $this->requireLegalAddress;
    }

    public function setRequireLegalAddress(bool $requireLegalAddress): void
    {
        $this->requireLegalAddress = $requireLegalAddress;
    }

    public function isRequireOgrn(): bool
    {
        return $this->requireOgrn;
    }

    public function setRequireOgrn(bool $requireOgrn): void
    {
        $this->requireOgrn = $requireOgrn;
    }

    public function isRequireInn(): bool
    {
        return $this->requireInn;
    }

    public function setRequireInn(bool $requireInn): void
    {
        $this->requireInn = $requireInn;
    }

    public function isRequireKpp(): bool
    {
        return $this->requireKpp;
    }

    public function setRequireKpp(bool $requireKpp): void
    {
        $this->requireKpp = $requireKpp;
    }
}
