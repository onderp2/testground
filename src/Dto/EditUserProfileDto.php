<?php

declare(strict_types=1);

namespace App\Dto;

class EditUserProfileDto
{
    private ?int $clientProfileId = null;

    private string $cmpFullName = '';

    private ?string $cmpShortName = null;

    private ?string $lastName = null;

    private ?string $firstName = null;

    private ?string $middleName = null;

    private ?string $phone = null;

    private ?string $email = null;

    private ?string $userName = null;

    private bool $quickRegistration = false;

    private ?BankAccountDto $bankAccount = null;

    private AddressDataDto $postalAddress;

    private AddressDataDto $legalAddress;

    // todo -> create a new nested dto for fields below
    private string $inn = '';

    private string $kpp = '';

    private string $ogrn = '';

    public ?int $userType = null;

    public ?int $role = null;

    private ?int $pointId;

    private ?int $partnerId;

    public function __construct()
    {
        $this->postalAddress = new AddressDataDto();
        $this->legalAddress = new AddressDataDto();
    }

    public function getClientProfileId(): ?int
    {
        return $this->clientProfileId;
    }

    public function setClientProfileId(?int $clientProfileId): self
    {
        $this->clientProfileId = $clientProfileId;

        return $this;
    }

    public function getCmpFullName(): string
    {
        return $this->cmpFullName;
    }

    public function setCmpFullName(string $cmpFullName): void
    {
        $this->cmpFullName = $cmpFullName;
    }

    public function getCmpShortName(): ?string
    {
        return $this->cmpShortName;
    }

    public function setCmpShortName(?string $cmpShortName): self
    {
        $this->cmpShortName = $cmpShortName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(?string $middleName): self
    {
        $this->middleName = $middleName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getBankAccount(): ?BankAccountDto
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?BankAccountDto $bankAccount): self
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    public function isQuickRegistration(): bool
    {
        return $this->quickRegistration;
    }

    public function setQuickRegistration(bool $quickRegistration): self
    {
        $this->quickRegistration = $quickRegistration;

        return $this;
    }

    public function getInn(): string
    {
        return $this->inn;
    }

    public function setInn(string $inn): self
    {
        $this->inn = $inn;

        return $this;
    }

    public function getKpp(): string
    {
        return $this->kpp;
    }

    public function setKpp(string $kpp): self
    {
        $this->kpp = $kpp;

        return $this;
    }

    public function getOgrn(): string
    {
        return $this->ogrn;
    }

    public function setOgrn(string $ogrn): self
    {
        $this->ogrn = $ogrn;

        return $this;
    }

    public function getPostalAddress(): AddressDataDto
    {
        return $this->postalAddress;
    }

    public function setPostalAddress(AddressDataDto $postalAddress): void
    {
        $this->postalAddress = $postalAddress;
    }

    public function getLegalAddress(): AddressDataDto
    {
        return $this->legalAddress;
    }

    public function setLegalAddress(AddressDataDto $legalAddress): void
    {
        $this->legalAddress = $legalAddress;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(?string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(?int $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getUserType(): ?int
    {
        return $this->userType;
    }

    public function setUserType(?int $userType): self
    {
        $this->userType = $userType;

        return $this;
    }

    public function getPointId(): ?int
    {
        return $this->pointId;
    }

    public function setPointId(?int $pointId): self
    {
        $this->pointId = $pointId;

        return $this;
    }

    public function getPartnerId(): ?int
    {
        return $this->partnerId;
    }

    public function setPartnerId(?int $partnerId): self
    {
        $this->partnerId = $partnerId;

        return $this;
    }
}
